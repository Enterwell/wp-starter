<?php
/**
 * Handles lockouts for modules and core
 *
 * @since   4.0
 * @package iThemes-Security
 */

use iThemesSecurity\Actor\Actor;
use iThemesSecurity\Ban_Hosts\Repository_Ban;
use iThemesSecurity\Ban_Users;
use iThemesSecurity\Ban_Users\Ban;
use iThemesSecurity\Lib\Lockout;
use iThemesSecurity\Lib\Lockout\Execute_Lock;
use iThemesSecurity\Lib\Lockout\Execute_Lock\Source\Configurable;
use iThemesSecurity\Lib\Lockout\Execute_Lock\Source\Lockout_Module;
use iThemesSecurity\Lib\Result;

require_once( __DIR__ . '/lib/lockout/execute-lock/source/interface-source.php' );
require_once( __DIR__ . '/lib/lockout/execute-lock/abstract-context.php' );
require_once( __DIR__ . '/lib/lockout/class-lockout.php' );
require_once( __DIR__ . '/lib/lockout/abstract-context.php' );

/**
 * Class ITSEC_Lockout
 *
 * The ITSEC Lockout class is the centralized controller for detecting and blocking already locked-out users. Other
 * Solid Security modules instruct ITSEC_Lockout to save a lock out to storage, but ITSEC Lockout will never lock
 * out a user itself.
 *
 * If a user attempts to login with valid credentials and their user ID is marked as locked out, they will be prevented
 * from logging in and the lock will remain in effect until its expiration.
 *
 * There are three types of lockouts.
 *
 *  - User ID
 *  - Username
 *  - Host
 *
 * = User ID =
 * User ID lockouts are used whenever an attacker tries to repeatedly log in with a valid username, but incorrect password.
 * By default, a host lockout will occur first ( assuming the attacker does not alter their IPs ). This is done because
 * a user ID lockout can lock out a legitimate user from signing into their account.
 *
 * = Username =
 * Username lockouts are used whenever an attacker tried to repeatedly log in with a non-existent username. Or, if
 * enabled, uses the 'admin' username. This is separate from the User ID lock out type, however the lockout message
 * is shared between the two.
 *
 * = Host =
 * Host lockouts are used whenever an IP address is flagged as an attacker. This is done via repeated 404 errors or
 * failed CAPTCHA validations. If an IP address is whitelisted, an event will be logged, but the user will not be
 * locked out. By default, host lockouts have the lowest threshold before locking out the host. The Network Brute Force
 * module does NOT create host lockouts, but utilizes ITSEC_Lockout::execute_lock() to prevent the attacker from
 * accessing the site.
 *
 * ITSEC_Lockout will store a record whenever ITSEC Lockout is instructed to perform a lockout via ::do_lockout() in the
 * itsec_temp database table. If the threshold for that lockout type has been met – the most recently added one counts –
 * an actual lockout will be saved to the itsec_lockouts table. If enabled, and enough lockouts have occurred
 * ( configurable via settings ), a host will be blacklisted instead of added to the itsec_lockouts table. Blacklisted
 * IPs are blocked at the server level. This is handled by the ban-users module.
 *
 * After the lockout has been stored, the request will be immediately exited.
 *
 * Solid Security supports two types of whitelists. Temporary and permanent whitelists. Permanent whitelists are
 * configured in the Global Settings module and will permanently prevent a user with that IP from being locked out.
 * The temporary whitelist is a global list of admin level user's IP addresses. Whenever an admin user is logged-in and
 * using the site, their IP will be added to the whitelist for 24 hours.
 *
 * This controller also provides a number of methods to retrieve a list or clear both lockouts and temporary whitelists.
 */
final class ITSEC_Lockout {

	private $lockout_modules;

	/**
	 * ITSEC_Lockout constructor.
	 */
	public function __construct() {
		$this->lockout_modules = array(); //array to hold information on modules using this feature
	}

	public function run() {
		wp_cache_add_non_persistent_groups( 'itsec-lockouts' );

		add_action( 'itsec_scheduled_purge-lockouts', array( $this, 'purge_lockouts' ) );

		if ( ITSEC_Core::is_loading_early() ) {
			$this->register_modules();
			$this->check_for_host_lockouts();
		} else {
			// Check for host lockouts
			add_action( 'init', array( $this, 'check_for_host_lockouts' ) );
		}

		// Register all plugin modules
		add_action( 'plugins_loaded', array( $this, 'register_modules' ) );

		// Ensure that locked out users are prevented from checking logins.
		add_filter( 'authenticate', array( $this, 'check_authenticate_lockout' ), 30 );

		// Updated temp whitelist to ensure that admin users are automatically added.
		if ( $this->is_temp_authorization_enabled() ) {
			add_action( 'init', array( $this, 'update_temp_whitelist' ), 0 );
		}

		add_action( 'ithemes_sync_register_verbs', array( $this, 'register_sync_verbs' ) );
		add_filter( 'itsec-filter-itsec-get-everything-verbs', array( $this, 'register_sync_get_everything_verbs' ) );

		add_filter( 'itsec_notifications', array( $this, 'register_notification' ) );
		add_filter( 'itsec_lockout_notification_strings', array( $this, 'notification_strings' ) );

		add_filter( 'itsec_logs_prepare_lockout_entry_for_list_display', array( $this, 'filter_entry_for_list_display' ), 10, 3 );
	}

	/**
	 * Check if a user has successfully logged-in, and prevent them from accessing the site if they
	 * still have a lockout in effect.
	 *
	 * @param \WP_User|\WP_Error|null $user
	 *
	 * @return WP_User|WP_Error|null
	 */
	public function check_authenticate_lockout( $user ) {
		if ( ! ( $user instanceof WP_User ) ) {
			return $user;
		}

		$this->check_lockout( $user->ID );

		return $user;
	}

	/**
	 * On every page load, check if the current host is locked out.
	 *
	 * When a host becomes locked out, Solid Security performs a quick ban. This will cause an IP block to be
	 * written to the site's server configuration file. This ip block might not immediately take effect, particularly
	 * on Nginx systems. So on every page load we check that if the current host is locked out or not.
	 */
	public function check_for_host_lockouts() {

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		$host = ITSEC_Lib::get_ip();

		if ( ITSEC_Lib::is_ip_banned() ) {
			$this->execute_lock( new Execute_Lock\Host_Context( new Configurable( 'blacklist' ), $host ) );
		}

		if ( $lockout = $this->find_lockout( 'host', $host ) ) {
			$this->execute_lock( $lockout->make_execute_lock_context() );
		}
	}

