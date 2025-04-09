<?php

use iThemesSecurity\User_Groups\Upgrader;

class ITSEC_HIBP_Setup {

	public function __construct() {
		add_action( 'itsec_modules_do_plugin_upgrade', array( $this, 'execute_upgrade' ) );
	}

	/**
	 * Execute module upgrade
	 *
	 * @param int $itsec_old_version
	 *
	 * @return void
	 */
	public function execute_upgrade( $itsec_old_version ) {
		if ( $itsec_old_version < 4117 ) {
			$settings = ITSEC_Modules::get_setting( 'password-requirements', 'requirement_settings' );

			if ( isset( $settings['hibp']['role'] ) ) {
				$settings['hibp']['group'] = ITSEC_Modules::get_container()
				                                          ->get( Upgrader::class )
				                                          ->upgrade_from_min_role( $settings['hibp']['role'] );
				unset( $settings['hibp']['role'] );
			} else {
				$settings['hibp']['group'] = [];
			}

			ITSEC_Modules::set_setting( 'password-requirements', 'requirement_settings', $settings );
		}
	}
}

new ITSEC_HIBP_Setup();
