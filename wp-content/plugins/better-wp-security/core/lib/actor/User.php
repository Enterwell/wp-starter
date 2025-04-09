<?php

namespace iThemesSecurity\Actor;

final class User implements Actor {

	/** @var \WP_User */
	private $user;

	/**
	 * User constructor.
	 *
	 * @param \WP_User $user
	 */
	public function __construct( \WP_User $user ) { $this->user = $user; }

	public function get_type() {
		return 'user';
	}

	public function get_identifier() {
		return (string) $this->user->ID;
	}

	public function __toString() {
		return $this->user->display_name;
	}
}
