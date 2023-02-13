<?php

namespace iThemesSecurity\Lib\Lockout\Execute_Lock;

use iThemesSecurity\Lib\Lockout\Execute_Lock\Source\Source;

final class Host_Context extends Context {

	/** @var string */
	private $host;

	/** @var bool */
	private $network_brute_force;

	/** @var int */
	private $login_user_id;

	/** @var string */
	private $login_username;

	/**
	 * ITSEC_Execute_Host_Lockout_Context constructor.
	 *
	 * @param Source $source
	 * @param string $host
	 */
	public function __construct( Source $source, $host ) {
		parent::__construct( $source );
		$this->host = $host;
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
	 * Get whether this is a Network Brute Force generated lockout.
	 *
	 * @return bool
	 */
	public function is_network_brute_force() {
		return $this->network_brute_force;
	}

	/**
	 * Set that this is a Network Brute Force generated lockout.
	 *
	 * @return $this
	 */
	public function set_network_brute_force() {
		$this->network_brute_force = true;

		return $this;
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
	 * Set who is being logging in as.
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

	public function to_legacy() {
		$legacy = parent::to_legacy();

		$legacy['host']         = $this->get_host();
		$legacy['network_lock'] = $this->is_network_brute_force();

		return $legacy;
	}
}
