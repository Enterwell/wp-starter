<?php

namespace iThemesSecurity\User_Groups;

use iThemesSecurity\Exception\Invalid_Argument_Exception;

final class Settings_Registration {

	const T_SINGLE = 'single';
	const T_MULTIPLE = 'multiple';

	/** @var string */
	private $module;

	/** @var string */
	private $setting;

	/** @var string */
	private $type;

	/** @var callable */
	private $labels;

	/** @var string */
	private $generated;

	/**
	 * Settings_Registration constructor.
	 *
	 * @param string   $module
	 * @param string   $setting
	 * @param string   $type
	 * @param callable $labels
	 */
	public function __construct( $module, $setting, $type, callable $labels ) {
		if ( ! in_array( $type, [ self::T_SINGLE, self::T_MULTIPLE ], true ) ) {
			throw new Invalid_Argument_Exception( __( 'Type must be either single or multiple.', 'better-wp-security' ) );
		}

		$this->module  = $module;
		$this->setting = $setting;
		$this->type    = $type;
		$this->labels  = $labels;
	}

	/**
	 * Get the slug of the module.
	 *
	 * @return string
	 */
	public function get_module() {
		return $this->module;
	}

	/**
	 * Get the slug of the setting.
	 *
	 * @return string
	 */
	public function get_setting() {
		return $this->setting;
	}

	/**
	 * Get the setting type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get the labels for this setting.
	 *
	 * Has 'title' and 'description' keys.
	 *
	 * @return string[]
	 */
	public function get_labels() {
		if ( ! $this->generated ) {
			$this->generated = call_user_func( $this->labels );
		}

		return $this->generated;
	}
}
