<?php

use iThemesSecurity\Config_Validator;
use \iThemesSecurity\User_Groups;

class ITSEC_Global_Validator extends Config_Validator {

	protected function validate_settings() {
		if ( ITSEC_Core::is_interactive() && $this->settings['manage_group'] && $this->settings['manage_group'] !== $this->previous_settings['manage_group'] ) {
			$matcher = ITSEC_Modules::get_container()->get( User_Groups\Matcher::class );

			if ( ! $matcher->matches( User_Groups\Match_Target::for_user( wp_get_current_user() ), $this->settings['manage_group'] ) ) {
				$this->add_error( new WP_Error( 'itsec-validator-global-cannot-exclude-self', __( 'The configuration you have chosen removes your capability to manage Solid Security.', 'better-wp-security' ), [ 'status' => 400 ] ) );
				$this->set_can_save( false );
			}
		}

		parent::validate_settings();
	}
}

ITSEC_Modules::register_validator( new ITSEC_Global_Validator( ITSEC_Modules::get_config( 'global' ) ) );
