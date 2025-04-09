<?php

use iThemesSecurity\Import_Export\Export\Export;
use iThemesSecurity\Import_Export\Import\Import_Context;
use iThemesSecurity\Module_Config;
use iThemesSecurity\User_Groups;

abstract class ITSEC_Settings {

	/** @var Module_Config|null */
	protected $config;

	/** @var array */
	protected $settings;

	/**
	 * ITSEC_Settings constructor.
	 *
	 * @param Module_Config|null $config The configuration object. If omitted, will attempt to retrieve it.
	 */
	public function __construct( Module_Config $config = null ) {
		$this->config = $config ?: ITSEC_Modules::get_config( $this->get_id() );
		$this->load();
	}

	/**
	 * Gets the module ID this settings class backs.
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Gets the list of default setting values.
	 *
	 * @return array
	 */
	abstract public function get_defaults();

	/**
	 * Gets the default value for a particular setting.
	 *
	 * @param string $setting The setting slug.
	 * @param mixed  $default The default value to use if the module did not declare one.
	 *
	 * @return mixed
	 */
	public function get_default( $setting, $default = null ) {
		$defaults = $this->get_defaults();

		if ( array_key_exists( $setting, $defaults ) ) {
			return $defaults[ $setting ];
		}

		return $default;
	}

	/**
	 * Gets the settings schema.
	 *
	 * @return array
	 */
	public function get_settings_schema() {
		return [];
	}

	/**
	 * Gets the list of known settings slugs.
	 *
	 * @return string[]
	 */
	public function get_known_settings() {
		return array_keys( $this->get_defaults() );
	}

	/**
	 * Checks if this is a known setting name.
	 *
	 * @param string $setting The setting slug.
	 *
	 * @return bool
	 */
	public function is_known_setting( $setting ) {
		return isset( $this->get_defaults()[ $setting ] );
	}

	/**
	 * Should the settings UI be shown.
	 *
	 * @return bool
	 */
	public function show_ui(): bool {
		return $this->has_interactive_settings();
	}

