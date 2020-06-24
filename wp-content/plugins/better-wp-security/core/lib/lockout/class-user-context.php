<?php

namespace iThemesSecurity\Lib\Lockout;

final class User_Context extends Context {

	/** @var int */
	private $user_id;

	/**
	 * ITSEC_User_Lockout_Context constructor.
	 *
	 * @param string $lockout_module
	 * @param int    $user_id
	 */
	public function __construct( $lockout_module, $user_id ) {
		parent::__construct( $lockout_module );
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

	public function make_execute_lock_context() {
		return new Execute_Lock\User_Context( $this, $this->get_user_id() );
	}
}
