<?php

namespace iThemesSecurity\User_Groups;

use InvalidArgumentException;
use WP_User;
use ITSEC_Lib;
use ITSEC_Lib_Canonical_Roles;

final class User_Group implements Matchable, \JsonSerializable {

	/** @var string */
	private $id;

	/** @var string */
	private $label = '';

	/** @var int[] */
	private $users = [];

	/** @var string[] */
	private $roles = [];

	/** @var array */
	private $canonical_roles = [];

	/** @var string */
	private $min_role = '';

	/**
	 * $this constructor.
	 *
	 * @param string $id
	 */
	public function __construct( $id ) {
		if ( ! wp_is_uuid( $id, 4 ) ) {
			throw new InvalidArgumentException( 'id must be a UUIDv4' );
		}

		$this->id = $id;
	}

	/**
	 * Get the id.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Add a user to this group.
	 *
	 * @param WP_User $user
	 *
	 * @return $this
	 */
	public function add_user( WP_User $user ) {
		if ( ! in_array( $user->ID, $this->users, true ) ) {
			$this->users[] = $user->ID;
		}

		return $this;
	}

	/**
	 * Remove a user from this group.
	 *
	 * @param WP_User $user
	 *
	 * @return $this
	 */
	public function remove_user( WP_User $user ) {
		if ( ( $i = array_search( $user->ID, $this->users, true ) ) !== false ) {
			unset( $this->users[ $i ] );
		}

		return $this;
	}

	/**
	 * Set the list of users to contain in this group.
	 *
	 * @param WP_User[] $users
	 *
	 * @return $this
	 */
	public function set_users( array $users ) {
		$this->users = [];
		array_walk( $users, [ $this, 'add_user' ] );

		return $this;
	}

	/**
	 * Get the list of users contained in this group.
	 *
	 * @return WP_User[]
	 */
	public function get_users() {
		return array_filter( array_map( 'get_userdata', $this->users ), static function ( $user ) {
			return $user instanceof WP_User;
		} );
	}

	/**
	 * Add a role to the user group.
	 *
	 * @param string $role
	 *
	 * @return $this
	 */
	public function add_role( $role ) {
		if ( ! get_role( $role ) ) {
			throw new InvalidArgumentException( sprintf( 'Role %s not found.', $role ) );
		}

		$this->roles[] = $role;

		return $this;
	}

	/**
	 * Remove a role from the user group.
	 *
	 * @param string $role
	 *
	 * @return $this
	 */
	public function remove_role( $role ) {
		if ( ( $i = array_search( $role, $this->roles, true ) ) !== false ) {
			unset( $this->roles[ $i ] );
		}

		return $this;
	}

	/**
	 * Set the list of roles for the user group.
	 *
	 * @param string[] $roles
	 *
	 * @return $this
	 */
	public function set_roles( array $roles ) {
		$this->roles = [];
		array_walk( $roles, [ $this, 'add_role' ] );

		return $this;
	}

	/**
	 * Get the list of roles for the user group.
	 *
	 * @return string[]
	 */
	public function get_roles() {
		return $this->roles;
	}

	/**
	 * Add a canonical role to the user group.
	 *
	 * @param string $role
	 *
	 * @return $this
	 */
	public function add_canonical_role( $role ) {
		if ( ! in_array( $role, ITSEC_Lib_Canonical_Roles::get_canonical_roles(), true ) ) {
			throw new InvalidArgumentException( sprintf( 'Role %s is not a valid canonical role.', $role ) );
		}

		$this->canonical_roles[] = $role;

		return $this;
	}

	/**
	 * Remove a canonical role from the user group.
	 *
	 * @param string $role
	 *
	 * @return $this
	 */
	public function remove_canonical_role( $role ) {
		if ( ( $i = array_search( $role, $this->canonical_roles, true ) ) !== false ) {
			unset( $this->canonical_roles[ $i ] );
		}

		return $this;
	}

	/**
	 * Set the list of roles for the user group.
	 *
	 * @param string[] $roles
	 *
	 * @return $this
	 */
	public function set_canonical_roles( array $roles ) {
		$this->canonical_roles = [];
		array_walk( $roles, [ $this, 'add_canonical_role' ] );

		return $this;
	}

	/**
	 * Get the list of canonical roles for the user group.
	 *
	 * @return string[]
	 */
	public function get_canonical_roles() {
		return $this->canonical_roles;
	}

	/**
	 * Get the role that users must be greater than for the group to apply.
	 *
	 * @return string
	 */
	public function get_min_role() {
		return $this->min_role;
	}

	/**
	 * Set the role that users must be greater than for the group to apply.
	 *
	 * @param string $role
	 *
	 * @return $this
	 */
	public function set_min_role( $role ) {
		if ( '' !== $role && ! get_role( $role ) ) {
			throw new InvalidArgumentException( sprintf( 'Role %s not found.', $role ) );
		}

		$this->min_role = $role;

		return $this;
	}

	/**
	 * Get the user provided label for this user group.
	 *
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * Set the label for this user group.
	 *
	 * @param string $label
	 *
	 * @return $this
	 */
	public function set_label( $label ) {
		if ( ! is_string( $label ) ) {
			throw new InvalidArgumentException( '$label must be a string.' );
		}

		$this->label = $label;

		return $this;
	}

