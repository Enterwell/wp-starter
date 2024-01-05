<?php

namespace iThemesSecurity\Modules\Core;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\User_Groups\Repository\Repository as User_Groups;
use iThemesSecurity\User_Groups\Repository\User_Group_Not_Found;

class User_Query_Extension implements Runnable {

	/** @var User_Groups */
	private $user_groups;

	public function __construct( User_Groups $user_groups ) { $this->user_groups = $user_groups; }

	public function run() {
		add_action( 'pre_get_users', [ $this, 'modify_query_vars' ] );
		add_action( 'pre_user_query', [ $this, 'modify_sql' ] );
	}

	/**
	 * Modifies the meta query of WP_User_Query to include custom query variables.
	 *
	 * @param \WP_User_Query $query
	 *
	 * @return void
	 */
	public function modify_query_vars( \WP_User_Query $query ) {
		$meta = [];

		if ( ! empty( $query->query_vars['solid_password_strength'] ) ) {
			$meta[] = $this->create_password_strength( $query->query_vars['solid_password_strength'] );
		}

		if ( ! empty( $query->query_vars['solid_last_seen'] ) ) {
			$meta[] = $this->create_date_range_meta_query( 'itsec_user_activity_last_seen', $query->query_vars['solid_last_seen'] );
		}

		if ( ! empty( $query->query_vars['solid_password_changed'] ) ) {
			$meta[] = $this->create_date_range_meta_query( 'itsec_last_password_change', $query->query_vars['solid_password_changed'] );
		}

		if ( ! empty( $query->query_vars['solid_2fa'] ) ) {
			$meta[] = $this->create_2fa( $query->query_vars['solid_2fa'] );
		}

		$meta = array_filter( $meta );

		if ( $meta ) {
			if ( empty( $query->query_vars['meta_query'] ) ) {
				$query->query_vars['meta_query'] = $meta;
			} else {
				$query->query_vars['meta_query'] = [
					'relation' => 'AND',
					$query->query_vars['meta_query'],
					array_merge( $meta, [
						'relation' => 'AND',
					] ),
				];
			}
		}

		if ( ! empty( $query->query_vars['solid_user_groups'] ) ) {
			[ 'users' => $users, 'roles' => $roles ] = $this->parse_user_groups( $query->query_vars['solid_user_groups'] );

			if ( $users && $roles ) {
				// Defer processing to ::apply_user_groups_to_sql.
				$query->query_vars['__solid_user_groups'] = compact( 'users', 'roles' );
			} elseif ( $users ) {
				$query->query_vars['include'] = array_merge( $users, $query->query_vars['include'] ?? [] );
			} elseif ( $roles ) {
				$query->query_vars['role__in'] = array_merge( $roles, $query->query_vars['role__in'] ?? [] );
			}
		}

		if ( ! empty( $query->query_vars['solid_user_groups__not_in'] ) ) {
			[ 'users' => $users, 'roles' => $roles ] = $this->parse_user_groups( $query->query_vars['solid_user_groups__not_in'] );

			if ( $users && $roles ) {
				// Defer processing to ::apply_user_groups_to_sql.
				$query->query_vars['__solid_user_groups__not_in'] = compact( 'users', 'roles' );
			} elseif ( $users ) {
				$query->query_vars['exclude'] = array_merge( $users, $query->query_vars['exclude'] ?? [] );
			} elseif ( $roles ) {
				$query->query_vars['role__not_in'] = array_merge( $roles, $query->query_vars['role__not_in'] ?? [] );
			}
		}
	}

	/**
	 * Modifies the SQL generated to find results in the queried user groups.
	 *
	 * This lets us assert that a user is either one of a set of IDs, or that the user
	 * has a certain role. Using `include` and `role__in` would create an AND relationship
	 * not an OR relationship.
	 *
	 * @param \WP_User_Query $query
	 *
	 * @return void
	 */
	public function modify_sql( \WP_User_Query $query ) {
		$this->generate_user_group_sql( $query, true );
		$this->generate_user_group_sql( $query, false );
	}

