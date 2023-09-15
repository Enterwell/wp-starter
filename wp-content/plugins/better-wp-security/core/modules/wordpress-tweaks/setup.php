<?php

class ITSEC_WordPress_Tweaks_Setup {

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
		$settings = ITSEC_Modules::get_settings( 'wordpress-tweaks' );


		if ( $itsec_old_version < 4000 ) {
			global $itsec_bwps_options;

			ITSEC_Lib::create_database_tables();

			if ( isset( $itsec_bwps_options['st_manifest'] ) && $itsec_bwps_options['st_manifest'] ) {
				$settings['wlwmanifest_header'] = true;
			}
			if ( isset( $itsec_bwps_options['st_edituri'] ) && $itsec_bwps_options['st_edituri'] ) {
				$settings['edituri_header'] = true;
			}
			if ( isset( $itsec_bwps_options['st_comment'] ) && $itsec_bwps_options['st_comment'] ) {
				$settings['comment_spam'] = true;
			}
			if ( isset( $itsec_bwps_options['st_loginerror'] ) && $itsec_bwps_options['st_loginerror'] ) {
				$settings['login_errors'] = true;
			}

			ITSEC_Response::regenerate_server_config();
			ITSEC_Response::regenerate_wp_config();
		}

		if ( $itsec_old_version < 4035 ) {
			ITSEC_Response::regenerate_server_config();
		}

		if ( $itsec_old_version < 4041 ) {
			$old_settings = get_site_option( 'itsec_tweaks' );

			if ( is_array( $old_settings ) ) {
				$settings = array_merge( $settings, $old_settings );
			}
		} else {
			// Time to get rid of the cruft.
			delete_site_option( 'itsec_tweaks' );
		}

		if ( $itsec_old_version < 4050 ) {
			if ( 'enable' === $settings['rest_api'] ) {
				$settings['rest_api'] = 'default-access';
			} elseif ( in_array( $settings['rest_api'], array( 'disable', 'require-admin' ) ) ) {
				$settings['rest_api'] = 'restrict-access';
			}
		}

		if ( $itsec_old_version < 4073 ) {
			unset( $settings['safe_jquery'] );
			unset( $settings['jquery_version'] );
		}

		if ( $itsec_old_version < 4123 ) {
			if ( 2 == $settings['disable_xmlrpc'] ) {
				$settings['disable_xmlrpc'] = 'disable';
			} elseif ( 1 == $settings['disable_xmlrpc'] ) {
				$settings['disable_xmlrpc'] = 'disable_pingbacks';
			} else {
				$settings['disable_xmlrpc'] = 'enable';
			}

			// WordPress Tweaks is now active by default, if the user has it deactivated,
			// we need to turn off all the options so their site functions the same on update.
			$active = get_site_option( 'itsec_active_modules', [] );

			if ( empty( $active['wordpress-tweaks'] ) ) {
				$settings['file_editor']                 = false;
				$settings['disable_xmlrpc']              = 'enable';
				$settings['allow_xmlrpc_multiauth']      = true;
				$settings['rest_api']                    = 'default-access';
				$settings['valid_user_login_type']       = 'both';
				$settings['force_unique_nicename']       = false;
				$settings['disable_unused_author_pages'] = false;
			}
		}

		ITSEC_Modules::set_settings( 'wordpress-tweaks', $settings );
	}
}

new ITSEC_WordPress_Tweaks_Setup();
