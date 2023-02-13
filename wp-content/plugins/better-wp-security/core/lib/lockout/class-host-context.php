<?php

namespace iThemesSecurity\Lib\Lockout;

use ITSEC_Lib;

final class Host_Context extends Context {

	/** @var string */
	private $host;

	/** @var int */
	private $login_user_id;

	/** @var string */
	private $login_username;

	/** @var bool */
	private $user_limit_triggered = false;

	/**
	 * ITSEC_Host_Lockout_Context constructor.
	 *
	 * @param string $lockout_module
	 * @param string $host
	 */
	public function __construct( $lockout_module, $host = '' ) {
		parent::__construct( $lockout_module );
		$this->host = $host ?: ITSEC_Lib::get_ip();
	}

	/**
	 * Get the host being locked out.
	 *
	 * @return string
	 */
	public function get_host() {
		return $this->host;
	}

	/**
	 * If this lockout occurred while trying to login as a user,
	 * this will return the user ID that was trying to be logged-in-to.
	 *
	 * @return int|null
	 */
	public function get_login_user_id() {
		return $this->login_user_id;
	}

	/**
	 * Set who is being logged in as.
	 *
	 * @param int $user_id
	 *
	 * @return $this
	 */
	public function set_login_user_id( $user_id ) {
		$this->login_user_id = $user_id;

		return $this;
	}

	/**
	 * If this lockout occurred while trying to login to a non-existent user,
	 * this will return that username.
	 *
	 * @return string
	 */
	public function get_login_username() {
		return $this->login_username;
	}

	/**
	 * Set which username is being logged in as.
	 *
	 * @param string $login_username
	 *
	 * @return Host_Context
	 */
	public function set_login_username( $login_username ) {
		$this->login_username = $login_username;

		return $this;
	}

	/**
	 * Was the user lockout limit was triggered.
	 *
	 * @return bool
	 */
	public function is_user_limit_triggered() {
		return $this->user_limit_triggered;
	}

	/**
	 * Set that the user lockout limit was triggered.
	 *
	 * @param bool $user_limit_triggered
	 *
	 * @return Host_Context
	 */
	public function set_user_limit_triggered( $user_limit_triggered = true ) {
		$this->user_limit_triggered = $user_limit_triggered;

		return $this;
	}

	public function make_execute_lock_context() {
		$context = new Execute_Lock\Host_Context( $this, $this->get_host() );

		if ( $user_id = $this->get_login_user_id() ) {
			$context->set_login_user_id( $user_id );
		} elseif ( $username = $this->get_login_username() ) {
			$context->set_login_username( $username );
		}

		return $context;
	}
}
