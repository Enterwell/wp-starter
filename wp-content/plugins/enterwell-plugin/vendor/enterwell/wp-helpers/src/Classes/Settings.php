<?php

namespace Ew\WpHelpers\Classes;
/**
 * Class Settings
 *
 * Singleton used for plugin settings CRUD operation.
 * Plugin settings are put in wp_options table.
 *
 * This is singleton class that exposes api for
 * CREATE and UPDATE basic plugin settings.
 *
 * @package Ew\WpHelpers
 * @author Matej Bošnjak<matej.bosnjak@enterwell.net>
 *
 */
class Settings {

	/**
	 * Settings meta key.
	 */
	const META_KEY = '_ew_plugin_settings_meta';

	/**
	 * Singleton instance.
	 *
	 * @since   1.0.0
	 *
	 * @var Settings
	 */
	protected static $instance;

	/**
	 * Meta key for settings storage.
	 *
	 * @var string
	 */
	private $meta_key;

	/**
	 * Array of settings.
	 * @since   1.0.0
	 * @var     array
	 */
	private $settings;

	/**
	 * Settings constructor.
	 */
	protected function __construct() {
		// Get settings from database on creation of class instance.
		$this->meta_key = $this->get_meta_key();
		$this->settings = get_option( $this->meta_key, [] );
	}

	/**
	 * Gets settings meta key.
	 *
	 * Uses ew/settings-meta filter.
	 *
	 * If value returned after applying filter is not a string
	 * then uses default value without exception.
	 *
	 * @since   1.0.0
	 *
	 * @return string
	 */
	private function get_meta_key() {
		// Apply meta key filter
		$key = apply_filters( 'ew/settings-meta', self::META_KEY );

		// If meta key is not string use default meta key
		if ( ! is_string( $key ) ) {
			$key = self::META_KEY;
		}

		return $key;
	}

	/**
	 * Gets singleton instance.
	 *
	 * @since   1.0.0
	 * @return Settings
	 */
	public static function get_instance() {
		if ( static::$instance == null ) {
			static::$instance = new Settings();
		}

		return static::$instance;
	}

	/**
	 * Gets settings option..
	 *
	 * @since   1.0.0
	 *
	 * @param   string $key
	 *
	 * @return  mixed|null
	 */
	public function get( $key ) {
		return array_key_exists( $key, $this->settings ) ? $this->settings[ $key ] : null;
	}

	/**
	 * Gets all plugin settings.
	 *
	 * @since   1.0.0
	 *
	 * @return array
	 */
	public function get_all() {
		return $this->settings;
	}

	/**
	 * Function used to add or update single
	 * settings field.
	 * Field is defined by key.
	 *
	 * @param   string $key
	 * @param   mixed $value
	 */
	public function update_single( $key, $value ) {
		// Update settings
		$this->settings[ $key ] = $value;

		// Update settings option in database
		update_option( $this->meta_key, $this->settings, false );
	}

	/**
	 * Updates all settings in array.
	 *
	 * @since   1.0.0
	 *
	 * @param   []  $settings_array
	 */
	public function update_all( $settings_array ) {
		// Update settings
		foreach ( $settings_array as $setting_key => $setting_value ) {
			$this->settings[ $setting_key ] = $setting_value;
		}

		// Update in db
		update_option( $this->meta_key, $this->settings, false );
	}

	#region Singleton methods.

	/**
	 * Override of clone method.
	 */
	private function __clone() {
		// No clone - this is singleton class.
	}

	/**
	 * Override of wakeup method
	 */
	private function __wakeup() {
		// No wakeup - this is singleton class.
	}

	#endregion

}