	/**
	 * Checks if the host or user is locked out and executes lockout
	 *
	 * @since 4.0
	 *
	 * @param WP_User|int|false $user     WordPress user object or false.
	 * @param string|false      $username The username to check.
	 * @param string            $type     Lockout type asking for the check.
	 *
	 * @return void
	 */
	public function check_lockout( $user = false, $username = false, $type = '' ) {
		$host     = ITSEC_Lib::get_ip();
		$username = sanitize_text_field( trim( $username ) );

		$lockout = null;

		if ( $user instanceof WP_User ) {
			$user_id = $user->ID;
		} elseif ( $user ) {
			$user    = get_userdata( (int) $user );
			$user_id = $user ? $user->ID : 0;
		} else {
			$user    = wp_get_current_user();
			$user_id = $user->ID;
			$lockout = $this->find_lockout( 'host', $host );
		}

		if ( ! $lockout && $user_id ) {
			$lockout = $this->find_lockout( 'user', $user_id );
		}

		// Only check for a username lockout if no user ID was passed.
		if ( ! $lockout && ! $user_id && $username ) {
			$lockout = $this->find_lockout( 'username', $username );
		}

		if ( ! $lockout ) {
			return;
		}

		$context = $lockout->make_execute_lock_context();

		if ( $type && $context->get_source()->get_source_slug() !== $type ) {
			$context = $context->with_source( new Lockout_Module( $type ) );
		}

		$this->execute_lock( $context );
	}

