<?php

namespace iThemesSecurity\User_Groups\Repository;

use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\User_Groups\User_Group;

final class Object_Caching_Decorator implements Repository {

	use Decorator;

	const GROUP = 'itsec-user-group';

	/**
	 * Caching_Decorator constructor.
	 *
	 * @param Repository $decorates
	 */
	public function __construct( Repository $decorates ) { $this->decorates = $decorates; }

	public function get( $id ) {
		if ( ! is_string( $id ) ) {
			throw new Invalid_Argument_Exception( __( 'Id must be a string.', 'better-wp-security' ) );
		}

		$serialized = wp_cache_get( $id, self::GROUP );

		if ( $serialized ) {
			return $this->hydrate( $id, $serialized );
		}

		$user_group = $this->decorates->get( $id );
		$this->cache( $user_group );

		return $user_group;
	}

	public function has( $id ) {
		if ( ! is_string( $id ) ) {
			throw new Invalid_Argument_Exception( __( 'Id must be a string.', 'better-wp-security' ) );
		}

		if ( wp_cache_get( $id, self::GROUP ) ) {
			return true;
		}

		return $this->decorates->has( $id );
	}

	public function persist( User_Group $user_group, array $args = [] ) {
		$this->decorates->persist( $user_group, $args );
		$this->cache( $user_group );
	}

	public function delete( User_Group $user_group ) {
		$this->decorates->delete( $user_group );
		wp_cache_delete( $user_group->get_id(), self::GROUP );
	}

	/**
	 * Cache the user group's data.
	 *
	 * @param User_Group $user_group
	 */
	private function cache( User_Group $user_group ) {
		wp_cache_set( $user_group->get_id(), $this->serialize( $user_group ), self::GROUP );
	}

	/**
	 * Serialize a user group to an array.
	 *
	 * @param User_Group $group
	 *
	 * @return array
	 */
	private function serialize( User_Group $group ) {
		return [
			'label'     => $group->get_label(),
			'roles'     => $group->get_roles(),
			'canonical' => $group->get_canonical_roles(),
			'min_role'  => $group->get_min_role(),
			'users'     => wp_list_pluck( $group->get_users(), 'ID' ),
		];
	}

	/**
	 * Hydrate a user group record.
	 *
	 * @param string $uuid
	 * @param array  $record
	 *
	 * @return User_Group
	 */
	private function hydrate( $uuid, $record ) {
		$group = new User_Group( $uuid );
		$group->set_label( $record['label'] );

		foreach ( $record['roles'] as $role ) {
			if ( get_role( $role ) ) {
				$group->add_role( $role );
			}
		}

		if ( get_role( $record['min_role'] ) ) {
			$group->set_min_role( $record['min_role'] );
		}

		if ( ! empty( $record['canonical'] ) ) {
			$group->set_canonical_roles( $record['canonical'] );
		}

		$group->set_users( array_filter( array_map( static function ( $id ) {
			return get_userdata( $id ) ?: null;
		}, $record['users'] ) ) );

		return $group;
	}
}
