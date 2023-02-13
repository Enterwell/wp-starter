<?php

namespace iThemesSecurity\Lib\Lockout;

final class Username_Context extends Context {

	/** @var string */
	private $username;

	/**
	 * ITSEC_Username_Lockout_Context constructor.
	 *
	 * @param string $lockout_module
	 * @param string $username
	 */
	public function __construct( $lockout_module, $username ) {
		parent::__construct( $lockout_module );
		$this->username = $username;
	}

	/**
	 * Get the username that was locked out.
	 *
	 * @return string
	 */
	public function get_username() {
		return $this->username;
	}

	public function make_execute_lock_context() {
		return new Execute_Lock\Username_Context( $this, $this->get_username() );
	}
}
