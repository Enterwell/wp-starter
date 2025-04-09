<?php

class ITSEC_System_Tweaks_Setup {

	public function __construct() {
		add_action( 'itsec_modules_do_plugin_uninstall', array( $this, 'execute_uninstall' ) );
		add_action( 'itsec_modules_do_plugin_upgrade', array( $this, 'execute_upgrade' ) );
	}

	/**
	 * Execute module uninstall
	 *
	 * @return void
	 */
	public function execute_uninstall() {
		delete_site_option( 'itsec_tweaks' );
	}

	/**
	 * Execute module upgrade
	 *
	 * @since 4.0
	 *
	 * @return void
	 */
	public function execute_upgrade( $itsec_old_version ) {

		if ( $itsec_old_version < 4000 ) {

			global $itsec_bwps_options;

			ITSEC_Lib::create_database_tables();

			$current_options = get_site_option( 'itsec_tweaks' );

			// Don't do anything if settings haven't already been set, defaults exist in the module system and we prefer to use those
			if ( false !== $current_options ) {

				$current_options['protect_files']            = isset( $itsec_bwps_options['st_ht_files'] ) && $itsec_bwps_options['st_ht_files'] == 1 ? true : false;
				$current_options['directory_browsing']       = isset( $itsec_bwps_options['st_ht_browsing'] ) && $itsec_bwps_options['st_ht_browsing'] == 1 ? true : false;
				$current_options['request_methods']          = isset( $itsec_bwps_options['st_ht_request'] ) && $itsec_bwps_options['st_ht_request'] == 1 ? true : false;
				$current_options['suspicious_query_strings'] = isset( $itsec_bwps_options['st_ht_query'] ) && $itsec_bwps_options['st_ht_query'] == 1 ? true : false;
				$current_options['non_english_characters']   = isset( $itsec_bwps_options['st_ht_foreign'] ) && $itsec_bwps_options['st_ht_foreign'] == 1 ? true : false;
				$current_options['long_url_strings']         = isset( $itsec_bwps_options['st_longurl'] ) && $itsec_bwps_options['st_longurl'] == 1 ? true : false;
				$current_options['write_permissions']        = isset( $itsec_bwps_options['st_fileperm'] ) && $itsec_bwps_options['st_fileperm'] == 1 ? true : false;

				update_site_option( 'itsec_tweaks', $current_options );
				ITSEC_Response::regenerate_wp_config();
			}

		}

		if ( $itsec_old_version < 4041 ) {
			$current_options = get_site_option( 'itsec_tweaks' );

			// If there are no current options, go with the new defaults by not saving anything
			if ( is_array( $current_options ) ) {
				$new_module_settings = ITSEC_Modules::get_settings( 'system-tweaks' );

				// Reduce to only settings in new module
				$current_options = array_intersect_key( $current_options, $new_module_settings );

				// Use new module settings as defaults for any missing settings
				$current_options = array_merge( $new_module_settings, $current_options );

				// If anything in this module is being used activate it, otherwise deactivate it
				$activate = false;
				foreach ( $current_options as $on ) {
					if ( $on ) {
						$activate = true;
						break;
					}
				}
				if ( $activate ) {
					ITSEC_Modules::activate( 'system-tweaks' );
				} else {
					ITSEC_Modules::deactivate( 'system-tweaks' );
				}

				ITSEC_Modules::set_settings( 'system-tweaks', $current_options );
			}
		}

		if ( $itsec_old_version < 4095 ) {
			ITSEC_Response::regenerate_server_config();
		}

		if ( $itsec_old_version < 4123 ) {
			if ( ITSEC_Modules::get_setting( 'system-tweaks', 'write_permissions' ) ) {
				@chmod( ITSEC_Lib::get_htaccess(), 0644 );
				@chmod( ITSEC_Lib::get_config(), 0644 );
			}

			// System Tweaks is now active by default, if the user has it deactivated,
			// we need to turn off all the options so their site functions the same on update.
			$active = get_site_option( 'itsec_active_modules', [] );

			if ( empty( $active['system-tweaks'] ) ) {
				$settings = ITSEC_Modules::get_settings( 'system-tweaks' );
				$disable  = [
					'protect_files'      => false,
					'directory_browsing' => false,
					'uploads_php'        => false,
					'themes_php'         => false,
					'plugins_php'        => false,
				];

				ITSEC_Modules::set_settings( 'system-tweaks', array_merge( $settings, $disable ) );
			}
		}
	}
}

new ITSEC_System_Tweaks_Setup();
