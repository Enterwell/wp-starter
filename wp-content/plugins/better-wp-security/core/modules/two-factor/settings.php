<?php

use iThemesSecurity\Config_Settings;

final class ITSEC_Two_Factor_Settings extends Config_Settings {
	public function get_default( $setting, $default = null ) {
		if ( 'on_board_welcome' === $setting ) {
			return $this->get_default_on_board_welcome();
		}

		return parent::get_default( $setting, $default );
	}

	public function get_settings_schema() {
		$schema = parent::get_settings_schema();

		foreach ( ITSEC_Two_Factor_Helper::get_instance()->get_all_provider_instances() as $provider ) {
			$schema['properties']['custom_available_methods']['items']['enum'][]      = get_class( $provider );
			$schema['properties']['custom_available_methods']['items']['enumNames'][] = $provider->get_label();
		}

		return $schema;
	}

	private function get_default_on_board_welcome() {
		$welcome = esc_html__( 'When you login using Two-factor authenticator you’ll be prompted to enter a secondary Authentication Code from your Phone or Email.', 'better-wp-security' );
		$welcome .= "\n\n";
		$welcome .= esc_html__( 'Two-Factor authentication adds an important extra layer of protection to your login by combining something you know, your password, with something you have, your Phone or Email, preventing attackers from gaining access to your account even if you lose control of your password.', 'better-wp-security' );

		return $welcome;
	}
}

ITSEC_Modules::register_settings( new ITSEC_Two_Factor_Settings( ITSEC_Modules::get_config( 'two-factor' ) ) );
