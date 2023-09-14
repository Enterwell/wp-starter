<?php

use iThemesSecurity\Config_Validator;

class ITSEC_Network_Brute_Force_Validator extends Config_Validator {
	protected function validate_settings() {
		parent::validate_settings();

		if ( ! $this->can_save() ) {
			return;
		}

		if ( ! empty( $this->settings['email'] ) ) {
			require_once( dirname( __FILE__ ) . '/utilities.php' );

			$key = ITSEC_Network_Brute_Force_Utilities::get_api_key( $this->settings['email'], $this->settings['updates_optin'] );

			if ( is_wp_error( $key ) ) {
				$this->set_can_save( false );
				$this->add_error( $key );
			} else {
				$secret = ITSEC_Network_Brute_Force_Utilities::activate_api_key( $key );

				if ( is_wp_error( $secret ) ) {
					$this->set_can_save( false );
					$this->add_error( $secret );
				} else {
					$this->settings['api_key']    = $key;
					$this->settings['api_secret'] = $secret;

					$this->settings['api_nag'] = false;

					ITSEC_Response::reload_module( $this->get_id() );
				}
			}

			if ( $this->can_save() ) {
				$this->settings['email'] = null;
			}
		}
	}
}

ITSEC_Modules::register_validator( new ITSEC_Network_Brute_Force_Validator( ITSEC_Modules::get_config( 'network-brute-force' ) ) );
