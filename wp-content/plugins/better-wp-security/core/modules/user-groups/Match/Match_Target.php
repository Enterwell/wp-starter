<?php

namespace iThemesSecurity\User_Groups;

use WP_User;

final class Match_Target {

	/** @var WP_User|null */
	private $user;

	/** @var string */
	private $role;

	/**
	 * Match_Target constructor.
	 *
	 * @param WP_User|null $user
	 * @param string        $role
	 */
	private function __construct( WP_User $user = null, $role = '' ) {
		$this->user = $user;
		$this->role = $role;
	}

	/**
	 * Creates a new Match Target with a user.
	 *
	 * @param WP_User $user User to match against.
	 * @param string   $role Optionally, specify the role to check against instead of the user's current role.
	 *
	 * @return Match_Target
	 */
	public static function for_user( WP_User $user, $role = '' ) {
		return new self( $user, $role );
	}

	/**
	 * Creates a new Match Target for a role.
	 *
	 * @param string $role
	 *
	 * @return Match_Target
	 */
	public static function for_role( $role ) {
		return new self( null, $role );
	}

	/**
	 * Gets the user to match against.
	 *
	 * @return WP_User|null The user may not be defined.
	 */
	public function get_user() {
		return $this->user;
	}

	/**
	 * Gets the role to check against.
	 *
	 * @return string
	 */
	public function get_role() {
		return $this->role;
	}
}