	/**
	 * Get the generated description for this user group.
	 *
	 * @return string
	 */
	public function get_description() {
		$parts = [];

		if ( $roles = $this->get_roles() ) {
			$parts[] = wp_sprintf( __( 'the following roles: %l', 'better-wp-security' ), array_map( 'translate_user_role', $roles ) );
		}

		if ( $canonical = $this->get_canonical_roles() ) {
			$parts[] = wp_sprintf( __( 'the following canonical roles: %l', 'better-wp-security' ), array_map( 'translate_user_role', $canonical ) );
		}

		if ( $min_role = $this->get_min_role() ) {
			$parts[] = sprintf( __( 'roles greater than %s', 'better-wp-security' ), translate_user_role( $min_role ) );
		}

		if ( $users = $this->get_users() ) {
			$parts[] = wp_sprintf( __( 'the following users: %l', 'better-wp-security' ), wp_list_pluck( $users, 'user_login' ) );
		}

		if ( ! $parts ) {
			return wp_sprintf( __( 'This user group contains no users.', 'better-wp-security' ) );
		}

		return wp_sprintf( __( 'This user group contains %l.', 'better-wp-security' ), $parts );
	}

	/**
	 * Get the computed list of roles this user group applies to.
	 *
	 * This is the manually selected list of roles, as well as all roles greater
	 * than the minimum role.
	 *
	 * @return string[]
	 */
	public function get_computed_role_list() {
		ITSEC_Lib::load( 'canonical-roles' );
		$roles           = $this->get_roles();
		$min_role        = $this->get_min_role();
		$canonical_roles = $this->get_canonical_roles();

		foreach ( wp_roles()->get_names() as $role => $name ) {
			if ( in_array( $role, $roles, true ) ) {
				continue;
			}

			$canonical = ITSEC_Lib_Canonical_Roles::get_canonical_role_from_role( $role );

			if ( in_array( $canonical, $canonical_roles, true ) ) {
				$roles[] = $role;
				continue;
			}

			if ( $min_role && ITSEC_Lib_Canonical_Roles::is_canonical_role_at_least( $min_role, $canonical ) ) {
				$roles[] = $role;
				continue;
			}
		}

		return $roles;
	}

	/**
	 * Is the user group properly configured.
	 *
	 * @return bool
	 */
	public function is_configured() {
		return $this->min_role || $this->roles || $this->users || $this->canonical_roles;
	}

	/**
	 * Does the given user match this user group.
	 *
	 * @param Match_Target $target
	 *
	 * @return bool
	 */
	public function matches( Match_Target $target ) {
		if ( ( $user = $target->get_user() ) && in_array( $user->ID, $this->users, true ) ) {
			return true;
		}

		if ( array_intersect( $this->get_roles(), $target->get_role() ? [ $target->get_role() ] : $user->roles ) ) {
			return true;
		}

		$min_role        = $this->get_min_role();
		$canonical_roles = $this->get_canonical_roles();

		if ( $min_role || $canonical_roles ) {
			ITSEC_Lib::load( 'canonical-roles' );

			if ( $target->get_role() && $target->get_user() ) {
				$canonical = ITSEC_Lib_Canonical_Roles::get_canonical_role_from_role_and_user( $target->get_role(), $target->get_user() );
			} elseif ( $target->get_role() ) {
				$canonical = ITSEC_Lib_Canonical_Roles::get_canonical_role_from_role( $target->get_role() );
			} elseif ( $target->get_user() ) {
				$canonical = ITSEC_Lib_Canonical_Roles::get_user_role( $target->get_user() );
			} else {
				$canonical = '';
			}

			if ( $canonical ) {
				if ( $min_role && ITSEC_Lib_Canonical_Roles::is_canonical_role_at_least( $min_role, $canonical ) ) {
					return true;
				}

				if ( $canonical_roles && in_array( $canonical, $canonical_roles, true ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Is this user group the same record as the given user group.
	 *
	 * @param User_Group $user_group
	 *
	 * @return bool
	 */
	public function identical( User_Group $user_group ) {
		return $this->get_id() === $user_group->get_id();
	}

	/**
	 * Is this user group made up of the same restrictions as the given user group.
	 *
	 * @param User_Group $user_group
	 *
	 * @return bool
	 */
	public function equals( User_Group $user_group ) {
		if ( $this->get_min_role() !== $user_group->get_min_role() ) {
			return false;
		}

		if ( ! ITSEC_Lib::equal_sets( $this->get_roles(), $user_group->get_roles() ) ) {
			return false;
		}

		if ( ! ITSEC_Lib::equal_sets( $this->get_canonical_roles(), $user_group->get_canonical_roles() ) ) {
			return false;
		}

		if ( ! ITSEC_Lib::equal_sets( $this->users, $user_group->users ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Converts the user group to a string.
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->get_label();
	}

	public function jsonSerialize(): array {
		return [
			'id'          => $this->get_id(),
			'label'       => $this->get_label(),
			'description' => $this->get_description(),
			'users'       => wp_list_pluck( $this->get_users(), 'ID' ),
			'roles'       => $this->get_roles(),
			'canonical'   => $this->get_canonical_roles(),
			'min_role'    => $this->get_min_role(),
		];
	}
}
