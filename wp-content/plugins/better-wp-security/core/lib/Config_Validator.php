<?php

namespace iThemesSecurity;

class Config_Validator extends \ITSEC_Validator {

	/**
	 * Config_Validator constructor.
	 *
	 * @param Module_Config $config
	 */
	public function __construct( Module_Config $config ) {
		parent::__construct( $config );

		$this->run_validate_matching_types           = false;
		$this->vars_to_skip_validate_matching_fields = array_merge( $this->vars_to_skip_validate_matching_fields, $config->get_removed_settings() );
	}

	public function get_id() {
		return $this->config->get_id();
	}

	protected function sanitize_settings() {
		foreach ( $this->config->get_removed_settings() as $setting ) {
			unset( $this->settings[ $setting ] );
		}

		$this->preserve_setting_if_exists( $this->config->get_deprecated_settings() );

		if ( \ITSEC_Core::is_interactive() ) {
			foreach ( $this->settings_obj->get_known_settings() as $setting ) {
				if ( ! $this->settings_obj->is_interactive_setting( $setting ) ) {
					$this->set_previous_if_missing( $setting );
				}
			}
		}

		foreach ( $this->settings_obj->get_conditional_settings() as $setting ) {
			if ( $this->settings_obj->is_known_setting( $setting ) && ! $this->settings_obj->is_conditional_setting_active( $setting, $this->settings ) ) {
				$this->set_previous_if_missing( $setting );
			}
		}
	}

	protected function validate_settings() {
		if ( ! $this->can_save() ) {
			return;
		}

		foreach ( $this->settings_obj->get_settings_schema()['properties'] as $setting => $schema ) {
			$param = $schema['title'] ?? $setting;
			$valid = rest_validate_value_from_schema( $this->settings[ $setting ], $schema, $param );

			if ( is_wp_error( $valid ) ) {
				$this->add_error( $valid );
				$this->set_can_save( false );
				continue;
			}

			$sanitized = rest_sanitize_value_from_schema( $this->settings[ $setting ], $schema, $param );

			if ( is_wp_error( $sanitized ) ) {
				$this->add_error( $sanitized );
				$this->set_can_save( false );
			}

			$this->settings[ $setting ] = $sanitized;
		}

		foreach ( $this->config->get_user_groups() as $user_group => $config ) {
			$valid = $this->validate_user_groups( $config['title'], $user_group );

			if ( is_wp_error( $valid ) ) {
				$this->add_error( $valid );
				$this->set_can_save( false );
			} else {
				\ITSEC_Lib::array_set( $this->settings, $user_group, $valid );
			}
		}
	}
}