	private function generate_user_group_sql( \WP_User_Query $query, bool $in ) {
		global $wpdb;

		$field = $in ? '__solid_user_groups' : '__solid_user_groups__not_in';

		if ( empty( $query->query_vars[ $field ] ) ) {
			return;
		}

		$blog_id = absint( $query->query_vars['blog_id'] ?? 0 );

		[ 'users' => $users, 'roles' => $roles ] = $query->query_vars[ $field ];

		$operator   = $in ? 'IN' : 'NOT IN';
		$like       = $in ? 'LIKE' : 'NOT LIKE';
		$join_table = $in ? 'solid_umeta_in' : 'solid_umeta_not_in';

		$where = [];

		$prepare = $users;
		$where[] = "( {$wpdb->users}.ID {$operator} (" . implode( ',', array_fill( 0, count( $users ), '%d' ) ) . '))';

		foreach ( $roles as $role ) {
			$where[]   = "( {$join_table}.meta_key = %s AND {$join_table}.meta_value {$like} %s)";
			$prepare[] = $wpdb->get_blog_prefix( $blog_id ) . 'capabilities';
			$prepare[] = '%"' . $role . '"%';
		}

		$where = implode( $in ? ' OR ' : ' AND ', $where );

		$query->query_from  .= " INNER JOIN {$wpdb->usermeta} as {$join_table} ON ({$wpdb->users}.ID = {$join_table}.user_id)";
		$query->query_where .= $wpdb->prepare( " AND ({$where})", $prepare );

		if ( 0 !== strpos( $query->query_fields, 'DISTINCT ' ) ) {
			$query->query_fields = 'DISTINCT ' . $query->query_fields;
		}
	}

	private function create_password_strength( $value ): array {
		return [
			'key'     => \ITSEC_Strong_Passwords::STRENGTH_KEY,
			'value'   => $value,
			'compare' => 'IN',
		];
	}

	private function create_2fa( $value ): ?array {
		if ( $value === 'enabled' ) {
			return [
				'relation' => 'AND',
				[
					'key'     => '_two_factor_enabled_providers',
					'compare' => '!=',
					'value'   => serialize( [] ),
				],
				[
					'key'     => '_two_factor_enabled_providers',
					'compare' => 'EXISTS',
				]
			];
		} elseif ( $value === 'not-enabled' ) {
			return [
				'relation' => 'OR',
				[
					'key'   => '_two_factor_enabled_providers',
					'value' => serialize( [] ),
				],
				[
					'key'     => '_two_factor_enabled_providers',
					'compare' => 'NOT EXISTS',
				],
			];
		}

		return null;
	}

	private function create_date_range_meta_query( string $meta_key, $value ): array {
		[ $after, $before ] = $this->parse_date_range( $value );

		$meta = [
			'key'  => $meta_key,
			'type' => 'NUMERIC'
		];

		if ( $after && $before ) {
			$meta['compare'] = 'BETWEEN';
			$meta['value']   = [ $after, $before ];
		} elseif ( $after ) {
			$meta['compare'] = '>=';
			$meta['value']   = $after;
		} elseif ( $before ) {
			$meta['compare'] = '<=';
			$meta['value']   = $before;
		} else {
			$meta['compare'] = 'EXISTS';
		}

		return $meta;
	}

	private function parse_date_range( $value ): array {
		$after = $before = null;

		if ( isset( $value['after'] ) ) {
			$after = is_int( $value['after'] ) ? $value['after'] : strtotime( $value['after'] );
		}

		if ( isset( $value['before'] ) ) {
			$before = is_int( $value['before'] ) ? $value['before'] : strtotime( $value['before'] );
		}

		return [ $after, $before ];
	}

	private function parse_user_groups( $value ): array {
		$users = [];
		$roles = [];

		foreach ( $value as $uuid ) {
			try {
				$user_group = $this->user_groups->get( $uuid );
			} catch ( User_Group_Not_Found $e ) {
				continue;
			}

			$users[] = wp_list_pluck( $user_group->get_users(), 'ID' );
			$roles[] = $user_group->get_computed_role_list();
		}

		return [
			'users' => array_merge( [], ...$users ),
			'roles' => array_merge( [], ...$roles ),
		];
	}
}
