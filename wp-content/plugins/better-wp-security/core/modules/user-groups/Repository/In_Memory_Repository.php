<?php

namespace iThemesSecurity\User_Groups\Repository;

use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\User_Groups\User_Group;

final class In_Memory_Repository implements Repository {
	/** @var array */
	private $memory;

	/**
	 * In_Memory_Repository constructor.
	 *
	 * @param array $memory
	 */
	public function __construct( array $memory = [] ) { $this->memory = $memory; }

	public function next_id() {
		return wp_generate_uuid4();
	}

	public function get( $id ) {
		if ( ! is_string( $id ) ) {
			throw new Invalid_Argument_Exception( __( 'uuid must be a string.', 'better-wp-security' ) );
		}

		if ( ! $this->has( $id ) ) {
			throw new User_Group_Not_Found( sprintf( __( "No user group found for '%s'", 'better-wp-security' ), $id ) );
		}

		return clone $this->memory[ $id ];
	}

	public function has( $id ) {
		return isset( $this->memory[ $id ] );
	}

	public function persist( User_Group $user_group, array $args = [] ) {
		$created = empty( $this->memory[ $user_group->get_id() ] );

		$this->memory[ $user_group->get_id() ] = $user_group;

		if ( $created ) {
			/**
			 * Fires when a user group is created.
			 *
			 * @since 6.4.0
			 *
			 * @param User_Group $user_group
			 */
			do_action( 'itsec_create_user_group', $user_group, $args );
		} else {
			/**
			 * Fires when a user group is updated.
			 *
			 * @since 6.4.0
			 *
			 * @param User_Group $user_group
			 */
			do_action( 'itsec_update_user_group', $user_group, $args );
		}
	}

	public function delete( User_Group $user_group ) {
		unset( $this->memory[ $user_group->get_id() ] );
	}

	public function all() {
		return array_values( $this->memory );
	}
}