	/**
	 * Find the lockout for a given type and identifier.
	 *
	 * @param string $type
	 * @param string $identifier
	 *
	 * @return Lockout\Lockout|null
	 */
	private function find_lockout( $type, $identifier ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		switch ( $type ) {
			case 'username':
				$field = 'lockout_username';
				break;
			case 'user':
				$field = 'lockout_user';
				break;
			case 'host':
				$field = 'lockout_host';
				break;
			default:
				return null;
		}

		$data = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `{$field}` = %s ORDER BY `lockout_start` DESC LIMIT 1;",
			date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ),
			$identifier
		), ARRAY_A );

		if ( ! $data ) {
			return null;
		}

		try {
			return $this->hydrate_lockout_entity( $data['lockout_id'], $data );
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Check if a given username is locked out.
	 *
	 * @param string $username
	 *
	 * @return bool
	 */
	public function is_username_locked_out( $username ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT `lockout_username` FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `lockout_username` = %s;",
			date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ), $username
		) );
	}

	/**
	 * Check if a given user is locked out.
	 *
	 * @param string $user_id
	 *
	 * @return bool
	 */
	public function is_user_locked_out( $user_id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT `lockout_user` FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `lockout_user` = %d;",
			date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ), $user_id
		) );
	}

	/**
	 * Check if a given host is locked out.
	 *
	 * @param string $host
	 *
	 * @return bool
	 */
	public function is_host_locked_out( $host ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT `lockout_host` FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_active`=1 AND `lockout_expire_gmt` > %s AND `lockout_host` = %s;",
			date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ), $host
		) );
	}

	/**
	 * This persists a lockout to storage or performs a permanent ban if appropriate.
	 *
	 * Each module registers lockout settings that determine if a lockout event applies to the hostname, user, or
	 * username. In addition, these settings determine how many lockout events of a specific kind trigger an actual
	 * lockout by calling $this->lockout().
	 *
	 * @since 4.0
	 *
	 * @param Lockout\Context $context
	 *
	 * @return void
	 */
	public function do_lockout( Lockout\Context $context ) {
		if ( ! isset( $this->lockout_modules[ $context->get_lockout_module() ] ) ) {
			return;
		}

		if ( ! apply_filters( 'itsec_do_lockout', true, $context ) ) {
			return;
		}

		$lockout = false;
		$options = $this->lockout_modules[ $context->get_lockout_module() ];

		$event_data = array(
			'temp_type'     => $options['type'],
			'temp_date'     => date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time() ),
			'temp_date_gmt' => date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ),
		);

		if ( $context instanceof Lockout\Host_Context && ! empty( $options['host'] ) ) {
			$lockout = $this->create_host_temp_event( $context->get_host(), $event_data, $options );

			if ( ! empty( $options['user'] ) ) {
				if ( $context->get_login_user_id() && $this->create_user_temp_event( $context->get_login_user_id(), $event_data, $options ) ) {
					if ( $lockout ) {
						$context->set_user_limit_triggered();
					} else {
						$context = new Lockout\User_Context( $context->get_lockout_module(), $context->get_login_user_id() );
					}

					$lockout = true;
				} elseif ( $context->get_login_username() && $this->create_username_temp_event( $context->get_login_username(), $event_data, $options ) ) {
					if ( $lockout ) {
						$context->set_user_limit_triggered();
					} else {
						$context = new Lockout\Username_Context( $context->get_lockout_module(), $context->get_login_username() );
					}

					$lockout = true;
				}
			}
		} elseif ( $context instanceof Lockout\User_Context && ! empty( $options['user'] ) ) {
			$lockout = $this->create_user_temp_event( $context->get_user_id(), $event_data, $options );
		} elseif ( $context instanceof Lockout\Username_Context && ! empty( $options['user'] ) ) {
			$lockout = $this->create_username_temp_event( $context->get_username(), $event_data, $options );
		}

		if ( $lockout ) {
			$this->lockout( $context );
		}
	}

	private function create_host_temp_event( $host, $event_data, $options ) {
		global $wpdb;

		$event_data['temp_host'] = $host;

		$wpdb->insert( "{$wpdb->base_prefix}itsec_temp", $event_data );

		$host_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$wpdb->base_prefix}itsec_temp` WHERE `temp_date_gmt` > %s AND `temp_host` = %s",
				date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - ( $options['period'] * MINUTE_IN_SECONDS ) ),
				$host
			)
		);

		return $host_count >= $options['host'];
	}

	private function create_user_temp_event( $user_id, $event_data, $options ) {
		global $wpdb;

		$event_data['temp_user']     = $user_id;
		$event_data['temp_username'] = sanitize_text_field( get_userdata( $user_id )->user_login );

		$wpdb->insert( "{$wpdb->base_prefix}itsec_temp", $event_data );

		$user_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$wpdb->base_prefix}itsec_temp` WHERE `temp_date_gmt` > %s AND (`temp_username` = %s OR `temp_user` = %d)",
				date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - ( $options['period'] * MINUTE_IN_SECONDS ) ),
				$event_data['temp_username'],
				$event_data['temp_user']
			)
		);

		return $user_count >= $options['user'];
	}

	private function create_username_temp_event( $username, $event_data, $options ) {
		global $wpdb;

		$event_data['temp_username'] = sanitize_text_field( $username );

		$wpdb->insert( "{$wpdb->base_prefix}itsec_temp", $event_data );

		$user_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$wpdb->base_prefix}itsec_temp` WHERE `temp_date_gmt` > %s AND `temp_username` = %s",
				date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - ( $options['period'] * MINUTE_IN_SECONDS ) ),
				$event_data['temp_username']
			)
		);

		return $user_count >= $options['user'];
	}

	/**
	 * Store a record of the locked out user/host or permanently ban the host.
	 *
	 * Permanently banned hosts will be forwarded to the ban-users module via the itsec-new-blacklisted-ip hook and
	 * not persisted to the database.
	 *
	 * If configured, notifies the configured email addresses of the lockout.
	 *
	 * @since 4.0
	 *
	 * @param Lockout\Context $context
	 *
	 * @return void
	 */
	private function lockout( Lockout\Context $context ) {
		$lock = $context->get_lockout_module() . '_';

		if ( $context instanceof Lockout\Host_Context ) {
			$lock .= $context->get_host();
		} elseif ( $context instanceof Lockout\User_Context ) {
			$lock .= $context->get_user_id();
		} elseif ( $context instanceof Lockout\Username_Context ) {
			$lock .= $context->get_username();
		}

		// Acquire a lock to prevent a lockout being created more than once by a particularly fast attacker.
		if ( ! ITSEC_Lib::get_lock( $lock, 180 ) ) {
			return;
		}

		$details = $this->create_lockout( $context );

		if ( $details['whitelisted'] ) {
			// No need to send an email notice when the host is whitelisted.
			ITSEC_Lib::release_lock( $lock );

			return;
		}

		$this->send_lockout_email(
			$context,
			$details['host_expiration'],
			$details['user_expiration'],
			$details['module_details']['reason']
		);

		ITSEC_Lib::release_lock( $lock );

		if ( $details['blacklisted'] ) {
			$this->execute_lock( new Execute_Lock\Host_Context( new Configurable( 'blacklist' ), $context->get_host() ) );
		} elseif ( $details['lockout'] instanceof Lockout\Lockout ) {
			$this->execute_lock( $details['lockout']->make_execute_lock_context() );
		} else {
			$this->execute_lock( $context->make_execute_lock_context() );
		}
	}

	/**
	 * Create a lockout.
	 *
	 * @param Lockout\Context|array $args_or_context
	 *
	 * @return array
	 */
	public function create_lockout( $args_or_context = array() ) {
		global $wpdb;

		$host = $user_id = $username = false;

		if ( $args_or_context instanceof Lockout\Context ) {
			$context = $args_or_context;
			$module  = $context->get_lockout_module();

			switch ( true ) {
				case $context instanceof Lockout\Host_Context:
					$host = $context->get_host();

					if ( $context->is_user_limit_triggered() ) {
						$user_id  = $context->get_login_user_id() ?: $user_id;
						$username = $context->get_login_username() ?: $username;
					}
					break;
				case $context instanceof Lockout\User_Context:
					$user_id = $context->get_user_id();
					break;
				case $context instanceof Lockout\Username_Context:
					$username = $context->get_username();
					break;
			}
		} else {
			$args     = $args_or_context;
			$module   = $args['module'];
			$host     = isset( $args['host'] ) ? $args['host'] : false;
			$user_id  = isset( $args['user_id'] ) ? $args['user_id'] : false;
			$username = isset( $args['username'] ) ? $args['username'] : false;
			$context  = null;
		}
		$module_details = $this->lockout_modules[ $module ];


		$whitelisted = ITSEC_Lib::is_ip_whitelisted( $host );
		$blacklisted = false;

		$log_data = array(
			'module'         => $module,
			'host'           => $host,
			'user_id'        => $user_id,
			'username'       => $username,
			'module_details' => $module_details,
			'whitelisted'    => $whitelisted,
			'blacklisted'    => false,
		);

		// Do a permanent ban if enabled and settings criteria are met.
		if ( false !== $host && ITSEC_Modules::get_setting( 'global', 'blacklist' ) ) {
			$blacklist_count   = ITSEC_Modules::get_setting( 'global', 'blacklist_count' );
			$blacklist_period  = ITSEC_Modules::get_setting( 'global', 'blacklist_period', 7 );
			$blacklist_seconds = $blacklist_period * DAY_IN_SECONDS;

			$host_count = 1 + $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_expire_gmt` > %s AND `lockout_host`= %s",
						date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() - $blacklist_seconds ),
						$host
					)
				);

			if ( $host_count >= $blacklist_count ) {
				$blacklisted             = true;
				$log_data['blacklisted'] = true;

				if ( $whitelisted ) {
					ITSEC_Log::add_notice( 'lockout', 'whitelisted-host-triggered-blacklist', array_merge( $log_data, compact( 'blacklist_period', 'blacklist_count', 'host_count' ) ) );
				} else {
					$this->blacklist_ip( $host, $context );
					ITSEC_Log::add_action( 'lockout', 'host-triggered-blacklist', array_merge( $log_data, compact( 'blacklist_period', 'blacklist_count', 'host_count' ) ) );
				}
			}
		}

		$host_expiration = false;
		$user_expiration = false;
		$lockout         = null;

		$lockouts_data = array(
			'lockout_type'      => $module_details['type'],
			'lockout_start'     => date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time() ),
			'lockout_start_gmt' => date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ),
			'lockout_context'   => $context ? serialize( $context ) : null,
		);

		if ( $whitelisted ) {
			$lockouts_data['lockout_expire']     = date( 'Y-m-d H:i:s', 1 );
			$lockouts_data['lockout_expire_gmt'] = date( 'Y-m-d H:i:s', 1 );
		} else {
			$exp_seconds = ITSEC_Modules::get_setting( 'global', 'lockout_period' ) * MINUTE_IN_SECONDS;

			$lockouts_data['lockout_expire']     = date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time() + $exp_seconds );
			$lockouts_data['lockout_expire_gmt'] = date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() + $exp_seconds );
		}

		if ( false !== $host && ! $blacklisted ) {
			$host_expiration = $lockouts_data['lockout_expire'];
			$lockout         = $this->add_lockout_to_db( 'host', $host, $whitelisted, $lockouts_data, $log_data );
		}

		if ( false !== $user_id ) {
			$user_expiration = $lockouts_data['lockout_expire'];
			$lockout         = $this->add_lockout_to_db( 'user', $user_id, $whitelisted, $lockouts_data, $log_data );
		}

		if ( false !== $username ) {
			$user_expiration = $lockouts_data['lockout_expire'];
			$lockout         = $this->add_lockout_to_db( 'username', $username, $whitelisted, $lockouts_data, $log_data );
		}

		$id = $lockout ? $lockout->get_id() : false;

		return compact( 'id', 'host_expiration', 'user_expiration', 'whitelisted', 'blacklisted', 'module_details', 'lockout' );
	}

	/**
	 * Adds a record of a lockout event to the database and log the event.
	 *
	 * @param string     $type         The type of lockout: "host", "user", "username".
	 * @param string|int $id           The value for the type: host's IP, user's ID, username.
	 * @param bool       $whitelisted  Whether or not the host triggering the event is whitelisted.
	 * @param array      $lockout_data Array of base data to be inserted.
	 * @param array      $log_data     Array of data to be logged for the event.
	 *
	 * @return Lockout\Lockout|null
	 */
	private function add_lockout_to_db( $type, $id, $whitelisted, $lockout_data, $log_data ) {
		global $wpdb;

		$lockout_data["lockout_$type"] = $id;

		$result    = $wpdb->insert( "{$wpdb->base_prefix}itsec_lockouts", $lockout_data );
		$insert_id = $result ? $wpdb->insert_id : false;

		if ( $whitelisted ) {
			ITSEC_Log::add_notice( 'lockout', "whitelisted-host-triggered-$type-lockout", array_merge( $log_data, $lockout_data ) );
		} else {
			if ( 'host' === $type ) {
				$code = "host-lockout::{$log_data['host']}";
			} elseif ( 'user' === $type ) {
				$code = "user-lockout::{$log_data['user_id']}";
			} elseif ( 'username' === $type ) {
				$code = "username-lockout::{$log_data['username']}";
			}

			ITSEC_Log::add_action( 'lockout', $code, array_merge( $log_data, $lockout_data ) );
		}

		if ( ! $insert_id ) {
			return null;
		}

		try {
			return $this->hydrate_lockout_entity( $insert_id, $lockout_data );
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Hydrate a lockout entity from its DB data.
	 *
	 * @param int   $id
	 * @param array $data
	 *
	 * @return Lockout\Lockout
	 * @throws Exception
	 */
	private function hydrate_lockout_entity( $id, array $data ) {
		$context = null;

		if ( $data['lockout_context'] && ! ( $context = unserialize( $data['lockout_context'] ) ) instanceof Lockout\Context ) {
			$context = null;
		}

		return new Lockout\Lockout(
			$id,
			$data['lockout_type'],
			new DateTime( $data['lockout_start_gmt'], new DateTimeZone( 'UTC' ) ),
			new DateTime( $data['lockout_expire_gmt'], new DateTimeZone( 'UTC' ) ),
			isset( $data['lockout_host'] ) ? $data['lockout_host'] : '',
			isset( $data['lockout_user'] ) ? $data['lockout_user'] : 0,
			isset( $data['lockout_username'] ) ? $data['lockout_username'] : '',
			! empty( $data['lockout_active'] ),
			$context
		);
	}

	/**
	 * Checks if ITSEC supports ban creation from a lockout.
	 *
	 * @return bool True if ban users module is active.
	 */
	public function is_lockout_banning_available(): bool {
		return ITSEC_Modules::is_active( 'ban-users' );
	}

	/**
	 * Checks is specific lockout can be banned.
	 *
	 * @param int $lockout_id The lockout id.
	 *
	 * @return Result<Lockout\Lockout> Successful if lockout can be banned.
	 */
	public function can_create_ban_from_lockout( int $lockout_id ): Result {
		if ( ! $this->is_lockout_banning_available() ) {
			return Result::error( new WP_Error(
				'itsec.lockout.persist-ban.not-supported',
				__( 'Not supported.', 'better-wp-security' )
			) );
		}

		try {
			$lockout = $this->get_lockout( $lockout_id, OBJECT );
		} catch ( Exception $e ) {
			return Result::error( new WP_Error(
				'itsec.lockout.persist-ban.invalid-lockout',
				__( 'Invalid lockout.', 'better-wp-security' )
			) );
		}

		if ( ! $lockout instanceof Lockout\Lockout ) {
			return Result::error( new WP_Error(
				'itsec.lockout.persist-ban.lockout-not-found',
				__( 'Lockout not found.', 'better-wp-security' )
			) );
		}

		if ( ! $lockout->get_host() ) {
			return Result::error( new WP_Error(
				'itsec.lockout.persist-ban.no-host',
				__( 'No host.', 'better-wp-security' )
			) );
		}

		return Result::success( $lockout );
	}

	/**
	 * Creates a ban from a lockout
	 *
	 * @param int        $lockout_id The lockout id.
	 * @param Actor|null $actor      Optionally, the actor creating the ban.
	 *
	 * @return Result<Repository_Ban>
	 */
	public function persist_ban_from_lockout( int $lockout_id, Actor $actor = null ): Result {

		$can_convert = $this->can_create_ban_from_lockout( $lockout_id );
		if ( ! $can_convert->is_success() ) {
			return $can_convert;
		}

		$lockout    = $can_convert->get_data();
		$module     = $lockout->get_module();
		$comment    = $this->lockout_modules[ $module ]['reason'] ?? '';
		$ban        = new Ban( $lockout->get_host(), $actor, $comment );
		$repository = ITSEC_Modules::get_container()->get( Ban_Users\Database_Repository::class );

		try {
			$saved = $repository->persist( $ban );
		} catch ( \iThemesSecurity\Exception\WP_Error $e ) {
			return Result::error( $e->get_error() );
		}

		return Result::success( $saved );
	}

	/**
	 * Executes lockout (locks user out)
	 *
	 * @param Execute_Lock\Context|array $context
	 *
	 * @return void
	 */
	public function execute_lock( Execute_Lock\Context $context ) {
		if ( ITSEC_Lib::is_ip_whitelisted( ITSEC_Lib::get_ip() ) ) {
			return;
		}

		if ( ! apply_filters( 'itsec_execute_lock', true, $context ) ) {
			return;
		}

		if ( $context instanceof Execute_Lock\Host_Context && $context->is_network_brute_force() ) {

			$message = ITSEC_Modules::get_setting( 'global', 'community_lockout_message' );

			if ( ! $message ) {
				$message = __( 'Your IP address has been flagged as a threat by the Solid Security network.', 'better-wp-security' );
			}
		} elseif ( $context instanceof Execute_Lock\User_Context || $context instanceof Execute_Lock\Username_Context ) {

			$message = ITSEC_Modules::get_setting( 'global', 'user_lockout_message' );

			if ( ! $message ) {
				$message = __( 'You have been locked out due to too many invalid login attempts.', 'better-wp-security' );
			}
		} else {
			$message = ITSEC_Modules::get_setting( 'global', 'lockout_message' );

			if ( ! $message ) {
				$message = __( 'Error.', 'better-wp-security' );
			}
		}

		$source = $context->get_source();

		if ( $source instanceof Lockout\Lockout ) {
			$slug = $source->get_module();
		} else {
			$slug = $source->get_source_slug();
		}

		if ( $slug ) {
			/**
			 * Filter the lockout message displayed to the user.
			 *
			 * @param string               $message
			 * @param string               $type
			 * @param Execute_Lock\Context $context
			 */
			$message = apply_filters( "itsec_{$slug}_lockout_message", $message, $context );
		}

		if ( function_exists( 'wp_get_current_user' ) ) {
			$current_user = wp_get_current_user();
		} else {
			$current_user = null;
		}

		if ( headers_sent() ) {
			wp_destroy_current_session();
			wp_set_current_user( 0 );

			if ( $current_user instanceof WP_User && $current_user->exists() ) {
				do_action( 'wp_logout', $current_user->ID );
			}
		} else {
			if ( $current_user instanceof WP_User && $current_user->exists() ) {
				wp_logout();
			}

			status_header( 403 );
			ITSEC_Lib::clear_auth_cookie();
			ITSEC_Lib::no_cache();
		}

		add_filter( 'wp_robots', 'wp_robots_sensitive_page' );

		$actions = apply_filters( 'itsec_lockout_action_links', array(), $context );

		ob_start();
		call_user_func( function () use ( $context, $message, $actions ) {
			require( dirname( __FILE__ ) . '/templates/lockout/lockout.php' );
		} );

		add_filter( 'wp_die_handler', function () {
			return '_scalar_wp_die_handler';
		} );
		wp_die( ob_get_clean() );
	}

	/**
	 * Shows all lockouts currently in the database.
	 *
	 * @since 4.0
	 *
	 * @param string $type 'all', 'host', 'user' or 'username'.
	 * @param array  $args Additional arguments.
	 *
	 * @return array all lockouts in the system
	 */
	public function get_lockouts( $type = 'all', $args = array() ) {

		global $wpdb;

		if ( is_bool( $args ) ) {
			$args = array( 'current' => $args );
		}

		if ( func_num_args() === 3 ) {
			$third = func_get_arg( 2 );

			if ( $third && is_numeric( $third ) ) {
				$args['limit'] = $third;
			}
		}

		$args = wp_parse_args( $args, array(
			'current' => true,
		) );

		$where  = $limit = $join = $order = '';
		$wheres = $prepare = array();

		switch ( $type ) {

			case 'host':
				$wheres[] = "`lockout_host` IS NOT NULL AND `lockout_host` != ''";
				break;
			case 'user':
				$wheres[] = '`lockout_user` != 0';
				break;
			case 'username':
				$wheres[] = "`lockout_username` IS NOT NULL AND `lockout_username` != ''";
				break;
		}

		if ( $args['current'] ) {
			$wheres[] = "`lockout_active` = 1 AND `lockout_expire_gmt` > '" . date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() ) . "'";
		}

		if ( isset( $args['after'] ) ) {
			$after = is_int( $args['after'] ) ? $args['after'] : strtotime( $args['after'] );
			$after = date( 'Y-m-d H:i:s', $after );

			$wheres[] = "`lockout_start_gmt` > '{$after}'";
		}

		if ( ! empty( $args['search'] ) ) {
			$search  = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$prepare = array_merge( $prepare, array_pad( array(), 6, $search ) );

			$u        = $wpdb->users;
			$l        = $wpdb->base_prefix . 'itsec_lockouts';
			$join     .= " LEFT JOIN `{$u}` ON ( `{$l}`.`lockout_user` = `{$u}`.`ID` )";
			$wheres[] = "( `{$u}`.`user_login` LIKE %s OR `{$u}`.`user_email` LIKE %s OR `{$u}`.`user_nicename` LIKE %s OR `{$u}`.`display_name` LIKE %s OR `{$l}`.`lockout_username` LIKE %s or `{$l}`.`lockout_host` LIKE %s)";
		}

		if ( $wheres ) {
			$where = ' WHERE ' . implode( ' AND ', $wheres );
		}

		if ( ! empty( $args['limit'] ) ) {
			$limit = ' LIMIT ' . absint( $args['limit'] );
		}

		if ( ! empty( $args['orderby'] ) ) {
			$columns   = array( 'lockout_id', 'lockout_start', 'lockout_expire' );
			$direction = isset( $args['order'] ) ? $args['order'] : 'DESC';

			if ( ! in_array( $args['orderby'], $columns, true ) ) {
				_doing_it_wrong( __METHOD__, "Orderby must be one of 'lockout_id', 'lockout_start', or 'lockout_expire'.", 4109 );

				return array();
			}

			if ( ! in_array( $direction, array( 'ASC', 'DESC' ), true ) ) {
				_doing_it_wrong( __METHOD__, "Order must be one of 'ASC' or 'DESC'.", 4109 );

				return array();
			}

			$order = " ORDER BY `{$args['orderby']}` $direction";
		}

		if ( isset( $args['return'] ) && 'count' === $args['return'] ) {
			$select   = 'SELECT COUNT(1) as COUNT';
			$is_count = true;
		} else {
			$select   = "SELECT `{$wpdb->base_prefix}itsec_lockouts`.*";
			$is_count = false;
		}

		$sql = "{$select} FROM `{$wpdb->base_prefix}itsec_lockouts` {$join}{$where}{$order}{$limit};";

		if ( $prepare ) {
			$sql = $wpdb->prepare( $sql, $prepare );
		}

		$results = $wpdb->get_results( $sql, ARRAY_A );

		if ( $is_count && $results ) {
			return $results[0]['COUNT'];
		}

		foreach ( $results as $result ) {
			wp_cache_add( $result['lockout_id'], $result, 'itsec-lockouts' );
		}

		return $results;
	}

	/**
	 * Checks if temp host authorization is enabled.
	 *
	 * @return bool
	 */
	public function is_temp_authorization_enabled() {
		if ( defined( 'ITSEC_DISABLE_TEMP_WHITELIST' ) && ITSEC_DISABLE_TEMP_WHITELIST ) {
			return false;
		}

		return ITSEC_Modules::get_setting( 'global', 'automatic_temp_auth' );
	}

	/**
	 * Retrieve a list of the temporary whitelisted IP addresses.
	 *
	 * @return array A map of IP addresses to their expiration time.
	 */
	public function get_temp_whitelist() {
		$whitelist = get_site_option( 'itsec_temp_whitelist_ip', false );

		if ( ! is_array( $whitelist ) ) {
			$whitelist = array();
		} elseif ( isset( $whitelist['ip'] ) ) {
			// Update old format
			$whitelist = array(
				$whitelist['ip'] => $whitelist['exp'] - ITSEC_Core::get_time_offset(),
			);
		} else {
			return $whitelist;
		}

		update_site_option( 'itsec_temp_whitelist_ip', $whitelist );

		return $whitelist;
	}

	/**
	 * If the current user has permission to manage ITSEC, add them to the temporary whitelist.
	 */
	public function update_temp_whitelist() {
		if ( ! ITSEC_Core::current_user_can_manage() ) {
			// Only add IP's of users that can manage Security settings.
			return;
		}

		$ip = ITSEC_Lib::get_ip();
		$this->add_to_temp_whitelist( $ip );
	}

	/**
	 * Add an IP address to the temporary whitelist for 24 hours.
	 *
	 * This method will also remove any expired IPs from storage.
	 *
	 * @param string $ip
	 */
	public function add_to_temp_whitelist( $ip ) {
		$whitelist          = $this->get_temp_whitelist();
		$expiration         = ITSEC_Core::get_current_time_gmt() + DAY_IN_SECONDS;
		$refresh_expiration = $expiration - HOUR_IN_SECONDS;

		if ( isset( $whitelist[ $ip ] ) && $whitelist[ $ip ] > $refresh_expiration ) {
			// An update is not needed yet.
			return;
		}

		// Remove expired entries.
		foreach ( $whitelist as $cached_ip => $cached_expiration ) {
			if ( $cached_expiration < ITSEC_Core::get_current_time_gmt() ) {
				unset( $whitelist[ $cached_ip ] );
			}
		}

		$whitelist[ $ip ] = $expiration;

		update_site_option( 'itsec_temp_whitelist_ip', $whitelist );
	}

	/**
	 * Remove a given IP address from the temporary whitelist.
	 *
	 * @param string $ip
	 */
	public function remove_from_temp_whitelist( $ip ) {
		$whitelist = $this->get_temp_whitelist();

		if ( ! isset( $whitelist[ $ip ] ) ) {
			return;
		}

		unset( $whitelist[ $ip ] );

		update_site_option( 'itsec_temp_whitelist_ip', $whitelist );
	}

	/**
	 * Completely clear the temporary whitelist of all IP addresses.
	 */
	public function clear_temp_whitelist() {
		update_site_option( 'itsec_temp_whitelist_ip', array() );
	}

	/**
	 * Inserts an IP address into the htaccess ban list.
	 *
	 * @since 4.0
	 *
	 * @param string               $ip      The IP address to ban.
	 * @param Lockout\Context|null $context The lockout context that caused the ban.
	 *
	 * @return boolean False if the IP is whitelisted, true otherwise.
	 */
	public function blacklist_ip( $ip, Lockout\Context $context = null ) {
		$ip = sanitize_text_field( $ip );

		if ( ITSEC_Lib::is_ip_banned( $ip ) ) {
			// Already blacklisted.
			return true;
		}

		if ( ITSEC_Lib::is_ip_whitelisted( $ip ) ) {
			// Cannot blacklist a whitelisted IP.
			return false;
		}

		/**
		 * Fires when a new IP has been banned.
		 *
		 * This is primarily used by the Ban Users module.
		 *
		 * @param string               $ip      The IP address.
		 * @param Lockout\Context|null $context The lockout context that caused the ban.
		 */
		do_action( 'itsec_new_banned_ip', $ip, $context );

		return true;
	}

	/**
	 * Check if the current user is temporarily whitelisted.
	 *
	 * @return bool
	 */
	public function is_visitor_temp_whitelisted() {
		if ( ! $this->is_temp_authorization_enabled() ) {
			return false;
		}

		$whitelist = $this->get_temp_whitelist();
		$ip        = ITSEC_Lib::get_ip();

		if ( isset( $whitelist[ $ip ] ) && $whitelist[ $ip ] > ITSEC_Core::get_current_time() ) {
			return true;
		}

		return false;
	}

	/**
	 * Purges lockouts more than 7 days old from the database
	 *
	 * @return void
	 */
	public function purge_lockouts() {
		global $wpdb;

		$this->record_lockout_summary();

		$now = ITSEC_Core::get_current_time_gmt();

		$period     = ITSEC_Modules::get_setting( 'global', 'blacklist_period' ) + 1;
		$older_than = $now - ( $period * DAY_IN_SECONDS );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_expire_gmt` < %s",
				date( 'Y-m-d H:i:s', $older_than )
			)
		);
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM `{$wpdb->base_prefix}itsec_temp` WHERE `temp_date_gmt` < %s",
				date( 'Y-m-d H:i:s', $now - DAY_IN_SECONDS )
			)
		);
	}

	/**
	 * Records a summary of the times an IP has been locked out in a day.
	 *
	 * We store a timestamp of the last time this function has run to avoid
	 * counting any lockouts twice.
	 *
	 * @return void
	 */
	private function record_lockout_summary() {
		global $wpdb;

		$last_seen = ITSEC_Modules::get_setting( 'core', 'last_seen_lockout' );
		$lockouts  = $wpdb->get_results(
			$wpdb->prepare(
				<<<SQL
SELECT lockout_host as h, sum(1) as c, DATE(`lockout_start_gmt`) as d
FROM `{$wpdb->base_prefix}itsec_lockouts`
WHERE
	`lockout_active` = 1 AND
	`lockout_host` IS NOT NULL AND
	`lockout_host` != '' AND
	`lockout_start_gmt` > %s
GROUP BY
	`lockout_host`,
	DATE(`lockout_start_gmt`)
SQL,
				date( 'Y-m-d H:i:s', $last_seen )
			),
			ARRAY_A
		);

		ITSEC_Modules::set_setting( 'core', 'last_seen_lockout', ITSEC_Core::get_current_time_gmt() );

		if ( is_array( $lockouts ) && $lockouts ) {
			$insert  = "INSERT INTO {$wpdb->base_prefix}itsec_dashboard_lockouts (`ip`, `time`, `count`) VALUES ";
			$prepare = [];

			foreach ( $lockouts as $lockout ) {
				$insert    .= '(%s, %s, %d),';
				$prepare[] = $lockout['h'];
				$prepare[] = $lockout['d'];
				$prepare[] = $lockout['c'];
			}

			$insert[ strlen( $insert ) - 1 ] = ';';

			$wpdb->query( $wpdb->prepare( $insert, $prepare ) );
		}
	}

	/**
	 * Gets a list of top blocked IPs in a given period.
	 *
	 * @param int $number Number of IPs to return.
	 * @param int $after  Find IPs locked out after this time.
	 * @param int $before Find IPs locked out before this time.
	 *
	 * @return Result<array>
	 */
	public function get_top_blocked_ips( int $number, int $after, int $before ): Result {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				<<<SQL
SELECT sum(count) as c, ip
FROM {$wpdb->base_prefix}itsec_dashboard_lockouts
WHERE `time` > %s AND `time` < %s
GROUP BY `ip`
ORDER BY c DESC
LIMIT %d
SQL,
				date( 'Y-m-d H:i:s', $after ),
				date( 'Y-m-d H:i:s', $before ),
				$number
			),
			ARRAY_A
		);

		if ( $wpdb->last_error ) {
			return Result::error( new WP_Error(
				'itsec.lockout.top-blocked-ips.db-error',
				$wpdb->last_error
			) );
		}

		return Result::success( array_map( function ( array $result ) {
			return [
				'ip'    => $result['ip'],
				'count' => (int) $result['c'],
			];
		}, $results ) );
	}

	/**
	 * Register verbs for Sync.
	 *
	 * @since 3.6.0
	 *
	 * @param Ithemes_Sync_API $api API object.
	 */
	public function register_sync_verbs( $api ) {
		$api->register( 'itsec-get-lockouts', 'Ithemes_Sync_Verb_ITSEC_Get_Lockouts', dirname( __FILE__ ) . '/sync-verbs/itsec-get-lockouts.php' );
		$api->register( 'itsec-release-lockout', 'Ithemes_Sync_Verb_ITSEC_Release_Lockout', dirname( __FILE__ ) . '/sync-verbs/itsec-release-lockout.php' );
		$api->register( 'itsec-get-temp-whitelist', 'Ithemes_Sync_Verb_ITSEC_Get_Temp_Whitelist', dirname( __FILE__ ) . '/sync-verbs/itsec-get-temp-whitelist.php' );
		$api->register( 'itsec-set-temp-whitelist', 'Ithemes_Sync_Verb_ITSEC_Set_Temp_Whitelist', dirname( __FILE__ ) . '/sync-verbs/itsec-set-temp-whitelist.php' );
	}

	/**
	 * Filter to add verbs to the response for the itsec-get-everything verb.
	 *
	 * @since 3.6.0
	 *
	 * @param array $verbs of verbs.
	 *
	 * @return array Array of verbs.
	 */
	public function register_sync_get_everything_verbs( $verbs ) {
		$verbs['lockout'][] = 'itsec-get-lockouts';
		$verbs['lockout'][] = 'itsec-get-temp-whitelist';

		return $verbs;
	}

	/**
	 * Register modules that will use the lockout service.
	 *
	 * @return void
	 */
	public function register_modules() {

		/**
		 * Filter the available lockout modules.
		 *
		 * @param array $lockout_modules Each lockout module should be an array containing 'type', 'reason' and
		 *                               'period' options. The type is a unique string referring to the type of lockout.
		 *                               'reason' is a human readable label describing the reason for the lockout.
		 *                               'period' is the number of days to check for lockouts to decide if the host
		 *                               should be permanently banned. Additionally, the 'user' and 'host' options instruct
		 *                               security to wait for that many temporary lockout events to occur before executing
		 *                               the lockout.
		 */
		$this->lockout_modules = apply_filters( 'itsec_lockout_modules', $this->lockout_modules );
	}

	/**
	 * Get all the registered lockout modules.
	 *
	 * @return array
	 */
	public function get_lockout_modules() {
		return $this->lockout_modules;
	}

	/**
	 * Get lockout details.
	 *
	 * @param int    $id
	 * @param string $return
	 *
	 * @return Lockout\Lockout|array|false
	 * @throws Exception
	 */
	public function get_lockout( $id, $return = ARRAY_A ) {
		global $wpdb;

		$data = wp_cache_get( $id, 'itsec-lockouts' );

		if ( ! $data ) {
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM `{$wpdb->base_prefix}itsec_lockouts` WHERE `lockout_id` = %d",
				$id
			), ARRAY_A );

			if ( ! is_array( $results ) || ! isset( $results[0] ) ) {
				return false;
			}

			$data = $results[0];
			wp_cache_add( $id, $data, 'itsec-lockouts' );
		}

		if ( $return === OBJECT ) {
			return $this->hydrate_lockout_entity( $id, $data );
		}

		return $data;
	}

	/**
	 * Process clearing lockouts on view log page
	 *
	 * @since 4.0
	 *
	 * @param int $id
	 *
	 * @return bool true on success or false
	 */
	public function release_lockout( $id = 0 ) {
		global $wpdb;

		if ( ! $id ) {
			return false;
		}

		return (bool) $wpdb->update(
			$wpdb->base_prefix . 'itsec_lockouts',
			array(
				'lockout_active' => 0,
			),
			array(
				'lockout_id' => (int) $id,
			)
		);
	}

	/**
	 * Register the lockout notification.
	 *
	 * @param array $notifications
	 *
	 * @return array
	 */
	public function register_notification( $notifications ) {
		$notifications['lockout'] = array(
			'subject_editable' => true,
			'recipient'        => ITSEC_Notification_Center::R_USER_LIST,
			'schedule'         => ITSEC_Notification_Center::S_NONE,
			'optional'         => true,
		);

		return $notifications;
	}

	/**
	 * Get the strings for the lockout notification.
	 *
	 * @return array
	 */
	public function notification_strings() {
		return array(
			'label'       => __( 'Site Lockouts', 'better-wp-security' ),
			'description' => __( 'Various modules send emails to notify you when a user or IP address is locked out of your website.', 'better-wp-security' ),
			'subject'     => __( 'Site Lockout Notification', 'better-wp-security' ),
		);
	}

	/**
	 * Sends an email to notify site admins of lockouts
	 *
	 * @since 4.0
	 *
	 * @param Lockout\Context $context
	 * @param string          $host_expiration when the host login expires
	 * @param string          $user_expiration when the user lockout expires
	 * @param string          $reason          the reason for the lockout to show to the user
	 *
	 * @return void
	 */
	private function send_lockout_email( Lockout\Context $context, $host_expiration, $user_expiration, $reason ) {

		$nc = ITSEC_Core::get_notification_center();

		if ( ! $nc || ! $nc->is_notification_enabled( 'lockout' ) ) {
			return;
		}

		$lockouts                    = array();
		$show_remove_ip_ban_message  = false;
		$show_remove_lockout_message = false;

		if (
			( $context instanceof Lockout\User_Context && $user_id = $context->get_user_id() ) ||
			( $context instanceof Lockout\Host_Context && $context->is_user_limit_triggered() && $user_id = $context->get_login_user_id() )
		) {
			$show_remove_lockout_message = true;

			$lockouts[] = array(
				'type'   => 'user',
				'id'     => get_userdata( $user_id )->user_login,
				'until'  => $user_expiration,
				'reason' => $reason,
			);
		}

		if (
			( $context instanceof Lockout\Username_Context && $username = $context->get_username() ) ||
			( $context instanceof Lockout\Host_Context && $context->is_user_limit_triggered() && $username = $context->get_login_username() )
		) {
			$lockouts[] = array(
				'type'   => 'username',
				'id'     => $username,
				'until'  => $user_expiration,
				'reason' => $reason,
			);
		}

		if ( $context instanceof Lockout\Host_Context ) {
			if ( false === $host_expiration ) {
				$host_expiration            = __( 'Permanently', 'better-wp-security' );
				$show_remove_ip_ban_message = true;
			} else {
				$show_remove_lockout_message = true;
			}

			$lockouts[] = array(
				'type'   => 'host',
				'id'     => $context->get_host(),
				'until'  => $host_expiration,
				'reason' => $reason,
			);
		}

		$mail = $nc->mail();

		$mail->add_header(
			esc_html__( 'Site Lockout Notification', 'better-wp-security' ),
			esc_html__( 'Site Lockout Notification', 'better-wp-security' ),
			false,
			sprintf( esc_html__( '%s lockout notification', 'better-wp-security'), $mail->get_display_url() ),
		);
		$mail->add_lockouts_table( $lockouts );

		if ( $show_remove_lockout_message ) {
			$mail->add_text( __( 'Release lockouts from the Active Lockouts section of the Security -> Dashboard page.', 'better-wp-security' ) );
			$mail->add_button( __( 'Visit Dashboard', 'better-wp-security' ), ITSEC_Mail::filter_admin_page_url( network_admin_url( 'admin.php?page=itsec-dashboard' ) ) );
		}

		if ( $show_remove_ip_ban_message ) {
			$mail->add_text( __( 'Release the permanently banned IP from the Banned IPs dashboard card.', 'better-wp-security' ) );
			$mail->add_button( __( 'Visit Dashboard', 'better-wp-security' ), ITSEC_Mail::filter_admin_page_url( network_admin_url( 'admin.php?page=itsec-dashboard' ) ) );
		}

		$mail->add_footer();

		$subject = $mail->prepend_site_url_to_subject( $nc->get_subject( 'lockout' ) );
		$subject = apply_filters( 'itsec_lockout_email_subject', $subject );
		$mail->set_subject( $subject, false );

		$nc->send( 'lockout', $mail );
	}

	public function filter_entry_for_list_display( $entry, $code, $data ) {
		$entry['module_display'] = esc_html__( 'Lockout', 'better-wp-security' );

		if ( 'whitelisted-host-triggered-blacklist' === $code ) {
			$entry['description'] = esc_html__( 'Authorized IP Triggered Ban Conditions', 'better-wp-security' );
		} elseif ( 'host-triggered-blacklist' === $code ) {
			$entry['description'] = esc_html__( 'IP Triggered Ban Conditions', 'better-wp-security' );
		} elseif ( 'whitelisted-host-triggered-host-lockout' === $code ) {
			$entry['description'] = esc_html__( 'Authorized IP Triggered IP Lockout', 'better-wp-security' );
		} elseif ( 'host-lockout' === $code ) {
			if ( isset( $data[0] ) ) {
				$entry['description'] = sprintf( wp_kses( __( 'IP Lockout: <code>%s</code>', 'better-wp-security' ), array( 'code' => array() ) ), $data[0] );
			} else {
				$entry['description'] = esc_html__( 'IP Lockout', 'better-wp-security' );
			}
		} elseif ( 'whitelisted-host-triggered-user-lockout' === $code ) {
			$entry['description'] = esc_html__( 'Authorized IP Triggered User Lockout', 'better-wp-security' );
		} elseif ( 'user-lockout' === $code ) {
			if ( isset( $data[0] ) ) {
				$user = get_user_by( 'id', $data[0] );
			}

			if ( isset( $user ) && false !== $user ) {
				$entry['description'] = sprintf( wp_kses( __( 'User Lockout: <code>%s</code>', 'better-wp-security' ), array( 'code' => array() ) ), $user->user_login );
			} else {
				$entry['description'] = esc_html__( 'User Lockout', 'better-wp-security' );
			}
		} elseif ( 'whitelisted-host-triggered-username-lockout' === $code ) {
			$entry['description'] = esc_html__( 'Authorized IP Triggered Username Lockout', 'better-wp-security' );
		} elseif ( 'username-lockout' === $code ) {
			if ( isset( $data[0] ) ) {
				$entry['description'] = sprintf( wp_kses( __( 'Username Lockout: <code>%s</code>', 'better-wp-security' ), array( 'code' => array() ) ), $data[0] );
			} else {
				$entry['description'] = esc_html__( 'Username Lockout', 'better-wp-security' );
			}
		}

		return $entry;
	}

}
