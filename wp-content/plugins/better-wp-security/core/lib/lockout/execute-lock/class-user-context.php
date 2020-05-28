<?php

namespace iThemesSecurity\Lib\Lockout\Execute_Lock;

use iThemesSecurity\Lib\Lockout\Execute_Lock\Source\Source;

final class User_Context extends Context {

	/** @var int */
	private $user_id;

	/**
	 * ITSEC_Execute_User_Lockout_Context constructor.
	 *
	 * @param Source $source
	 * @param int    $user_id
	 */
	public function __construct( Source $source, $user_id ) {
		parent::__construct( $source );
		$this->user_id = $user_id;
	}

	/**
	 * Get the user ID that was locked out.
	 *
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	public function to_legacy() {
		$legacy = parent::to_legacy();

		$legacy['user']      = $this->get_user_id();
		$legacy['user_lock'] = true;

		return $legacy;
	}
}