	/**
	 * Checks if this module has any interactive settings.
	 *
	 * @return bool
	 */
	final public function has_interactive_settings() {
		foreach ( $this->get_known_settings() as $setting ) {
			if ( $this->is_interactive_setting( $setting ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if this setting can be set by the user, or is it wholly managed by the module.
	 *
	 * @param string $setting The setting name.
	 *
	 * @return bool
	 */
	public function is_interactive_setting( $setting ) {
		return true;
	}

	/**
	 * Gets the list of conditional settings slugs.
	 *
	 * @return array
	 */
	public function get_conditional_settings() {
		return array_filter( $this->get_known_settings(), [ $this, 'is_conditional_setting' ] );
	}

	/**
	 * Checks if the setting is conditionally rendered, or is it always available.
	 *
	 * @param string $setting The setting name.
	 *
	 * @return bool
	 */
	public function is_conditional_setting( $setting ) {
		return false;
	}

	/**
	 * Gets the configuration for a conditional setting to be active.
	 *
	 * @param string $setting The setting name.
	 *
	 * @return array
	 */
	public function get_conditional_setting_config( $setting ) {
		return [];
	}

	/**
	 * Checks if a conditional setting is currently visible.
	 *
	 * @param string     $setting  The setting name.
	 * @param array|null $settings Optionally, compares against the given settings
	 *                             instead of the saved settings.
	 *
	 * @return bool
	 */
	public function is_conditional_setting_active( string $setting, array $settings = null ): bool {
		$settings = $settings ?? $this->settings;

		if ( ! $this->is_conditional_setting( $setting ) ) {
			return false;
		}

		$config = $this->get_conditional_setting_config( $setting );

		if ( isset( $config['settings'] ) ) {
			if ( is_wp_error( rest_validate_value_from_schema( $settings, $config['settings'] ) ) ) {
				return false;
			}

			if ( is_wp_error( rest_sanitize_value_from_schema( $settings, $config['settings'] ) ) ) {
				return false;
			}
		}

		if ( isset( $config['server-type'] ) && ! in_array( ITSEC_Lib::get_server(), $config['server-type'], true ) ) {
			return false;
		}

		if ( isset( $config['install-type'] ) && $config['install-type'] !== ITSEC_Core::get_install_type() ) {
			return false;
		}

		if ( isset( $config['active-modules'] ) ) {
			$active = array_filter( $config['active-modules'], 'ITSEC_Modules::is_active' );

			if ( count( $active ) !== count( $config['active-modules'] ) ) {
				return false;
			}
		}

		if ( isset( $config['user-groups'] ) ) {
			foreach ( $config['user-groups'] as $group_setting ) {
				if ( ! ITSEC_Lib::array_get( $settings, $group_setting ) ) {
					return false;
				}
			}
		}

		if ( isset( $config['feature-flags'] ) ) {
			foreach ( $config['feature-flags'] as $flag ) {
				if ( ! ITSEC_Lib_Feature_Flags::is_enabled( $flag ) ) {
					return false;
				}
			}
		}

		return true;
	}

	protected function after_save() { }

	protected function handle_settings_changes( $old_settings ) {}

	public function export() {
		_deprecated_function( __METHOD__, '7.1' );

		return $this->settings;
	}

	public function import( $settings ) {
		_deprecated_function( __METHOD__, '7.1' );
		$this->set_all( $settings );
	}

	public function get( $name, $default = null ) {
		if ( isset( $this->settings[ $name ] ) ) {
			return $this->settings[ $name ];
		}

		return $default;
	}

	public function get_all() {
		return $this->settings;
	}

	/**
	 * Prepares the settings for output in the REST API.
	 *
	 * @return array
	 */
	public function prepare_for_rest() {
		return $this->settings;
	}

	/**
	 * Prepares the settings from a REST API request to be saved.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function prepare_from_rest( $settings ) {
		return $settings;
	}

	public function set( $name, $value ) {
		$settings          = $this->settings;
		$settings[ $name ] = $value;

		return $this->set_all( $settings );
	}

	public function set_all( $settings ) {
		$retval = array(
			'old_settings' => $this->settings,
			'new_settings' => $this->settings,
			'errors'       => array(),
			'messages'     => array(),
			'saved'        => false,
		);

		$validator = ITSEC_Modules::get_validator( $this->get_id() );

		if ( is_null( $validator ) ) {
			$retval['errors'][] = new WP_Error( 'itsec-settings-missing-validator-for-' . $this->get_id(), sprintf( __( 'The data validator for %1$s is missing. Data for the module cannot be saved without the validator. This error could indicate a bad install of Solid Security. Please remove the plugin and reinstall it. If this message persists, please contact support and send them this error message.', 'better-wp-security' ), $this->get_id() ) );
		} else {
			$validator->validate( $settings );

			$retval['errors']   = $validator->get_errors();
			$retval['messages'] = $validator->get_messages();

			if ( $validator->can_save() ) {
				$this->settings = $validator->get_settings();

				ITSEC_Storage::set( $this->get_id(), $this->settings );
				$this->after_save();
				$this->handle_settings_changes( $retval['old_settings'] );

				$retval['new_settings'] = $this->settings;
				$retval['saved']        = true;

				do_action( 'itsec-settings-updated', $this->get_id() );
			} else {
				ITSEC_Response::set_success( false );
			}
		}

		ITSEC_Response::add_errors( $retval['errors'] );
		ITSEC_Response::add_messages( $retval['messages'] );

		return $retval;
	}

	public function load() {
		$this->settings = ITSEC_Storage::get( $this->get_id() );

		if ( ! is_array( $this->settings ) ) {
			$this->settings = array();
		}

		foreach ( $this->get_known_settings() as $setting ) {
			if ( ! array_key_exists( $setting, $this->settings ) ) {
				$this->settings[ $setting ] = $this->get_default( $setting );
			}
		}
	}
}
