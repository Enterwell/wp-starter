<?php

namespace iThemesSecurity\Actor;

final class Lockout_Module implements Actor {

	/** @var string */
	private $module;

	/**
	 * Lockout_Module constructor.
	 *
	 * @param string $module
	 */
	public function __construct( $module ) { $this->module = $module; }

	public function get_type() {
		return 'lockout_module';
	}

	public function get_identifier() {
		return $this->module;
	}

	public function __toString() {
		/** @var \ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout;

		$modules = $itsec_lockout->get_lockout_modules();

		if ( ! isset( $modules[ $this->module ] ) ) {
			return sprintf( esc_html__( 'Inactive lockout module %s', 'better-wp-security' ), $this->module );
		}

		return isset( $modules[ $this->module ]['label'] ) ? (string) $modules[ $this->module ]['label'] : (string) $modules[ $this->module ]['reason'];
	}
}
