<?php

namespace iThemesSecurity\Lib\Lockout;

use iThemesSecurity\Lib\Lockout\Execute_Lock\Source\Source;

final class Lockout implements Source {

	/** @var int */
	private $id;

	/** @var string */
	private $module;

	/** @var \DateTime */
	private $start;

	/** @var \DateTime */
	private $expire;

	/** @var string */
	private $host;

	/** @var int */
	private $user_id;

	/** @var string */
	private $username;

	/** @var bool */
	private $active;

	/** @var Context */
	private $context;

	/**
	 * Lockout constructor.
	 *
	 * @param int       $id
	 * @param string    $module
	 * @param \DateTime $start
	 * @param \DateTime $expire
	 * @param string    $host
	 * @param int       $user_id
	 * @param string    $username
	 * @param bool      $active
	 * @param Context   $context
	 */
	public function __construct( $id, $module, \DateTime $start, \DateTime $expire, $host, $user_id, $username, $active, Context $context = null ) {
		$this->id       = $id;
		$this->module   = $module;
		$this->start    = $start;
		$this->expire   = $expire;
		$this->host     = $host;
		$this->user_id  = $user_id;
		$this->username = $username;
		$this->active   = $active;
		$this->context  = $context;
	}

	/**
	 * Get the unique id for this lockout.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the lockout module responsible for this lockout.
	 *
	 * @return string
	 */
	public function get_module() {
		return $this->module;
	}

	/**
	 * Get the date & time when the lockout started. In UTC.
	 *
	 * @return \DateTime
	 */
	public function get_start() {
		return $this->start;
	}

	/**
	 * Get the date & time when the lockout has expired. In UTC.
	 *
	 * @return \DateTime
	 */
	public function get_expire() {
		return $this->expire;
	}

	/**
	 * Get the host that was locked out.
	 *
	 * @return string
	 */
	public function get_host() {
		return $this->host;
	}

	/**
	 * Get the user ID that was locked out.
	 *
	 * @return int
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	/**
	 * Get the username that was locked out.
	 *
	 * @return string
	 */
	public function get_username() {
		return $this->username;
	}

	/**
	 * Is the lockout marked as active. This does not check that
	 * the lockout has not expired.
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->active;
	}

	/**
	 * @return Context|null
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Make an execute lock context for this lockout.
	 *
	 * @return Execute_Lock\Context
	 */
	public function make_execute_lock_context() {
		if ( $context = $this->get_context() ) {
			return $context->make_execute_lock_context()->with_source( $this );
		}

		if ( $this->get_host() ) {
			return new Execute_Lock\Host_Context( $this, $this->get_host() );
		}

		if ( $this->get_user_id() ) {
			return new Execute_Lock\User_Context( $this, $this->get_user_id() );
		}

		if ( $this->get_username() ) {
			return new Execute_Lock\Username_Context( $this, $this->get_username() );
		}

		throw new \UnexpectedValueException( __( 'Unable to generate context for lockout.', 'better-wp-security' ) );
	}

	public function get_source_slug() {
		return 'lockout-' . $this->id;
	}
}
