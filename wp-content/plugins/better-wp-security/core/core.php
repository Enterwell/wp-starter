<?php

use iThemesSecurity\User_Groups;
use iThemesSecurity\Lib;

/**
 * Solid Security Core.
 *
 * Core class for Solid Security sets up globals and other items and dispatches modules.
 *
 * @since   4.0
 *
 * @package iThemes_Security
 *
 * @global array  $itsec_globals Global variables for use throughout Solid Security.
 * @global object $itsec_lockout Class for handling lockouts.
 *
 */
if ( ! class_exists( 'ITSEC_Core' ) ) {

	final class ITSEC_Core {

		private static $instance = false;

		/**
		 * This number keeps track of data format changes and triggers data upgrade handlers.
		 *
		 * @access private
		 */
		private $plugin_build = 4128;

		/**
		 * Used to distinguish between a user modifying settings and the API modifying settings (such as from Sync
		 * requests).
		 *
		 * @access private
		 */
		private $interactive = false;

		private $notices_loaded = false;
		private $doing_data_upgrade = false;

		/** @var true|WP_Error|null */
		private $setup_error;

		/**
		 * True if Security was loaded via an mu-plugin.
		 *
		 * @var bool
		 */
		private $load_early = false;

		private
			$itsec_files,
			$itsec_notify,
			$notifications,
			$scheduler,
			$sync_api,
			$plugin_file,
			$plugin_dir,
			$plugin_name,
			$current_time,
			$current_time_gmt,
			$is_iwp_call,
			$wp_upload_dir,
			$login_interstitial,
			$version,
			$importing = false;


		/**
		 * Private constructor to make this a singleton
		 *
		 * @access private
		 */
		private function __construct() { }

		/**
		 * Function to instantiate our class and make it a singleton
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Loads core functionality across both admin and frontend.
		 *
		 * Creates all plugin globals, registers activation and related hooks,
		 * loads the text domain and loads all plugin modules
		 *
		 * @since  4.0
		 *
		 * @access private
		 *
		 * @param string $plugin_file The main plugin file
		 * @param string $plugin_name The plugin name
		 *
		 */
		public function init( $plugin_file, $plugin_name ) {
			global $itsec_globals, $itsec_lockout;

			$this->load_early       = defined( 'ITSEC_LOAD_EARLY' ) && ITSEC_LOAD_EARLY;
			$this->plugin_file      = $plugin_file;
			$this->plugin_dir       = dirname( $plugin_file ) . '/';
			$this->plugin_name      = $plugin_name;
			$this->current_time     = current_time( 'timestamp' );
			$this->current_time_gmt = current_time( 'timestamp', true );

			$itsec_globals = array(
				'plugin_dir'       => $this->plugin_dir,
				'current_time'     => $this->current_time,
				'current_time_gmt' => $this->current_time_gmt,
			);

			register_activation_hook( $this->plugin_file, array( 'ITSEC_Core', 'handle_activation' ) );
			register_deactivation_hook( $this->plugin_file, array( 'ITSEC_Core', 'handle_deactivation' ) );
			register_uninstall_hook( $this->plugin_file, array( 'ITSEC_Core', 'handle_uninstall' ) );

			require( $this->plugin_dir . 'core/lib/settings.php' );
			require( $this->plugin_dir . 'core/lib/storage.php' );
			require( $this->plugin_dir . 'core/modules.php' );
			require( $this->plugin_dir . 'core/lib.php' );
			require( $this->plugin_dir . 'core/lib/log.php' );
			require( $this->plugin_dir . 'core/lib/class-itsec-mutex.php' );
			require( $this->plugin_dir . 'core/rest.php' );

			$this->setup_tables();

			add_filter( 'user_has_cap', [ $this, 'user_has_cap' ], 10, 4 );

			( new ITSEC_REST() )->run();

			add_action( 'itsec-register-modules', array( $this, 'register_modules' ) );
			ITSEC_Modules::init_modules();

			require( $this->plugin_dir . 'core/lockout.php' );
			require( $this->plugin_dir . 'core/files.php' );
			require( $this->plugin_dir . 'core/notify.php' );
			require( $this->plugin_dir . 'core/response.php' );
			require( $this->plugin_dir . 'core/lib/class-itsec-lib-user-activity.php' );
			require( $this->plugin_dir . 'core/lib/class-itsec-lib-password-requirements.php' );
			require( $this->plugin_dir . 'core/lib/class-itsec-lib-login-interstitial.php' );
			require( $this->plugin_dir . 'core/lib/class-itsec-lib-distributed-storage.php' );
			require( $this->plugin_dir . 'core/lib/class-itsec-lib-remote-messages.php' );

			require( $this->plugin_dir . 'core/lib/class-itsec-scheduler.php' );
			require( $this->plugin_dir . 'core/lib/class-itsec-job.php' );

			$this->itsec_files  = ITSEC_Files::get_instance();
			$this->itsec_notify = new ITSEC_Notify();
			$itsec_lockout      = new ITSEC_Lockout();

			if ( ITSEC_Core::is_loading_early() ) {
				$this->early_init();
			}

			add_action( 'plugins_loaded', array( $this, 'compat_checks' ), - 150 );
			add_action( 'plugins_loaded', array( $this, 'handle_upgrade' ), - 100, 0 );
			add_action( 'plugins_loaded', array( $this, 'continue_init' ), - 90 );

			add_action( 'itsec_scheduled_clear-locks', array( 'ITSEC_Lib', 'delete_expired_locks' ) );
			add_action( 'itsec_scheduled_clear-tokens', array( ITSEC_Lib_Opaque_Tokens::class, 'delete_expired_tokens' ) );
			add_action( 'itsec_before_import', function () {
				$this->importing = true;
			} );
			add_action( 'itsec_after_import', function () {
				$this->importing = false;
			} );
		}

		/**
		 * Shared initialization that needs to happen for early and normal loading.
		 */
		private function shared_init() {
			global $itsec_lockout;

			$this->setup_scheduler();
			ITSEC_Modules::run_active_modules();

			$itsec_lockout->run();
		}

		/**
		 * Performs initialization for the plugin when it is being loaded by an MU Plugin.
		 *
		 * @return void
		 */
		private function early_init() {
			// We don't want to run our upgrade routines early, and it's not safe to run
			// Security without the upgrade routine having been run. So skip the early
			// loading and fallback to a normal load for this request.
			if ( self::needs_upgrade() ) {
				self::get_instance()->load_early = false;

				return;
			}

			$this->shared_init();
		}

		/**
		 * Perform initialization that requires the plugins_loaded hook to be fired.
		 */
		public function continue_init() {
			if ( is_wp_error( $this->setup_error ) ) {
				add_action( 'all_admin_notices', function () {
					if ( ! current_user_can( is_multisite() ? 'manage_network_options' : 'manage_options' ) ) {
						return;
					}

					echo '<div class="notice notice-error">';
					echo '<p>';
					esc_html_e( 'Cannot run Solid Security. Error encountered during setup. Please try deactivating and reactivating Solid Security. Contact support if the error persists.', 'better-wp-security' );
					echo '</p>';

					echo '<ol>';
					foreach ( ITSEC_Lib::get_error_strings( $this->setup_error ) as $string ) {
						echo '<li>' . $string . '</li>';
					}
					echo '</ol>';
					echo '</div>';
				} );

				return;
			}

			// If this wasn't an early load, we need to do the shared initialization.
			if ( ! self::is_loading_early() ) {
				$this->shared_init();
			}

			if ( is_admin() ) {
				require( $this->plugin_dir . 'core/admin-pages/init.php' );

				add_filter( 'plugin_action_links', array( $this, 'add_action_link' ), 10, 2 );
				add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 4 );
			}

			add_action( 'wp_login_failed', array( 'ITSEC_Lib', 'handle_wp_login_failed' ) );
			add_action( 'ithemes_sync_register_verbs', array( $this, 'register_sync_verbs' ) );

			ITSEC_Modules::get_container()->get( Lib\Tools\Tools_Runner::class )->run();

			$this->login_interstitial = new ITSEC_Lib_Login_Interstitial();
			$this->login_interstitial->run();

			if ( defined( 'ITSEC_USE_CRON' ) && ITSEC_USE_CRON !== ITSEC_Lib::use_cron() ) {
				ITSEC_Modules::set_setting( 'global', 'use_cron', ITSEC_USE_CRON );
			}

			do_action( 'itsec_initialized' );

			Lib\User_Actions_Background_Process::run_processes();
			ITSEC_Lib_Remote_Messages::init();
			$this->run_integrations();
		}

		/**
		 * Register our tables with {@see wpdb}.
		 */
		private function setup_tables() {
			global $wpdb;

			$wpdb->global_tables = array_merge( $wpdb->global_tables, ITSEC_Schema::get_table_names() );
		}

		private function setup_scheduler() {

			if ( $this->scheduler ) {
				return;
			}

			$choices = array(
				'ITSEC_Scheduler_Cron'      => $this->plugin_dir . 'core/lib/class-itsec-scheduler-cron.php',
				'ITSEC_Scheduler_Page_Load' => $this->plugin_dir . 'core/lib/class-itsec-scheduler-page-load.php',
			);

			if ( ITSEC_Lib::use_cron() ) {
				$class = 'ITSEC_Scheduler_Cron';
			} else {
				$class = 'ITSEC_Scheduler_Page_Load';
			}

			require_once( $choices[ $class ] );

			$this->scheduler = new $class();
			self::get_scheduler()->run();
		}

		/**
		 * Handle dynamically granting the 'itsec_manage' cap to users in the necessary group.
		 *
		 * @param array   $has_caps
		 * @param array   $requested_caps
		 * @param array   $args
		 * @param WP_User $user
		 *
		 * @return array
		 */
		public function user_has_cap( $has_caps, $requested_caps, $args, $user ) {
			if ( ! in_array( 'itsec_manage', $requested_caps, true ) ) {
				return $has_caps;
			}

			if ( isset( $has_caps['itsec_manage'] ) ) {
				return $has_caps;
			}

			if (
				is_wp_error( $this->setup_error ) ||
				self::is_temp_disable_modules_set() ||
				! ITSEC_Modules::get_container()->has( User_Groups\Matcher::class ) ||
				! $this->has_valid_manage_groups()
			) {
				$has_caps['itsec_manage'] = $user->has_cap( is_multisite() ? 'manage_network_options' : 'manage_options' );

				return $has_caps;
			}

			$group   = ITSEC_Modules::get_setting( 'global', 'manage_group' );
			$matcher = ITSEC_Modules::get_container()->get( User_Groups\Matcher::class );

			if ( ! $matcher->matches( User_Groups\Match_Target::for_user( $user ), $group ) ) {
				return $has_caps;
			}

			$has_caps['itsec_manage'] = true;

			return $has_caps;
		}

		/**
		 * Check if there are valid "Manage groups" selected.
		 *
		 * @return bool
		 */
		private function has_valid_manage_groups() {
			$source = ITSEC_Modules::get_container()->get( User_Groups\Matchables_Source::class );
			$groups = ITSEC_Modules::get_setting( 'global', 'manage_group' );

			foreach ( $groups as $group ) {
				if ( $source->has( $group ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Get the required capability to manage ITSEC.
		 *
		 * @return string
		 */
		public static function get_required_cap() {
			return apply_filters( 'itsec_cap_required', 'itsec_manage' );
		}

		/**
		 * Does the current user have permission to manage ITSEC.
		 *
		 * @return bool
		 */
		public static function current_user_can_manage() {
			return current_user_can( self::get_required_cap() );
		}

		/**
		 * Checks if the user has completed the onboarding process.
		 *
		 * @return bool
		 */
		public static function is_onboarded() {
			return ITSEC_Modules::get_setting( 'global', 'onboard_complete' );
		}

		/**
		 * Retrieve the global instance of the files utility.
		 *
		 * @return ITSEC_Files
		 */
		public static function get_itsec_files() {
			$self = self::get_instance();

			return $self->itsec_files;
		}

		/**
		 * Retrieve the global instance of the user notification utility.
		 *
		 * @return ITSEC_Notify
		 */
		public static function get_itsec_notify() {
			$self = self::get_instance();

			return $self->itsec_notify;
		}

		/**
		 * Set the notification center instance.
		 *
		 * @param ITSEC_Notification_Center $center
		 */
		public static function set_notification_center( ITSEC_Notification_Center $center ) {
			self::get_instance()->notifications = $center;
		}

		/**
		 * Get the notification center instance.
		 *
		 * @return ITSEC_Notification_Center
		 */
		public static function get_notification_center() {
			return self::get_instance()->notifications;
		}

		/**
		 * Set the scheduler to use.
		 *
		 * @param ITSEC_Scheduler $scheduler
		 */
		public static function set_scheduler( ITSEC_Scheduler $scheduler ) {
			self::get_instance()->scheduler = $scheduler;
		}

		/**
		 * Get the scheduler.
		 *
		 * @return ITSEC_Scheduler
		 */
		public static function get_scheduler() {

			$self = self::get_instance();

			if ( ! $self->scheduler ) {
				$self->setup_scheduler();
			}

			return $self->scheduler;
		}

		/**
		 * Get the login interstitial library instance.
		 *
		 * @return ITSEC_Lib_Login_Interstitial
		 */
		public static function get_login_interstitial() {
			return self::get_instance()->login_interstitial;
		}

		/**
		 * Retrieve the global instance of the Sync API.
		 *
		 * The API is not available until iThemes Sync verbs have been registered ( init#11 ).
		 *
		 * @return Ithemes_Sync_API|null
		 */
		public static function get_sync_api() {
			$self = self::get_instance();

			return $self->sync_api;
		}

		/**
		 * Check if an import is in progress.
		 *
		 * @return bool
		 */
		public static function is_importing() {
			return self::get_instance()->importing;
		}

		/**
		 * Register ITSEC verbs with sync.
		 *
		 * @param Ithemes_Sync_API $sync_api
		 */
		public function register_sync_verbs( $sync_api ) {
			// For use by the itsec-get-everything verb as it has to run other verbs to get their details.
			$this->sync_api = $sync_api;

			$sync_api->register( 'itsec-get-everything', 'Ithemes_Sync_Verb_ITSEC_Get_Everything', dirname( __FILE__ ) . '/sync-verbs/itsec-get-everything.php' );
		}

		/**
		 * Register core modules.
		 */
		public function register_modules() {
			$path = dirname( __FILE__ );

			ITSEC_Modules::register_module( 'feature-flags', "$path/modules/feature-flags" );
			ITSEC_Modules::register_module( 'user-groups', "$path/modules/user-groups" );
			ITSEC_Modules::register_module( 'global', "$path/modules/global" );
			ITSEC_Modules::register_module( 'notification-center', "$path/modules/notification-center" );
			ITSEC_Modules::register_module( 'privacy', "$path/modules/privacy" );
			ITSEC_Modules::register_module( 'dashboard', "$path/modules/dashboard" );
			ITSEC_Modules::register_module( 'admin-user', "$path/modules/admin-user" );
			ITSEC_Modules::register_module( 'ban-users', "$path/modules/ban-users" );
			include( "$path/modules/ban-users/init.php" ); // Provides the itsec_ban_users_handle_new_blacklisted_ip function which is always needed.
			ITSEC_Modules::register_module( 'database-prefix', "$path/modules/database-prefix" );
			ITSEC_Modules::register_module( 'core', "$path/modules/core" );
			ITSEC_Modules::register_module( 'promos', "$path/modules/promos" );
			ITSEC_Modules::register_module( 'email-confirmation', "$path/modules/email-confirmation" );
			ITSEC_Modules::register_module( 'file-change', "$path/modules/file-change" );
			ITSEC_Modules::register_module( 'file-permissions', "$path/modules/file-permissions" );
			ITSEC_Modules::register_module( 'file-writing', "$path/modules/file-writing" );
			ITSEC_Modules::register_module( 'firewall', "$path/modules/firewall" );
			ITSEC_Modules::register_module( 'brute-force', "$path/modules/brute-force" );
			ITSEC_Modules::register_module( 'network-brute-force', "$path/modules/network-brute-force" );

			if ( ! defined( 'ITSEC_DISABLE_PASSWORD_REQUIREMENTS' ) || ! ITSEC_DISABLE_PASSWORD_REQUIREMENTS ) {
				ITSEC_Modules::register_module( 'password-requirements', "$path/modules/password-requirements/" );
			}

			ITSEC_Modules::register_module( 'ssl', "$path/modules/ssl" );

			if ( ! defined( 'BACKUPBUDDY_PLUGIN_FILE' ) || ( defined( 'ITSEC_ENABLE_BACKUPS' ) && ITSEC_ENABLE_BACKUPS ) ) {
				ITSEC_Modules::register_module( 'backup', "$path/modules/backup" );
			}

			ITSEC_Modules::register_module( 'two-factor', "$path/modules/two-factor" );
			ITSEC_Modules::register_module( 'strong-passwords', "$path/modules/strong-passwords" );
			ITSEC_Modules::register_module( 'hibp', "$path/modules/hibp" );
			ITSEC_Modules::register_module( 'system-tweaks', "$path/modules/system-tweaks" );
			ITSEC_Modules::register_module( 'wordpress-salts', "$path/modules/salts" );
			ITSEC_Modules::register_module( 'wordpress-tweaks', "$path/modules/wordpress-tweaks" );
			ITSEC_Modules::register_module( 'security-check-pro', "$path/modules/security-check-pro" );
			ITSEC_Modules::register_module( 'sync-connect', "$path/modules/sync-connect" );
			ITSEC_Modules::register_module( 'site-scanner', "$path/modules/site-scanner" );
			ITSEC_Modules::register_module( 'malware-scheduling', "$path/modules/malware-scheduling" );
			ITSEC_Modules::register_module( 'hide-backend', "$path/modules/hide-backend" );
		}

		/**
		 * Runs any global ITSEC integrations.
		 */
		private function run_integrations() {
			if ( function_exists( 'restrict_content_pro' ) ) {
				require_once self::get_core_dir() . '/integrations/rcp.php';
			}
		}

		/**
		 * Add action link to plugin page.
		 *
		 * Adds plugin settings link to plugin page in WordPress admin area.
		 *
		 * @since 4.0
		 *
		 * @param array  $links Array of WordPress links
		 * @param string $file  String name of current file
		 *
		 * @return array Array of WordPress links
		 */
		public function add_action_link( $links, $file ) {

			static $this_plugin;

			if ( empty( $this_plugin ) ) {
				$this_plugin = str_replace( WP_PLUGIN_DIR . '/', '', self::get_plugin_file() );
			}

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="' . esc_url( self::get_settings_page_url() ) . '">' . __( 'Settings', 'better-wp-security' ) . '</a>';
				array_unshift( $links, $settings_link );
			}

			return $links;
		}

		/**
		 * Adds links to the plugin row meta
		 *
		 * @since 4.0
		 *
		 * @param array  $meta        Existing meta
		 * @param string $plugin_file the wp plugin slug (path)
		 *
		 * @return array
		 */
		public function add_plugin_meta_links( $meta, $plugin_file ) {

			$plugin_base = str_replace( WP_PLUGIN_DIR . '/', '', self::get_plugin_file() );

			if ( $plugin_base == $plugin_file ) {

				$meta = apply_filters( 'itsec_meta_links', $meta );

			}

			return $meta;
		}

		/**
		 * Checks for compatibility with existing plugins,
		 * and disables the respective ITSEC modules if needed.
		 */
		public function compat_checks() {
			// The Two-Factor feature plugin and ITSEC Two-Factor cannot be used at the same time.
			if ( defined( 'TWO_FACTOR_VERSION' ) ) {
				ITSEC_Modules::deregister_module( 'two-factor' );
				ITSEC_Modules::deregister_module( 'pro-two-factor' );
			}
		}

		/**
		 * Dispatch a request to upgrade the data schema to another version.
		 *
		 * @param int|bool $build The version of the data storage format. Pass false to default to the current version.
		 *
		 * @return true|WP_Error|null
		 */
		public function handle_upgrade( $build = false ) {

			if ( func_num_args() === 0 && ! self::needs_upgrade() ) {
				return null;
			}

			$this->doing_data_upgrade = true;

			self::load_setup();
			self::get_instance()->setup_error = ITSEC_Setup::handle_upgrade( $build );

			return self::get_instance()->setup_error;
		}

		/**
		 * Checks if an upgrade routine needs to be run.
		 *
		 * @return bool
		 */
		private static function needs_upgrade(): bool {
			return self::get_saved_plugin_build() < self::get_instance()->plugin_build;
		}

		public static function handle_activation() {
			self::load_setup();
			self::get_instance()->compat_checks();
			self::get_instance()->setup_error = ITSEC_Setup::handle_activation();
		}

		public static function handle_deactivation() {
			self::load_setup();
			ITSEC_Setup::handle_deactivation();
		}

		public static function handle_uninstall() {
			self::load_setup();
			ITSEC_Setup::handle_uninstall();
		}

		private static function load_setup() {
			if ( ! class_exists( 'ITSEC_Setup' ) ) {
				require_once( self::get_core_dir() . 'setup.php' );
			}
		}

		/**
		 * Register a notice to be displayed in the WordPress admin.
		 *
		 * @param callable $callback  Function that will render a notice.
		 * @param bool     $all_pages Display the notice on all pages or only on ITSEC, plugins, and upgrade page.
		 */
		public static function add_notice( $callback, $all_pages = false ) {
			_deprecated_function( __METHOD__, '6.0.0', 'ITSEC_Lib_Admin_Notices::register' );
		}

		public static function get_plugin_file() {
			$self = self::get_instance();

			return $self->plugin_file;
		}

		public static function set_plugin_file( $plugin_file ) {
			$self              = self::get_instance();
			$self->plugin_file = $plugin_file;
			$self->plugin_dir  = dirname( $plugin_file ) . '/';
		}

		/**
		 * Get the build the plugin is running on.
		 *
		 * @return int
		 */
		public static function get_plugin_build() {
			$self = self::get_instance();

			return $self->plugin_build;
		}

		/**
		 * Get the build the database is running.
		 *
		 * @param bool $use_cache
		 *
		 * @return int
		 */
		public static function get_saved_plugin_build( $use_cache = true ) {
			if ( $use_cache ) {
				return ITSEC_Modules::get_setting( 'global', 'build' );
			}

			$storage = get_site_option( 'itsec-storage' );

			if ( ! $storage ) {
				return 0;
			}

			if ( ! isset( $storage['global']['build'] ) ) {
				return 0;
			}

			return (int) $storage['global']['build'];
		}

		public static function get_plugin_dir() {
			$self = self::get_instance();

			return $self->plugin_dir;
		}

		public static function get_core_dir() {
			return self::get_plugin_dir() . 'core/';
		}

		public static function get_plugin_name() {
			$self = self::get_instance();

			return $self->plugin_name;
		}

		/**
		 * Is this an Solid Security Pro installation.
		 *
		 * This value is not cached.
		 *
		 * @return bool
		 */
		public static function is_pro() {
			return is_dir( self::get_plugin_dir() . 'pro' );
		}

		/**
		 * Gets the installation type.
		 *
		 * @return string
		 */
		public static function get_install_type() {
			if ( defined( 'ITSEC_FORCE_INSTALL_TYPE' ) && ITSEC_FORCE_INSTALL_TYPE === 'free' ) {
				return 'free';
			}

			if ( self::is_pro() ) {
				return 'pro';
			}

			return 'free';
		}

		/**
		 * Is this an actively licensed Pro installation.
		 *
		 * @return bool
		 */
		public static function is_licensed() {
			if ( ! self::is_pro() ) {
				return false;
			}

			if ( ! isset( $GLOBALS['ithemes_updater_path'] ) ) {
				return false;
			}

			include_once( $GLOBALS['ithemes_updater_path'] . '/keys.php' );
			include_once( $GLOBALS['ithemes_updater_path'] . '/packages.php' );

			if ( ! class_exists( 'Ithemes_Updater_Packages' ) ) {
				return false;
			}

			$package_details = Ithemes_Updater_Packages::get_full_details();
			$file            = plugin_basename( self::get_plugin_file() );

			if ( empty( $package_details['packages'][ $file ]['status'] ) ) {
				return false;
			}

			if ( empty( $package_details['packages'][ $file ]['user'] ) ) {
				return false;
			}

			return 'active' === $package_details['packages'][ $file ]['status'];
		}

		/**
		 * Checks if this Pro install has access to Patchstack.
		 *
		 * @return bool
		 */
		public static function has_patchstack(): bool {
			if ( ! self::is_licensed() || 'free' === self::get_install_type() ) {
				return false;
			}

			if ( ! function_exists( 'ithemes_updater_site_has_patchstack' ) ) {
				return false;
			}

			return ithemes_updater_site_has_patchstack();
		}

		/**
		 * Gets the URL Solid Security was licensed for.
		 *
		 * @return string
		 */
		public static function get_licensed_url() {
			if ( ! self::is_licensed() || ! function_exists( 'ithemes_updater_get_licensed_site_url' ) ) {
				return '';
			}

			if ( ! ithemes_updater_is_licensed_site_url_confirmed() ) {
				return '';
			}

			$hostname = ithemes_updater_get_licensed_site_url();

			if ( ! $hostname ) {
				return '';
			}

			return $hostname;
		}

		/**
		 * Gets the username that is licensed.
		 *
		 * @return string
		 */
		public static function get_licensed_user() {
			if ( ! self::is_licensed() ) {
				return '';
			}

			if ( ! function_exists( 'ithemes_updater_get_licensed_username' ) ) {
				return '';
			}

			return ithemes_updater_get_licensed_username( 'ithemes-security-pro' );
		}

		/**
		 * Checks if the licensed user is a Liquid Web customer.
		 *
		 * @return bool
		 */
		public static function licensed_user_is_lw_customer() {
			return self::get_licensed_user() === 'liquidweb';
		}

		/**
		 * Get the current local timestamp.
		 *
		 * This value will be the same throughout the entire request.
		 *
		 * @return int
		 */
		public static function get_current_time() {
			$self = self::get_instance();

			return $self->current_time;
		}

		/**
		 * Get the current UTC timestamp.
		 *
		 * This value will be the same throughout the entire request.
		 *
		 * @return int|\DateTimeInterface
		 */
		public static function get_current_time_gmt( bool $as_object = false ) {
			$self = self::get_instance();

			return $as_object ? new \DateTimeImmutable( '@' . $self->current_time_gmt ) : $self->current_time_gmt;
		}

		/**
		 * Get the UTC offset in seconds.
		 *
		 * @return int
		 */
		public static function get_time_offset() {
			$self = self::get_instance();

			return $self->current_time - $self->current_time_gmt;
		}

		public static function get_settings_page_url() {
			$url = network_admin_url( 'admin.php?page=itsec' );

			return $url;
		}

		public static function get_logs_page_url( $filters = false, $deprecated = false ) {
			$url = network_admin_url( 'admin.php?page=itsec-logs' );

			if ( is_string( $filters ) ) {
				_deprecated_argument( __METHOD__, '6.7.0', __( 'Passing a module as a single parameter is no longer supported. Pass a filters array instead.', 'better-wp-security' ) );

				$filters           = array();
				$filters['module'] = $filters;
			}

			if ( $deprecated ) {
				_deprecated_argument( __METHOD__, '6.7.0', __( 'Passing the log type as the second parameter is no longer supported. Pass a filters array instead.', 'better-wp-security' ) );
				$filters['type'] = $deprecated;
			}

			if ( $filters ) {
				$formatted = array();

				foreach ( $filters as $filter => $value ) {
					$formatted[] = rawurlencode( "{$filter}|{$value}" );
				}

				$url = add_query_arg( array( 'filters' => $formatted ), $url );
			}

			return $url;
		}

		public static function get_backup_creation_page_url() {
			$url = self::get_settings_module_url( 'backup' );

			return apply_filters( 'itsec-filter-backup-creation-page-url', $url );
		}

		public static function get_settings_module_route( $module ) {
			$path   = '/settings/configure/';
			$config = ITSEC_Modules::get_config( $module );

			if ( ! $config ) {
				return $path;
			}

			if ( $config->get_id() === 'global' ) {
				return '/settings/global';
			}

			if ( in_array( $config->get_type(), [ 'custom', 'tool', 'recommended' ], true ) ) {
				return $path;
			}

			if ( $config->get_type() === 'advanced' ) {
				return "/settings/advanced#{$config->get_id()}";
			}

			$settings = ITSEC_Modules::get_settings_obj( $module );

			if ( $config->get_status() === 'always-active' && ! $settings->show_ui() ) {
				return $path;
			}

			return "/settings/configure/{$config->get_type()}#{$config->get_id()}";
		}

		public static function get_settings_module_url( $module ) {
			$path = self::get_settings_module_route( $module );

			return self::get_admin_page_url( 'settings', $path );
		}

		public static function get_url_for_settings_route( $path ) {
			return self::get_admin_page_url( 'settings', $path );
		}

		public static function get_link_for_settings_route( $path ) {
			$url = self::get_url_for_settings_route( $path );

			return sprintf( '<a href="%s" data-itsec-path="%s">', esc_attr( $url ), esc_attr( $path ) );
		}

		/**
		 * Gets the URL for a Tool.
		 *
		 * @deprecated 8.0.0
		 *
		 * @param string $tool
		 *
		 * @return string
		 */
		public static function get_tools_route( string $tool ): string {
			_deprecated_function( __METHOD__, '7.0.0' );

			return '';
		}

		public static function get_url_for_tools_route( string $tool ): string {
			return self::get_url_for_settings_route( '/settings/tools' ) . '#' . $tool;
		}

		/**
		 * Gets the URL for an admin page.
		 *
		 * @param string $page
		 * @param string $path
		 *
		 * @return string
		 */
		public static function get_admin_page_url( string $page, string $path = '/' ) {
			if ( $page === 'settings' || $page === '' ) {
				$page = 'itsec';
			} else {
				$page = 'itsec-' . $page;
			}

			$parts = explode( '#', $path );
			$path = $parts[0];
			$hash = '';
			if ( count( $parts ) > 1 ) {
				$hash = '#' . $parts[1];
			}

			return network_admin_url( sprintf( 'admin.php?page=%s&path=%s%s', $page, urlencode( $path ), $hash ) );
		}

		/**
		 * Gets the URL for the Security Check page.
		 *
		 * @return string
		 * @deprecated 7.0.0
		 *
		 */
		public static function get_security_check_page_url() {
			_deprecated_function( __METHOD__, '7.0.0' );

			return self::get_settings_page_url();
		}

		/**
		 * Specify whether there is a user modifying settings or if an API is modifying settings.
		 *
		 * @param bool $interactive
		 */
		public static function set_interactive( $interactive = true ) {
			$self              = self::get_instance();
			$self->interactive = (bool) $interactive;
		}

		/**
		 * Is a user modifying settings or the API modifying settings (such as from Sync requests).
		 *
		 * @return bool
		 */
		public static function is_interactive() {
			$self = self::get_instance();

			return $self->interactive;
		}

		/**
		 * Runs a callback with the given interactivity settings.
		 *
		 * @param bool     $interactive Whether to process the callback in interactive mode.
		 * @param callable $callback    The callback to execute.
		 *
		 * @return mixed The return value from callback.
		 */
		public static function with_interactivity( bool $interactive, callable $callback ) {
			$current = self::is_interactive();
			self::set_interactive( $interactive );
			$r = $callback();
			self::set_interactive( $current );

			return $r;
		}

		/**
		 * Determine whether the current request is an Infinite WP API call.
		 *
		 * @return bool
		 */
		public static function is_iwp_call() {
			return false;
		}

		/**
		 * Get the configured WordPress upload directory of the main site.
		 *
		 * This value is cached for both the lifetime of the request and possibly indefinitely when WordPress is
		 * using an object cache.
		 *
		 * @return array
		 * @see wp_upload_dir
		 *
		 */
		public static function get_wp_upload_dir() {
			$self = self::get_instance();

			if ( isset( $self->wp_upload_dir ) ) {
				return $self->wp_upload_dir;
			}

			$wp_upload_dir = get_site_transient( 'itsec_wp_upload_dir' );

			if ( ! is_array( $wp_upload_dir ) || ! isset( $wp_upload_dir['basedir'] ) || ! is_dir( $wp_upload_dir['basedir'] ) ) {
				if ( is_multisite() ) {
					switch_to_blog( 1 );
					$wp_upload_dir = wp_upload_dir();
					restore_current_blog();
				} else {
					$wp_upload_dir = wp_upload_dir();
				}

				set_site_transient( 'itsec_wp_upload_dir', $wp_upload_dir, DAY_IN_SECONDS );
			}

			$self->wp_upload_dir = $wp_upload_dir;

			return $self->wp_upload_dir;
		}

		/**
		 * Set a new upload directory and ensure the previously cached value is cleared.
		 *
		 * @param string $old_dir
		 * @param string $new_dir
		 */
		public static function update_wp_upload_dir( $old_dir, $new_dir ) {
			$self = self::get_instance();

			// Prime caches.
			self::get_wp_upload_dir();

			$self->wp_upload_dir = str_replace( $old_dir, $new_dir, $self->wp_upload_dir );

			// Ensure that the transient will be regenerated on the next page load.
			delete_site_transient( 'itsec_wp_upload_dir' );
		}

		/**
		 * Retrieve and/or create a directory for ITSEC to store data.
		 *
		 * @param string $dir    Optionally specify an additional sub-directory.
		 * @param bool   $public Whether to get the public version of the directory.
		 *
		 * @return string
		 */
		public static function get_storage_dir( $dir = '', $public = false ) {
			$wp_upload_dir = self::get_wp_upload_dir();

			$storage_dir = $wp_upload_dir['basedir'];

			if ( $public ) {
				$storage_dir .= '/ithemes-security-public/';
			} else {
				$storage_dir .= '/ithemes-security/';
			}

			$dir = $storage_dir . $dir;
			$dir = rtrim( $dir, '/' );

			ITSEC_Lib_Directory::create( $dir );

			return $dir;
		}

		/**
		 * Get the URL to the directory that ITSEC stores data in.
		 *
		 * @param string $dir
		 * @param bool   $public Whether to get the public version of the directory.
		 *
		 * @return string
		 */
		public static function get_storage_url( $dir = '', $public = false ) {

			self::get_storage_dir( $dir );

			$upload_dir = self::get_wp_upload_dir();
			$base       = untrailingslashit( $upload_dir['baseurl'] );

			$url = $base;

			if ( $public ) {
				$url .= '/ithemes-security-public/';
			} else {
				$url .= '/ithemes-security/';
			}

			return $url . $dir;
		}

		public static function doing_data_upgrade() {
			$self = self::get_instance();

			return $self->doing_data_upgrade;
		}

		public static function is_ajax_request() {
			if ( function_exists( 'wp_doing_ajax' ) ) {
				return wp_doing_ajax();
			}

			return defined( 'DOING_AJAX' ) && DOING_AJAX;
		}

		public static function is_xmlrpc_request() {
			return defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST;
		}

		/**
		 * Is this a WordPress REST API request.
		 *
		 * This function is suitable to be used immediately, not just after REST_REQUEST has been defined.
		 *
		 * @return bool
		 */
		public static function is_rest_api_request() {
			if ( isset( $GLOBALS['__itsec_core_is_rest_api_request'] ) ) {
				return $GLOBALS['__itsec_core_is_rest_api_request'];
			}

			if ( ! function_exists( 'rest_get_url_prefix' ) ) {
				$GLOBALS['__itsec_core_is_rest_api_request'] = false;

				return false;
			}

			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				$GLOBALS['__itsec_core_is_rest_api_request'] = true;

				return true;
			}

			$home_path = parse_url( get_option( 'home' ), PHP_URL_PATH ) ?: '';
			$home_path = trim( $home_path, '/' );

			if ( '' === $home_path ) {
				$rest_api_path = '/' . rest_get_url_prefix() . '/';
			} else {
				$rest_api_path = "/$home_path/" . rest_get_url_prefix() . '/';
			}

			if ( 0 === strpos( $_SERVER['REQUEST_URI'], $rest_api_path ) ) {
				$GLOBALS['__itsec_core_is_rest_api_request'] = true;
			} else {
				$GLOBALS['__itsec_core_is_rest_api_request'] = false;
			}

			return $GLOBALS['__itsec_core_is_rest_api_request'];
		}

		/**
		 * Is this a request to wp-admin/admin-post.php?
		 *
		 * @return bool
		 */
		public static function is_admin_post_php_request() {
			if ( 'wp-admin/admin-post.php' === ITSEC_Lib::get_request_path() ) {
				return true;
			}

			return false;
		}

		/**
		 * Is the current request being made by a WordPress API.
		 *
		 * @param bool $include_ajax           Whether to include Ajax requests as a subset of API requests.
		 * @param bool $include_admin_post_php Whether to include wp-admin/admin-post.php requests as a subset of API
		 *                                     requests.
		 *
		 * @return bool
		 */
		public static function is_api_request( $include_ajax = true, $include_admin_post_php = true ) {
			if ( $include_ajax && self::is_ajax_request() ) {
				return true;
			}

			if ( $include_admin_post_php && self::is_admin_post_php_request() ) {
				return true;
			}

			if ( self::is_rest_api_request() || self::is_xmlrpc_request() ) {
				return true;
			}

			return false;
		}

		/**
		 * Checks if Solid Security is in development mode.
		 *
		 * @return bool
		 */
		public static function is_development() {
			return defined( 'ITSEC_DEVELOPMENT' ) && ITSEC_DEVELOPMENT;
		}

		/**
		 * Check to see if the define to disable all active modules is set.
		 *
		 * Note that the ITSEC_DISABLE_MODULES should only be used to gain access to a site that you are locked out of.
		 * Once logged in, you should remove the define to re-enable the protections offered by Solid Security.
		 *
		 * @return bool true if the define is set to a truthy value, false otherwise.
		 */
		public static function is_temp_disable_modules_set() {
			if ( defined( 'ITSEC_DISABLE_MODULES' ) && ITSEC_DISABLE_MODULES ) {
				return true;
			}

			return false;
		}

		/**
		 * Get the actual version string of the plugin.
		 *
		 * This should be used rarely. Any decision flows based off a "version" should be made using the Plugin Build.
		 *
		 * @return string
		 */
		public static function get_plugin_version() {
			$self = self::get_instance();

			if ( ! isset( $self->version ) ) {
				$data = get_file_data( $self->plugin_file, array(
					'Version' => 'Version'
				) );

				$self->version = $data['Version'];
			}

			return $self->version;
		}

		/**
		 * Checks if Security was loaded as a mu-plugin.
		 *
		 * @return bool
		 */
		public static function is_loading_early(): bool {
			return self::get_instance()->load_early;
		}

		/**
		 * Gets the PHP version that is going to be required soon.
		 *
		 * @return string
		 */
		public static function get_next_php_requirement(): string {
			return '7.3.0';
		}

		/**
		 * Gets the UTM campaign based on the Install Type.
		 *
		 * @return string
		 */
		public static function get_utm_campaign(): string {
			return self::is_pro() ? 'itsecprocta' : 'itsecfreecta';
		}

		/**
		 * Gets a link configured for Google Analytics tracking.
		 *
		 * @param string $link
		 * @param string $source
		 * @param string $medium
		 *
		 * @return string
		 */
		public static function get_tracking_link( string $link, string $source, string $medium ): string {
			return add_query_arg( [
				'utm_source'   => $source,
				'utm_medium'   => $medium,
				'utm_campaign' => self::get_utm_campaign(),
			], $link );
		}

		public static function is_test_suite( $suite = '' ) {
			if ( ! defined( 'ITSEC_TEST_SUITE' ) ) {
				return false;
			}

			return $suite ? ITSEC_TEST_SUITE === $suite : true;
		}
	}
}
