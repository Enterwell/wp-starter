<?php

use iThemesSecurity\Config_Settings;

class ITSEC_Password_Requirements_Settings extends Config_Settings {

	public function load() {
		$this->settings = ITSEC_Storage::get( $this->get_id() );
		$defaults       = $this->get_defaults();

		if ( ! is_array( $this->settings ) ) {
			$this->settings = [];
		}

		$this->settings = array_merge( $defaults, $this->settings );

		foreach ( ITSEC_Lib_Password_Requirements::get_registered() as $code => $requirement ) {
			if ( ! isset( $this->settings['enabled_requirements'][ $code ] ) ) {
				$this->settings['enabled_requirements'][ $code ] = false;
			}

			if ( ! $schema = $requirement->get_settings_schema() ) {
				continue;
			}

			$this->settings['requirement_settings'][ $code ] = wp_parse_args(
				$this->settings['requirement_settings'][ $code ] ?? [],
				wp_list_pluck( $schema['properties'], 'default' )
			);
		}
	}

	public function show_ui(): bool {
		foreach ( ITSEC_Lib_Password_Requirements::get_registered() as $requirement ) {
			if ( $requirement->get_settings_schema() ) {
				return true;
			}
		}

		return false;
	}

	public function get_settings_schema() {
		$schema = parent::get_settings_schema();

		foreach ( ITSEC_Lib_Password_Requirements::get_registered() as $code => $requirement ) {
			if ( ! $requirement_schema = $requirement->get_settings_schema() ) {
				continue;
			}

			if ( $requirement->has_user_group() && count( $requirement_schema['properties'] ) === 1 ) {
				continue;
			}

			if ( isset( $requirement_schema['uiSchema'] ) ) {
				$schema['uiSchema']['requirement_settings'][ $code ] = $requirement_schema['uiSchema'];
				unset( $requirement_schema['uiSchema'] );
			}

			$schema['properties']['requirement_settings']['properties'][ $code ] = $requirement_schema;
		}

		return $schema;
	}

	public function get_conditional_settings() {
		$conditional = parent::get_conditional_settings();

		foreach ( ITSEC_Lib_Password_Requirements::get_registered() as $code => $requirement ) {
			if ( ! $requirement_schema = $requirement->get_settings_schema() ) {
				continue;
			}

			unset( $requirement_schema['properties']['group'] );

			if ( count( $requirement_schema['properties'] ) ) {
				$conditional[] = "requirement_settings.{$code}";
			}
		}

		return $conditional;
	}

	public function get_conditional_setting_config( $setting ) {
		if ( 0 !== strpos( $setting, 'requirement_settings.' ) ) {
			return parent::get_conditional_setting_config( $setting );
		}

		list( , $code ) = explode( '.', $setting, 2 );

		$requirement = ITSEC_Lib_Password_Requirements::get_registered()[ $code ] ?? null;

		if ( ! $requirement ) {
			return [];
		}

		$config = [];

		if ( $requirement->has_user_group() ) {
			$config['user-groups'] = [
				"requirement_settings.{$code}.group",
			];
		} else {
			$config['settings'] = [
				'type'       => 'object',
				'required'   => [ 'enabled_requirements' ],
				'properties' => [
					'enabled_requirements' => [
						'type'       => 'object',
						'required'   => [ $code ],
						'properties' => [
							$code => [
								'type' => 'boolean',
								'enum' => [ true ]
							],
						],
					],
				],
			];
		}

		return $config;
	}
}

ITSEC_Modules::register_settings( new ITSEC_Password_Requirements_Settings( ITSEC_Modules::get_config( 'password-requirements' ) ) );
