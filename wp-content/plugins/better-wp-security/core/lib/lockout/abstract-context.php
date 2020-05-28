<?php

namespace iThemesSecurity\Lib\Lockout;

use iThemesSecurity\Lib\Lockout\Execute_Lock\Source\Source;

abstract class Context implements Source {

	/** @var string */
	private $lockout_module;

	/**
	 * ITSEC_Execute_Lockout_Context constructor.
	 *
	 * @param string $lockout_module
	 */
	public function __construct( $lockout_module ) {
		$this->lockout_module = $lockout_module;
	}

	/**
	 * Get the registered lockout module {@see 'itsec_lockout_modules'} that
	 *
	 * @return string
	 */
	public function get_lockout_module() {
		return $this->lockout_module;
	}

	/**
	 * @return mixed
	 */
	public function get_source_slug() {
		return $this->get_lockout_module();
	}

	/**
	 * Make an execute lock context from the lockout context.
	 *
	 * @return Execute_Lock\Context
	 */
	abstract public function make_execute_lock_context();
}

require_once( __DIR__ . '/class-host-context.php' );
require_once( __DIR__ . '/class-user-context.php' );
require_once( __DIR__ . '/class-username-context.php' );
