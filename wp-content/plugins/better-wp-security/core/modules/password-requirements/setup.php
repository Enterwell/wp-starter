<?php

class ITSEC_Password_Requirements_Setup {
	public function __construct() {
		add_action( 'itsec_modules_do_plugin_upgrade', [ $this, 'execute_upgrade' ] );
	}

	public function execute_upgrade( $build ) {
		$initial = ITSEC_Modules::get_setting( 'global', 'initial_build' );

		if ( $build < 4124 && $initial !== 4123 ) {
			$enabled      = ITSEC_Modules::get_setting( 'password-requirements', 'enabled_requirements' );
			$requirements = ITSEC_Modules::get_setting( 'password-requirements', 'requirement_settings' );

			foreach ( $requirements as $code => $settings ) {
				if ( empty( $enabled[ $code ] ) && isset( $settings['group'] ) ) {
					$requirements[ $code ]['group'] = [];
				}
			}

			ITSEC_Modules::set_setting( 'password-requirements', 'requirement_settings', $requirements );
		}
	}
}

new ITSEC_Password_Requirements_Setup();
