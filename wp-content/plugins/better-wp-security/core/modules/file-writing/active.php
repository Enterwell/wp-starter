<?php

use iThemesSecurity\Lib\Tools\Config_Tool;
use iThemesSecurity\Lib\Tools\Tools_Registry;
use \iThemesSecurity\Lib\Result;

add_action( 'itsec_register_tools', function ( Tools_Registry $registry ) {
	$registry->register( new class( 'server-config-rules', ITSEC_Modules::get_config( 'file-writing' ) ) extends Config_Tool {
		public function run( array $form = [] ): Result {
			ITSEC_Files::regenerate_server_config();

			return Result::from_response()
			             ->add_success_message( __( 'Regenerated Server Config rules.', 'better-wp-security' ) );
		}
	} );

	$registry->register( new class( 'wp-config-rules', ITSEC_Modules::get_config( 'file-writing' ) ) extends Config_Tool {
		public function run( array $form = [] ): Result {
			ITSEC_Files::regenerate_wp_config();

			return Result::from_response()
			             ->add_success_message( __( 'Regenerated WP-Config rules.', 'better-wp-security' ) );
		}
	} );
} );
