<?php

namespace iThemesSecurity\User_Groups\Module;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\User_Groups\Everybody_Else;
use iThemesSecurity\User_Groups\Repository\Repository;
use iThemesSecurity\User_Groups\User_Group;
use ITSEC_Settings;

class Settings extends ITSEC_Settings implements Runnable {
	/** @var Repository */
	private $repository;

	/**
	 * Settings constructor.
	 *
	 * @param Repository $repository
	 */
	public function __construct( Repository $repository ) {
		$this->repository = $repository;
		parent::__construct();
	}

	public function run() {
		\ITSEC_Modules::register_settings( $this );
	}

	public function get_id() {
		return 'user-groups';
	}

	public function get_defaults() {
		return [
			'default_groups' => [],
		];
	}

	/**
	 * Get the default user group.
	 *
	 * @param string $name     The name of the gorup.
	 * @param bool   $recreate Whether to recreate the group if it was deleted.
	 *
	 * @return string
	 *
	 * @throws Invalid_Argument_Exception
	 * @throws \iThemesSecurity\Exception\WP_Error
	 */
	public function get_default_group_id( $name, $recreate = false ) {
		if ( ! in_array( $name, \ITSEC_Lib_Canonical_Roles::get_canonical_roles( false ), true ) ) {
			throw new Invalid_Argument_Exception( sprintf( __( 'No default group called %s', 'better-wp-security' ), $name ) );
		}

		$defaults = $this->get( 'default_groups' );

		if ( ! isset( $defaults[ $name ] ) ) {
			$this->create_default_groups();
			$defaults = $this->get( 'default_groups' );
		}

		if ( $this->repository->has( $defaults[ $name ] ) ) {
			return $defaults[ $name ];
		}

		$prototype = new User_Group( $this->repository->next_id() );
		$this->configure_group( $prototype, $name );

		foreach ( $this->repository->all() as $group ) {
			if ( $group->equals( $prototype ) ) {
				$defaults[ $name ] = $group->get_id();
				$this->set( 'default_groups', $defaults );

				return $group->get_id();
			}
		}

		if ( $recreate ) {
			$this->repository->persist( $prototype );
			$defaults[ $name ] = $prototype->get_id();

			$this->set( 'default_groups', $defaults );

			return $prototype->get_id();
		}

		return '';
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
		$defaults = $this->get( 'default_groups' );
		$save     = false;

		foreach ( \ITSEC_Lib_Canonical_Roles::get_canonical_roles( false ) as $role ) {
			if ( ! isset( $defaults[ $role ] ) ) {
				$group = new User_Group( $this->repository->next_id() );
				$this->configure_group( $group, $role );
				$this->repository->persist( $group );
				$defaults[ $role ] = $group->get_id();

				$save = true;
			}
		}

		if ( $save ) {
			$this->set( 'default_groups', $defaults );
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
