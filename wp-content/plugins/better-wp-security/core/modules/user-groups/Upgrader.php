<?php

namespace iThemesSecurity\User_Groups;

use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\User_Groups\Repository\Repository;
use ITSEC_Lib_Canonical_Roles;

final class Upgrader {

	/** @var Repository */
	private $user_groups;

	/** @var User_Group[] */
	private $defaults = [];

	/**
	 * Upgrader constructor.
	 *
	 * @param Repository $user_groups
	 */
	public function __construct( Repository $user_groups ) {
		$this->user_groups = $user_groups;
	}

	/**
	 * Upgrade an existing setting to a user group.
	 *
	 * @param string   $label     Label to give the user group if a new one has to be created.
	 * @param callable $configure Callable to configure the user group.
	 *
	 * @return User_Group Either the newly created user group, or an existing user group that was an exact match.
	 */
	public function find_or_create( $label, callable $configure ) {
		$user_group = new User_Group( $this->user_groups->next_id() );
		$configure( $user_group );

		foreach ( $this->user_groups->all() as $maybe_group ) {
			if ( $maybe_group->equals( $user_group ) ) {
				return $maybe_group;
			}
		}

		$user_group->set_label( $label );
		$this->user_groups->persist( $user_group, [] );

		return $user_group;
	}

	/**
	 * Upgrade a min role based setting to a group based setting.
	 *
	 * @param string $min_role The minimum canonical role.
	 *
	 * @return string[] The list of user group IDs to store.
	 */
	public function upgrade_from_min_role( $min_role ) {
		$groups = [];

		foreach ( ITSEC_Lib_Canonical_Roles::get_canonical_roles_of_at_least( $min_role ) as $role ) {
			if ( 'super-admin' === $role ) {
				continue;
			}

			$groups[] = $this->get_default_group_id( $role );
		}

		if ( 'subscriber' === $min_role ) {
			$groups[] = 'everybody-else';
		}

		return $groups;
	}

	/**
	 * Get the default user group.
	 *
	 * @param string $name     The name of the group.
	 *
	 * @return string
	 *
	 * @throws Invalid_Argument_Exception
	 * @throws \iThemesSecurity\Exception\WP_Error
	 */
	public function get_default_group_id( $name ) {
		if ( ! in_array( $name, \ITSEC_Lib_Canonical_Roles::get_canonical_roles( false ), true ) ) {
			throw new Invalid_Argument_Exception( sprintf( __( 'No default group called %s', 'better-wp-security' ), $name ) );
		}

		if ( ! isset( $this->defaults[ $name ] ) ) {
			$this->create_default_groups();
		}

		if ( $this->user_groups->has( $this->defaults[ $name ] ) ) {
			return $this->defaults[ $name ];
		}

		$prototype = new User_Group( $this->user_groups->next_id() );
		$this->configure_group( $prototype, $name );

		foreach ( $this->user_groups->all() as $group ) {
			if ( $group->equals( $prototype ) ) {
				$this->defaults[ $name ] = $group->get_id();

				return $group->get_id();
			}
		}

		$this->user_groups->persist( $prototype, [ 'is_default' => $name ] );
		$this->defaults[ $name ] = $prototype->get_id();

		return $prototype->get_id();
	}

	/**
	 * Get the default canonical groups and the everybody-else group.
	 *
	 * @return string[]
	 */
	public function get_groups_for_all_users() {
		$groups = [];

		foreach ( \ITSEC_Lib_Canonical_Roles::get_canonical_roles( false ) as $canonical ) {
			if ( $group = $this->get_default_group_id( $canonical ) ) {
				$groups[] = $group;
			}
		}

		$groups[] = Everybody_Else::ID;

		return $groups;
	}

	/**
	 * Create the default user groups.
	 */
	private function create_default_groups() {
		foreach ( \ITSEC_Lib_Canonical_Roles::get_canonical_roles( false ) as $role ) {
			if ( ! isset( $this->defaults[ $role ] ) ) {
				$group = new User_Group( $this->user_groups->next_id() );
				$this->configure_group( $group, $role );
				$this->user_groups->persist( $group, [ 'is_default' => $role ] );
				$this->defaults[ $role ] = $group->get_id();
			}
		}
	}

	/**
	 * Configure a group
	 *
	 * @param User_Group $group
	 * @param string     $type
	 *
	 * @return void
	 */
	private function configure_group( User_Group $group, $type ) {
		$group->add_canonical_role( $type );
		$group->set_label( sprintf( __( '%s Users', 'better-wp-security' ), translate_user_role( ucfirst( $type ) ) ) );

		if ( $type === 'administrator' && is_multisite() ) {
			$group->add_canonical_role( 'super-admin' );
		}
	}
}
