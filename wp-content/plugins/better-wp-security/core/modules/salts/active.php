<?php

use iThemesSecurity\Lib\Tools\Config_Tool;
use iThemesSecurity\Lib\Tools\Tools_Registry;
use \iThemesSecurity\Lib\Result;

add_action( 'itsec_register_tools', function ( Tools_Registry $registry ) {
	$registry->register( new class( 'change-salts', ITSEC_Modules::get_config( 'wordpress-salts' ) ) extends Config_Tool {
		public function run( array $form = [] ): Result {
			$generated = ITSEC_WordPress_Salts_Utilities::generate_new_salts();

			if ( is_wp_error( $generated ) ) {
				return Result::error( $generated );
			}

			ITSEC_Modules::set_setting( 'wordpress-salts', 'last_generated', time() );

			return Result::success()
			             ->add_success_message( __( 'The WordPress salts were successfully regenerated.', 'better-wp-security' ) );
		}
	} );
} );
