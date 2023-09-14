<?php

namespace iThemesSecurity\User_Groups\Repository;

use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\User_Groups\User_Group;

final class Eager_Loading_Decorator implements Repository {
	use Decorator;

	/** @var User_Group[] */
	private $all;

	public function __construct( Repository $decorates ) {
		$this->decorates = $decorates;
	}

	public function get( $id ) {
		if ( ! is_string( $id ) ) {
			throw new Invalid_Argument_Exception( __( 'Id must be a string.', 'better-wp-security' ) );
		}

		$this->all();

		if ( ! isset( $this->all[ $id ] ) ) {
			throw new User_Group_Not_Found( sprintf( __( "No user group found for '%s'", 'better-wp-security' ), $id ) );
		}

		return clone $this->all[ $id ];
	}

	public function has( $id ) {
		if ( ! is_string( $id ) ) {
			throw new Invalid_Argument_Exception( __( 'Id must be a string.', 'better-wp-security' ) );
		}

		$this->all();

		return isset( $this->all[ $id ] );
	}

	public function persist( User_Group $user_group, array $args = [] ) {
		try {
			if ( null !== $this->all ) {
				$this->all[ $user_group->get_id() ] = $user_group;
			}

			$this->decorates->persist( $user_group, $args );
		} catch ( \Exception $e ) {
			unset( $this->all[ $user_group->get_id() ] );

			throw $e;
		}
	}

	public function delete( User_Group $user_group ) {
		$this->decorates->delete( $user_group );
		if ( null !== $this->all ) {
			unset( $this->all[ $user_group->get_id() ] );
		}
	}

	public function all() {
		if ( null === $this->all ) {
			$this->all = [];

			foreach ( $this->decorates->all() as $user_group ) {
				$this->all[ $user_group->get_id() ] = $user_group;
			}
		}

		return array_map( 'wp_clone', array_values( $this->all ) );
	}
}
