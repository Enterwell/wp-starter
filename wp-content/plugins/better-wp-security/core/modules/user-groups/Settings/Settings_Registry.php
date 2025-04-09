<?php

namespace iThemesSecurity\User_Groups;

final class Settings_Registry {

	/** @var Settings_Registration[] */
	private $settings = [];

	/**
	 * Register the setting.
	 *
	 * @param Settings_Registration $registration
	 */
	public function register( Settings_Registration $registration ) {
		$this->settings[] = $registration;
	}

	/**
	 * Unregister a setting.
	 *
	 * @param string $module
	 * @param string $setting
	 *
	 * @return bool True if the setting was unregistered.
	 */
	public function unregister( $module, $setting ) {
		foreach ( $this->settings as $i => $registration ) {
			if ( $registration->get_module() === $module && $registration->get_setting() === $setting ) {
				unset( $this->settings[ $i ] );
				$this->settings = array_values( $this->settings );

				return true;
			}
		}

		return false;
	}

	/**
	 * Get a list of all the settings.
	 *
	 * @return Settings_Registration[]
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Find a setting registration by module and setting.
	 *
	 * @param string $module
	 * @param string $setting
	 *
	 * @return Settings_Registration|null
	 */
	public function find( $module, $setting ) {
		foreach ( $this->get_settings() as $registration ) {
			if ( $registration->get_module() === $module && $registration->get_setting() === $setting ) {
				return $registration;
			}
		}

		return null;
	}

	/**
	 * Clear all registered settings.
	 */
	public function clear_settings() {
		$this->settings = [];
	}
}
