<?php

namespace iThemesSecurity\Lib\Lockout\Execute_Lock;

use iThemesSecurity\Lib\Lockout\Execute_Lock\Source\Source;

final class Username_Context extends Context {

	/** @var string */
	private $username;

	/**
	 * ITSEC_Execute_Username_Lockout_Context constructor.
	 *
	 * @param Source $source
	 * @param string $username
	 */
	public function __construct( Source $source, $username ) {
		parent::__construct( $source );
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

	public function to_legacy() {
		$legacy = parent::to_legacy();

		$legacy['username']  = $this->get_username();
		$legacy['user_lock'] = true;

		return $legacy;
	}
}
