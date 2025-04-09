<?php

namespace iThemesSecurity\User_Groups\Repository;

use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\Exception\WP_Error;
use iThemesSecurity\User_Groups\User_Group;

final class DB_Repository implements Repository {

	/** @var \wpdb */
	private $wpdb;

	/**
	 * DB_Repository constructor.
	 *
	 * @param \wpdb $wpdb
	 */
	public function __construct( \wpdb $wpdb ) { $this->wpdb = $wpdb; }

	public function next_id() {
		return wp_generate_uuid4();
	}

	public function get( $id ) {
		if ( ! is_string( $id ) ) {
			throw new Invalid_Argument_Exception( __( 'Id must be a string.', 'better-wp-security' ) );
		}

		$record = $this->wpdb->get_row( $this->wpdb->prepare(
			"SELECT * FROM {$this->tn()} WHERE group_id = %s",
			$id
		), ARRAY_A );

		if ( ! $record ) {
			throw new User_Group_Not_Found( sprintf( __( "No user group found for '%s'", 'better-wp-security' ), $id ) );
		}

		return $this->hydrate( $record );
	}

	public function has( $id ) {
		if ( ! is_string( $id ) ) {
			throw new Invalid_Argument_Exception( __( 'Id must be a string.', 'better-wp-security' ) );
		}

		return (bool) $this->wpdb->get_var( $this->wpdb->prepare(
			"SELECT count(1) FROM {$this->tn()} WHERE group_id = %s",
			$id
		) );
	}

	public function persist( User_Group $user_group, array $args = [] ) {
		$this->wpdb->query( $this->wpdb->prepare(
			"INSERT INTO {$this->tn()} (group_id, group_label, group_roles, group_canonical, group_users, group_min_role, group_created_at) VALUES (%s, %s, %s, %s, %s, %s, %s)" .
			'ON DUPLICATE KEY UPDATE group_label = VALUES(group_label), group_roles = VALUES(group_roles), group_canonical = VALUES(group_canonical), group_users = VALUES(group_users), group_min_role = VALUES(group_min_role)',
			$user_group->get_id(),
			$user_group->get_label(),
			implode( ',', $user_group->get_roles() ),
			implode( ',', $user_group->get_canonical_roles() ),
			implode( ',', wp_list_pluck( $user_group->get_users(), 'ID' ) ),
			$user_group->get_min_role(),
			date( 'Y-m-d H:i:s' )
		) );

		// When a on duplicate key update updates a record, the number of affected rows is 2 instead of 1 for creating.
		$affected = $this->wpdb->rows_affected;

		if ( $affected === 1 ) {
			/**
			 * Fires when a user group is created.
			 *
			 * @since 6.4.0
			 *
			 * @param User_Group $user_group
			 * @param array      $args
			 */
			do_action( 'itsec_create_user_group', $user_group, $args );
		} else {
			/**
			 * Fires when a user group is updated.
			 *
			 * @since 6.4.0
			 *
			 * @param User_Group $user_group
			 * @param array      $args
			 */
			do_action( 'itsec_update_user_group', $user_group, $args );
		}
	}

	public function delete( User_Group $user_group ) {
		$deleted = $this->wpdb->delete( $this->tn(), [ 'group_id' => $user_group->get_id() ] );

		if ( false === $deleted ) {
			throw WP_Error::from_code( 'itsec-delete-user-group-failed', __( 'Failed to delete group.', 'better-wp-security' ) );
		}
	}

	public function all() {
		$records = $this->wpdb->get_results( "SELECT * FROM {$this->tn()} ORDER BY group_created_at ASC", ARRAY_A );

		return array_map( [ $this, 'hydrate' ], $records );
	}

	/**
	 * Hydrate a user group record.
	 *
	 * @param array $record
	 *
	 * @return User_Group
	 */
	private function hydrate( array $record ) {
		$group = new User_Group( $record['group_id'] );
		$group->set_label( $record['group_label'] );

		foreach ( wp_parse_list( $record['group_roles'] ) as $role ) {
			if ( get_role( $role ) ) {
				$group->add_role( $role );
			}
		}

		if ( get_role( $record['group_min_role'] ) ) {
			$group->set_min_role( $record['group_min_role'] );
		}

		if ( ! empty( $record['group_canonical'] ) ) {
			$group->set_canonical_roles( wp_parse_list( $record['group_canonical'] ) );
		}

		$group->set_users( array_filter( array_map( static function ( $id ) {
			return get_userdata( $id ) ?: null;
		}, wp_parse_id_list( $record['group_users'] ) ) ) );

		return $group;
	}

	private function tn() {
		return $this->wpdb->base_prefix . 'itsec_user_groups';
	}
}
