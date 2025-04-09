<?php

use iThemesSecurity\Config_Validator;
use iThemesSecurity\Lib\Legacy_Password_Requirement;

class ITSEC_Password_Requirements_Validator extends Config_Validator {

	protected function validate_settings() {
		parent::validate_settings();

		if ( ! $this->can_save() ) {
			return;
		}

		foreach ( ITSEC_Lib_Password_Requirements::get_registered() as $code => $requirement ) {
			if ( $requirement->has_user_group() ) {
				$this->validate_user_groups( $requirement->get_title(), "requirement_settings.{$code}.group" );
			}

			if ( $requirement instanceof Legacy_Password_Requirement && $schema = $requirement->get_settings_schema() ) {
				$this->settings['requirement_settings'][ $code ] =
					$this->previous_settings['requirement_settings'][ $code ] ?? wp_list_pluck( $schema['properties'], 'default' );
			}
		}
	}
}

ITSEC_Modules::register_validator( new ITSEC_Password_Requirements_Validator( ITSEC_Modules::get_config( 'password-requirements' ) ) );
