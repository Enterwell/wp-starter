<?php

use iThemesSecurity\TwoFactor\Application_Passwords_Core;
use iThemesSecurity\User_Groups\Matcher;
use iThemesSecurity\User_Groups;

/**
 * Two-Factor Execution
 *
 * Handles all two-factor execution once the feature has been
 * enabled by the user.
 *
 * @since   1.2.0
 *
 * @package iThemes_Security
 */
class ITSEC_Two_Factor {
	private static $instance = false;

	/**
	 * Helper class
	 *
	 * @access private
	 * @var ITSEC_Two_Factor_Helper
	 */
	private $helper;

	/** @var User_Groups\Matcher */
	private $matcher;

	/**
	 * The user meta provider key.
	 *
	 * @access private
	 * @var string
	 */
	private $_provider_user_meta_key = '_two_factor_provider';

	/**
	 * The user meta enabled providers key.
	 *
	 * @access private
	 * @var string
	 */
	private $_enabled_providers_user_meta_key = '_two_factor_enabled_providers';

	private function __construct() {
		add_action( 'itsec_login_interstitial_init', array( $this, 'register_interstitial' ) );

		add_action( 'show_user_profile', array( $this, 'user_two_factor_options' ) );
		add_action( 'edit_user_profile', array( $this, 'user_two_factor_options' ) );
		add_action( 'personal_options_update', array( $this, 'user_two_factor_options_update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'user_two_factor_options_update' ) );

		add_filter( 'authenticate', array( $this, 'block_xmlrpc' ), 100 );
		add_filter( 'itsec_is_user_using_two_factor', array( $this, 'mark_user_as_using_2fa' ), 10, 2 );
		add_action( 'itsec_passwordless_login_initialize_interstitial', array( $this, 'pwls_skip_2fa' ), 10, 2 );
		add_filter( 'itsec_user_security_profile_data', array( $this, 'add_2fa_security_profile_data' ), 10, 2 );

		add_action( 'ithemes_sync_register_verbs', array( $this, 'register_sync_verbs' ) );
		add_filter( 'itsec-filter-itsec-get-everything-verbs', array( $this, 'register_sync_get_everything_verbs' ) );

		add_action( 'load-profile.php', array( $this, 'add_profile_page_styling' ) );
		add_action( 'load-user-edit.php', array( $this, 'add_profile_page_styling' ) );

		add_filter( 'itsec_notifications', array( $this, 'register_notifications' ) );
		add_filter( 'itsec_two-factor-email_notification_strings', array( $this, 'two_factor_email_method_strings' ) );
		add_filter( 'itsec_two-factor-confirm-email_notification_strings', array( $this, 'two_factor_confirm_email_method_strings' ) );

		$this->matcher = ITSEC_Modules::get_container()->get( Matcher::class );
		$this->load_helper();

		( new Application_Passwords_Core() )->run();
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function load_helper() {
		if ( ! isset( $this->helper ) ) {
			require_once( dirname( __FILE__ ) . '/class-itsec-two-factor-helper.php' );
			$this->helper = ITSEC_Two_Factor_Helper::get_instance();
		}
	}

	/**
	 * Register verbs for Sync.
	 *
	 * @since 3.6.0
	 *
	 * @param Ithemes_Sync_API Sync API object.
	 */
	public function register_sync_verbs( $api ) {
		$api->register( 'itsec-get-two-factor-users', 'Ithemes_Sync_Verb_ITSEC_Get_Two_Factor_Users', dirname( __FILE__ ) . '/sync-verbs/itsec-get-two-factor-users.php' );
		$api->register( 'itsec-override-two-factor-user', 'Ithemes_Sync_Verb_ITSEC_Override_Two_Factor_User', dirname( __FILE__ ) . '/sync-verbs/itsec-override-two-factor-user.php' );
		$api->register( 'itsec-authorize-two-factor-user', 'Ithemes_Sync_Verb_ITSEC_Authorize_Two_Factor_User', dirname( __FILE__ ) . '/sync-verbs/itsec-authorize-two-factor-user.php' );
	}

	/**
	 * Filter to add verbs to the response for the itsec-get-everything verb.
	 *
	 * @since 3.6.0
	 *
	 * @param array Array of verbs.
	 *
	 * @return array Array of verbs.
	 */
	public function register_sync_get_everything_verbs( $verbs ) {
		$verbs['two_factor'][] = 'itsec-get-two-factor-users';

		return $verbs;
	}

	/**
	 * Add user profile fields.
	 *
	 * This executes during the `show_user_profile` & `edit_user_profile` actions.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 */
	public function user_two_factor_options( $user ) {
		$this->load_helper();

		$allowed_providers = $this->get_allowed_provider_instances_for_user( $user );

		if ( ! $allowed_providers ) {
			return;
		}

		$enabled_providers = get_user_meta( $user->ID, $this->_enabled_providers_user_meta_key, true );

		if ( ! $enabled_providers ) {
			$enabled_providers = array();
		}

		$primary_provider = get_user_meta( $user->ID, $this->_provider_user_meta_key, true );

		wp_nonce_field( 'user_two_factor_options', '_nonce_user_two_factor_options', false );
		?>
		<h3 id="two-factor-user-options"><?php esc_html_e( 'Two-Factor Authentication Options', 'better-wp-security' ); ?></h3>
		<p><?php esc_html_e( 'Enabling two-factor authentication greatly increases the security of your user account on this site. With two-factor authentication enabled, after you login with your username and password, you will be asked for an authentication code before you can successfully log in.', 'better-wp-security' ); ?>
			<strong> <?php esc_html_e( 'Two-factor authentication codes can come from an app that runs on your mobile device, an email that is sent to you after you login with your username and password, or from a pre-generated list of codes.', 'better-wp-security' ); ?></strong> <?php esc_html_e( 'The settings below allow you to configure which of these authentication code providers are enabled for your user.', 'better-wp-security' ); ?>
		</p>

		<table class="two-factor-methods-table widefat wp-list-table striped">
			<thead>
			<tr>
				<th scope="col" class="manage-column column-primary column-method"><?php esc_html_e( 'Provider', 'better-wp-security' ); ?></th>
				<th scope="col" class="manage-column column-enable"><?php esc_html_e( 'Enabled', 'better-wp-security' ); ?></th>
				<th scope="col" class="manage-column column-make-primary"><?php esc_html_e( 'Primary', 'better-wp-security' ); ?></th>
			</tr>
			</thead>
			<tbody id="the-list">
			<?php foreach ( $allowed_providers as $class => $object ) : ?>
				<tr>
					<td class="column-method column-primary" style="width:60%;vertical-align:top;">
						<strong><?php $object->print_label(); ?></strong>
						<?php do_action( 'two-factor-user-options-' . $class, $user ); ?>
						<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>
					</td>
					<td class="column-enable" style="width:20%;vertical-align:top;">
						<input type="checkbox" name="<?php echo esc_attr( $this->_enabled_providers_user_meta_key ); ?>[]"
							   id="<?php echo esc_attr( $this->_enabled_providers_user_meta_key . '-' . $class ); ?>"
							   value="<?php echo esc_attr( $class ); ?>" <?php checked( in_array( $class, $enabled_providers ) ); ?> />
						<label for="<?php echo esc_attr( $this->_enabled_providers_user_meta_key . '-' . $class ); ?>">
							<?php esc_html_e( 'Enable', 'better-wp-security' ) ?>
							<?php
							if ( $object->recommended ) {
								echo ' <strong>' . __( '(recommended)', 'better-wp-security' ) . '</strong>';
							}
							?>
						</label>
					</td>
					<td class="column-make-primary" style="width:20%;vertical-align:top;">
						<input type="radio" name="<?php echo esc_attr( $this->_provider_user_meta_key ); ?>" value="<?php echo esc_attr( $class ); ?>"
							   id="<?php echo esc_attr( $this->_provider_user_meta_key . '-' . $class ); ?>" <?php checked( $class, $primary_provider ); ?> />
						<label for="<?php echo esc_attr( $this->_provider_user_meta_key . '-' . $class ); ?>">
							<?php esc_html_e( 'Make Primary', 'better-wp-security' ) ?>
							<?php
							if ( $object->recommended ) {
								echo ' <strong>' . __( '(recommended)', 'better-wp-security' ) . '</strong>';
							}
							?>
						</label>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			<tfoot>
			<tr>
				<th scope="col" class="manage-column column-primary column-method"><?php esc_html_e( 'Method', 'better-wp-security' ); ?></th>
				<th scope="col" class="manage-column column-enable"><?php esc_html_e( 'Enabled', 'better-wp-security' ); ?></th>
				<th scope="col" class="manage-column column-make-primary"><?php esc_html_e( 'Primary', 'better-wp-security' ); ?></th>
			</tr>
			</tfoot>
		</table>
		<?php
		/**
		 * Fires after the Two Factor methods table.
		 *
		 * To be used by Two Factor methods to add settings UI.
		 */
		do_action( 'show_user_security_settings', $user );
	}

	/**
	 * Update the user meta value.
	 *
	 * This executes during the `personal_options_update` & `edit_user_profile_update` actions.
	 *
	 * @param int $user_id User ID.
	 */
	public function user_two_factor_options_update( $user_id ) {
		$this->load_helper();

		if ( isset( $_POST['_nonce_user_two_factor_options'] ) ) {
			check_admin_referer( 'user_two_factor_options', '_nonce_user_two_factor_options' );
			$providers = $this->helper->get_enabled_provider_instances();
			// If there are no providers enabled for the site, then let's not worry about this.
			if ( empty( $providers ) ) {
				return;
			}

			$enabled_providers = isset( $_POST[ $this->_enabled_providers_user_meta_key ] ) ? $_POST[ $this->_enabled_providers_user_meta_key ] : array();
			$this->set_enabled_providers_for_user( $enabled_providers, $user_id );

			// Whitelist the new values to only the available classes and empty.
			$primary_provider = isset( $_POST[ $this->_provider_user_meta_key ] ) ? $_POST[ $this->_provider_user_meta_key ] : '';
			$this->set_primary_provider_for_user( $primary_provider, $user_id );
		}
	}

	/**
	 * Block XML-RPC login requests for users using Two-Factor.
	 *
	 * @param WP_User|WP_Error|null $user_or_error
	 *
	 * @return WP_Error|WP_User|null
	 */
	public function block_xmlrpc( $user_or_error ) {
		global $wp_rest_application_password_status;

		if ( ! defined( 'XMLRPC_REQUEST' ) || ! XMLRPC_REQUEST ) {
			return $user_or_error;
		}

		if ( ! $user_or_error instanceof WP_User ) {
			return $user_or_error;
		}

		if ( ! $this->is_user_using_two_factor( $user_or_error->ID ) ) {
			return $user_or_error;
		}

		if ( $wp_rest_application_password_status instanceof WP_User && $wp_rest_application_password_status->ID === $user_or_error->ID ) {
			return $user_or_error;
		}

		return new WP_Error( 'two_factor_required', esc_html__( 'User has Two-Factor enabled.', 'better-wp-security' ) );
	}

	/**
	 * Marks a user as using Two-Factor.
	 *
	 * @param bool    $is_using
	 * @param WP_User $user
	 *
	 * @return bool
	 */
	public function mark_user_as_using_2fa( $is_using, WP_User $user ) {
		if ( ! $is_using ) {
			$is_using = (bool) $this->get_primary_provider_for_user( $user->ID );
		}

		return $is_using;
	}

	/**
	 * Conditionally skips the Two-Factor interstitial when using Passwordless Login
	 * if the user's primary provider is Email.
	 *
	 * @param ITSEC_Login_Interstitial_Session $session
	 * @param array                            $args
	 */
	public function pwls_skip_2fa( ITSEC_Login_Interstitial_Session $session, $args ) {
		if (
			$args['method'] === 'magic' &&
			self::get_instance()->get_primary_provider_for_user( $session->get_user()->ID ) instanceof Two_Factor_Email
		) {
			$session->add_completed_interstitial( '2fa' );
		}
	}

	/**
	 * Adds Two-Factor data to the User Security Profile card.
	 *
	 * @param array   $data
	 * @param WP_User $user
	 *
	 * @return array
	 */
	public function add_2fa_security_profile_data( $data, WP_User $user ) {
		if ( $this->get_available_providers_for_user( $user, false ) ) {
			$data['two_factor'] = 'enabled';
		} elseif ( $this->get_available_providers_for_user( $user, true ) ) {
			$data['two_factor'] = 'enforced-not-configured';
		} else {
			$data['two_factor'] = 'not-enabled';
		}

		return $data;
	}

	/**
	 * Update the list of enabled Two Factor providers for a user.
	 *
	 * @param array    $enabled_providers
	 * @param int|null $user_id
	 *
	 * @return bool
	 */
	public function set_enabled_providers_for_user( $enabled_providers, $user_id = null ) {
		$this->load_helper();

		$providers = $this->helper->get_enabled_providers();

		// If there are no providers enabled for the site, then let's not worry about this.
		if ( empty( $providers ) ) {
			return false;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! is_array( $enabled_providers ) ) {
			// Make sure enabled providers is an array
			$enabled_providers = array();
		} else {
			// Only site-enabled providers can be enabled for a user
			$enabled_providers = array_intersect( $enabled_providers, array_keys( $providers ) );
		}

		return (bool) update_user_meta( $user_id, $this->_enabled_providers_user_meta_key, $enabled_providers );
	}

	/**
	 * Set the primary provider for a user.
	 *
	 * @param string   $primary_provider
	 * @param int|null $user_id
	 *
	 * @return bool
	 */
	public function set_primary_provider_for_user( $primary_provider, $user_id = null ) {
		$this->load_helper();

		$providers = $this->helper->get_enabled_providers();

		// If there are no providers enabled for the site, then let's not worry about this.
		if ( ! $providers ) {
			return false;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $primary_provider ) || array_key_exists( $primary_provider, $providers ) ) {
			return (bool) update_user_meta( $user_id, $this->_provider_user_meta_key, $primary_provider );
		}

		return false;
	}

	/**
	 * Get a list of the allowed providers for a user.
	 *
	 * @param WP_User $user
	 *
	 * @return string[]
	 */
	public function get_allowed_providers_for_user( $user = null ) {
		if ( ! $user instanceof WP_User ) {
			$user = wp_get_current_user();
		}

		if ( ! $user instanceof WP_User || ! $user->exists() ) {
			return array();
		}

		$this->load_helper();

		$providers = array_keys( $this->helper->get_enabled_providers() );

		/**
		 * Filter the list of allowed providers for a user.
		 *
		 * @param string[] $providers Provider classes.
		 * @param WP_User  $user
		 */
		return apply_filters( 'itsec_two_factor_allowed_providers_for_user', $providers, $user );
	}

	/**
	 * Get the allowed provider instances for a given user.
	 *
	 * @param WP_User|null $user
	 *
	 * @return Two_Factor_Provider[]
	 */
	public function get_allowed_provider_instances_for_user( $user = null ) {
		$classes   = $this->get_allowed_providers_for_user( $user );
		$instances = array();

		foreach ( $classes as $class ) {
			if ( $provider = $this->helper->get_provider_instance( $class ) ) {
				$instances[ $class ] = $provider;
			}
		}

		return $instances;
	}

	/**
	 * Get all Two-Factor Auth providers that are enabled for the specified|current user.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 *
	 * @return string[]
	 */
	public function get_enabled_providers_for_user( $user = null ) {
		$this->load_helper();

		if ( ! $user instanceof WP_User ) {
			$user = wp_get_current_user();
		}

		$allowed = $this->get_allowed_providers_for_user( $user );
		$enabled = get_user_meta( $user->ID, $this->_enabled_providers_user_meta_key, true );

		if ( ! $enabled ) {
			$enabled = array();
		}

		$enabled = array_intersect( $enabled, $allowed );

		return $enabled;
	}

	/**
	 * Get all Two-Factor Auth providers that are both enabled and configured for the specified|current user.
	 *
	 * @param WP_User $user         WP_User object of the logged-in user.
	 * @param bool    $add_enforced Whether to add in the email provider if 2fa is enforced for the user's account.
	 *
	 * @return Two_Factor_Provider[]
	 */
	public function get_available_providers_for_user( $user = null, $add_enforced = true ) {
		$this->load_helper();

		if ( ! $user instanceof WP_User ) {
			$user = wp_get_current_user();
		}

		if ( ! $user instanceof WP_User || ! $user->exists() ) {
			return array();
		}

		$enabled    = $this->get_enabled_providers_for_user( $user );
		$configured = array();

		foreach ( $enabled as $classname ) {
			$provider = $this->helper->get_provider_instance( $classname );

			if ( $provider && $provider->is_available_for_user( $user ) ) {
				$configured[ $classname ] = $provider;
			}
		}

		if (
			$add_enforced &&
			! isset( $configured['Two_Factor_Email'] ) &&
			array_key_exists( 'Two_Factor_Email', $this->helper->get_enabled_providers() ) &&
			$this->user_requires_two_factor( $user->ID )
		) {
			$configured['Two_Factor_Email'] = $this->helper->get_provider_instance( 'Two_Factor_Email' );
		}

		/**
		 * Filters all of the available providers for a given user.
		 *
		 * @param Two_Factor_Provider[] $configured
		 * @param WP_User               $user
		 * @param bool                  $add_enforced
		 */
		return apply_filters( 'itsec_two_factor_available_providers_for_user', $configured, $user, $add_enforced );
	}

	/**
	 * Get the reason that two factor is required for a given user.
	 *
	 * 'user_type' - Required because all users are required, their role requires it, or they are a privileged user.
	 * 'vulnerable_users' - Required because they have a weak password.
	 * 'vulnerable_site' - Required because the site is running outdated versions of plugins.
	 *
	 * @param int|null $user_id
	 *
	 * @return string|null|false
	 */
	public function get_two_factor_requirement_reason( $user_id = null ) {
		$this->load_helper();

		if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$providers = $this->helper->get_enabled_provider_instances();

		if ( ! isset( $providers['Two_Factor_Email'] ) ) {
			// Two-factor can't be a requirement if the Email method is not available.
			return false;
		}

		$user = get_userdata( $user_id );

		if ( ! $user instanceof WP_User ) {
			return false;
		}

		/**
		 * Filters the reason that Two-Factor is required for a user.
		 *
		 * @param string|null $reason
		 * @param WP_User     $user
		 */
		return apply_filters( 'itsec_two_factor_requirement_reason', null, $user );
	}

	/**
	 * Is the user excluded from Two-Factor authentication.
	 *
	 * @param int|WP_User|string $user
	 *
	 * @return bool
	 */
	public function is_user_excluded( $user ) {
		if ( ! $user = ITSEC_Lib::get_user( $user ) ) {
			return false;
		}

		$groups = ITSEC_Modules::get_setting( 'two-factor', 'exclude_group' );

		return $this->matcher->matches( User_Groups\Match_Target::for_user( $user ), $groups );
	}

	/**
	 * Get a description for the reason Two Factor is required.
	 *
	 * @param string $reason
	 *
	 * @return string
	 */
	public function get_reason_description( $reason ) {
		/**
		 * Filters the description for the reason Two-Factor is required.
		 *
		 * @param string $reason
		 * @param string $description
		 */
		return apply_filters( 'itsec_two_factor_requirement_reason_description', $reason, '' );
	}

	/**
	 * Does the given user require Two Factor to be enabled.
	 *
	 * @param int|null $user_id
	 *
	 * @return bool
	 */
	public function user_requires_two_factor( $user_id = null ) {
		$reason = $this->get_two_factor_requirement_reason( $user_id );

		return (bool) $reason;
	}

	/**
	 * Gets the Two-Factor Auth provider for the specified|current user.
	 *
	 * @param int $user_id Optional. User ID. Default is 'null'.
	 *
	 * @return Two_Factor_Provider|null
	 */
	public function get_primary_provider_for_user( $user_id = null ) {
		$this->load_helper();

		if ( ! $user_id || ! is_numeric( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$user_providers = $this->get_available_providers_for_user( get_userdata( $user_id ) );

		if ( ! $user_providers ) {
			return null;
		}

		if ( 1 === count( $user_providers ) ) {
			$provider = key( $user_providers );
		} else {
			$provider = get_user_meta( $user_id, $this->_provider_user_meta_key, true );

			// If the provider specified isn't enabled, just grab the first one that is.
			if ( ! $provider || ! isset( $user_providers[ $provider ] ) ) {
				$provider = key( $user_providers );
			}
		}

		/**
		 * Filter the two-factor authentication provider used for this user.
		 *
		 * @param string $provider The provider currently being used.
		 * @param int    $user_id  The user ID.
		 */
		$provider = apply_filters( 'two_factor_primary_provider_for_user', $provider, $user_id );

		return $this->helper->get_provider_instance( $provider );
	}

	/**
	 * Quick boolean check for whether a given user is using two-step.
	 *
	 * @param int $user_id Optional. User ID. Default is 'null'.
	 *
	 * @return bool|null True if they are using it. False if not using it. Null if disabled site-wide.
	 */
	public function is_user_using_two_factor( $user_id = null ) {
		if ( defined( 'ITSEC_DISABLE_TWO_FACTOR' ) && ITSEC_DISABLE_TWO_FACTOR ) {
			return null;
		}

		return (bool) $this->get_primary_provider_for_user( $user_id );
	}

	/**
	 * Determine if a Sync Two-Factor override is active.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return bool True if the override is active. False otherwise.
	 */
	public function is_sync_override_active( $user_id ) {
		$sync_override = (int) get_user_option( 'itsec_two_factor_override', $user_id );

		if ( 1 !== $sync_override ) {
			return false;
		}

		$override_expires = (int) get_user_option( 'itsec_two_factor_override_expires', $user_id );

		if ( current_time( 'timestamp' ) > $override_expires ) {
			return false;
		}

		$post_data = $_POST;
		ITSEC_Log::add_debug( 'two_factor', "sync_override::$user_id", compact( 'user_id', 'sync_override', 'override_expires', 'post_data' ), compact( 'user_id' ) );

		return true;
	}

	/**
	 * Register the 2fa interstitial.
	 *
	 * @param ITSEC_Lib_Login_Interstitial $lib
	 */
	public function register_interstitial( $lib ) {
		require_once( dirname( __FILE__ ) . '/class-itsec-two-factor-interstitial.php' );
		require_once( dirname( __FILE__ ) . '/class-itsec-two-factor-on-board.php' );

		$interstitial = new ITSEC_Two_Factor_Interstitial( $this );
		$interstitial->run();
		$lib->register( '2fa', $interstitial );
		$lib->register( '2fa-on-board', new ITSEC_Two_Factor_On_Board( $this ) );
	}

	/**
	 * Enqueue the css/profile-page.css file.
	 */
	public function add_profile_page_styling() {
		wp_enqueue_style( 'itsec-two-factor-profile-page', plugins_url( 'css/profile-page.css', __FILE__ ), array(), ITSEC_Core::get_plugin_build() );

		$this->load_helper();
		$this->helper->get_enabled_provider_instances();
	}

	/**
	 * Register the Two Factor Email method notification.
	 *
	 * @param array $notifications
	 *
	 * @return array
	 */
	public function register_notifications( $notifications ) {

		$notifications['two-factor-email'] = array(
			'slug'             => 'two-factor-email',
			'schedule'         => ITSEC_Notification_Center::S_NONE,
			'recipient'        => ITSEC_Notification_Center::R_USER,
			'subject_editable' => true,
			'message_editable' => true,
			'tags'             => array( 'username', 'display_name', 'site_title' ),
			'module'           => 'two-factor',
		);

		$notifications['two-factor-confirm-email'] = array(
			'slug'             => 'two-factor-confirm-email',
			'schedule'         => ITSEC_Notification_Center::S_NONE,
			'recipient'        => ITSEC_Notification_Center::R_USER,
			'subject_editable' => true,
			'message_editable' => true,
			'tags'             => array( 'username', 'display_name', 'site_title' ),
			'module'           => 'two-factor',
			'optional'         => true,
		);

		return $notifications;
	}

	/**
	 * Provide translated strings for the Two Factor Email method notification.
	 *
	 * @return array
	 */
	public function two_factor_email_method_strings() {
		/* translators: Do not translate the curly brackets or their contents, those are placeholders. */
		$message = __( 'Hi {{ $display_name }},

Click the button to continue or manually enter the authentication code below to finish logging in.', 'better-wp-security' );

		return array(
			'label'       => __( 'Two-Factor Email', 'better-wp-security' ),
			'description' => sprintf(
				__( 'The %1$sTwo-Factor Authentication%2$s module sends an email containing the Authentication Code for users using email as their two-factor provider.', 'better-wp-security' ),
				ITSEC_Core::get_link_for_settings_route( ITSEC_Core::get_settings_module_route( 'two-factor' ) ),
				'</a>'
			),
			'subject'     => __( 'Login Authentication Code', 'better-wp-security' ),
			'message'     => $message,
			'tags'        => array(
				'username'     => __( "The recipient’s WordPress username.", 'better-wp-security' ),
				'display_name' => __( "The recipient’s WordPress display name.", 'better-wp-security' ),
				'site_title'   => __( 'The WordPress Site Title. Can be changed under Settings → General → Site Title', 'better-wp-security' ),
			)
		);
	}

	/**
	 * Provide translated strings for the Two Factor Confirm Email method notification.
	 *
	 * @return array
	 */
	public function two_factor_confirm_email_method_strings() {
		/* translators: Do not translate the curly brackets or their contents, those are placeholders. */
		$message = __( 'Hi {{ $display_name }},

Click the button to continue or manually enter the authentication code below to finish setting up Two-Factor.', 'better-wp-security' );

		$desc = sprintf(
			__( 'The %1$sTwo-Factor Authentication%2$s module sends an email containing the Authentication Code for users when they are setting up Two-Factor. Try to keep the email similar to the Two Factor Email.', 'better-wp-security' ),
			ITSEC_Core::get_link_for_settings_route( ITSEC_Core::get_settings_module_route( 'two-factor' ) ),
			'</a>'
		);
		$desc .= ' ' . __( 'Disabling this email will disable the Two-Factor Email Confirmation flow.', 'better-wp-security' );

		return array(
			'label'       => __( 'Two-Factor Email Confirmation', 'better-wp-security' ),
			'description' => $desc,
			'subject'     => __( 'Login Authentication Code', 'better-wp-security' ),
			'message'     => $message,
			'tags'        => array(
				'username'     => __( 'The recipient’s WordPress username.', 'better-wp-security' ),
				'display_name' => __( 'The recipient’s WordPress display name.', 'better-wp-security' ),
				'site_title'   => __( 'The WordPress Site Title. Can be changed under Settings → General → Site Title', 'better-wp-security' ),
			)
		);
	}

	public function get_helper() {
		return $this->helper;
	}

	//
	// Deprecated
	//

	/**
	 * @deprecated 7.0.0
	 */
	const REMEMBER_COOKIE = 'itsec_remember_2fa';

	/**
	 * @deprecated 7.0.0
	 */
	const REMEMBER_META_KEY = '_itsec_remember_2fa';

	/**
	 * Set the remember 2fa cookie.
	 *
	 * @param WP_User $user
	 *
	 * @return bool
	 * @deprecated 7.0.0
	 *
	 */
	public function set_remember_cookie( $user ) {
		$c   = ITSEC_Modules::get_container();
		$api = \iThemesSecurity\Pro_Two_Factor\API::class;

		_deprecated_function(
			__METHOD__,
			'7.0.0',
			$api . '::set_remember_cookie'
		);

		if ( $c->has( $api ) ) {
			return $c->get( $api )->set_remember_cookie( $user );
		}

		return false;
	}

	/**
	 * Clear the remember 2fa cookie.
	 *
	 * @return bool
	 * @deprecated 7.0.0
	 *
	 */
	public function clear_remember_cookie() {
		$c   = ITSEC_Modules::get_container();
		$api = \iThemesSecurity\Pro_Two_Factor\API::class;

		_deprecated_function(
			__METHOD__,
			'7.0.0',
			$api . '::clear_remember_cookie'
		);

		if ( $c->has( $api ) ) {
			return $c->get( $api )->clear_remember_cookie();
		}

		return false;
	}

	/**
	 * Is the user allowed to remember 2fa.
	 *
	 * @param WP_User $user
	 *
	 * @return bool
	 * @deprecated 7.0.0
	 *
	 */
	public function is_remember_allowed( $user ) {
		$c   = ITSEC_Modules::get_container();
		$api = \iThemesSecurity\Pro_Two_Factor\API::class;

		_deprecated_function(
			__METHOD__,
			'7.0.0',
			$api . '::is_remember_allowed'
		);

		if ( $c->has( $api ) ) {
			return $c->get( $api )->is_remember_allowed( $user );
		}

		return false;
	}
}
