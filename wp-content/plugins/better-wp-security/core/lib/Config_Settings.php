<?php

namespace iThemesSecurity;

class Config_Settings extends \ITSEC_Settings {

	public function __construct( Module_Config $config ) {
		parent::__construct( $config );
	}

	public function get_id() {
		return $this->config->get_id();
	}

	public function get_defaults() {
		$defaults = [];

		foreach ( $this->get_known_settings() as $setting ) {
			$defaults[ $setting ] = $this->get_default( $setting );
		}

		return $defaults;
	}

	public function get_settings_schema() {
		return $this->config->translate( Module_Config::T_SETTINGS )->get_settings();
	}

	public function get_known_settings() {
		return array_keys( $this->config->get_settings()['properties'] );
	}

	public function is_known_setting( $setting ) {
		return isset( $this->config->get_settings()['properties'][ $setting ] ) || in_array( $setting, $this->config->get_deprecated_settings(), true );
	}

	public function is_interactive_setting( $setting ) {
		if ( ! empty( $this->config->get_settings()['properties'][ $setting ]['readonly'] ) ) {
			return false;
		}

		if ( in_array( $setting, $this->config->get_deprecated_settings(), true ) ) {
			return false;
		}

		return parent::is_interactive_setting( $setting );
	}

	public function is_conditional_setting( $setting ) {
		$config = $this->config->get_conditional_settings();

		return isset( $config[ $setting ] );
	}

	public function get_conditional_setting_config( $setting ) {
		if ( ! $this->is_conditional_setting( $setting ) ) {
			return [];
		}

		return $this->config->get_conditional_settings()[ $setting ];
	}

	public function get_default( $setting, $default = null ) {
		if ( ! isset( $this->config->get_settings()['properties'][ $setting ] ) ) {
			return $default;
		}

		$definition = $this->config->get_settings()['properties'][ $setting ];
		$default    = $definition['default'];

		if ( 'object' === $definition['type'] ) {
			foreach ( $definition['properties'] ?? [] as $property => $prop_schema ) {
				$default[ $property ] = $prop_schema['default'] ?? $default[ $property ] ?? null;
			}
		}

		return $default;
	}

	protected function after_save() {
		parent::after_save();

		\ITSEC_Core::get_scheduler()->register_events_for_config( $this->config );
	}
}
