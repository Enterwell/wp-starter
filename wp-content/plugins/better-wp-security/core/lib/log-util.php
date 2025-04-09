<?php

final class ITSEC_Log_Util {
	public static function get_type_counts( $min_timestamp = 0 ) {
		global $wpdb;

		$where   = [];
		$prepare = [];

		if ( $min_timestamp > 0 ) {
			$where[]   = 'init_timestamp > %s';
			$prepare[] = date( 'Y-m-d H:i:s', $min_timestamp );
		}

		if ( $where ) {
			$where = 'WHERE ' . implode( ' AND ', $where );
		} else {
			$where = '';
		}

		$query = "SELECT type, COUNT(*) AS count FROM `{$wpdb->base_prefix}itsec_logs` {$where} GROUP BY type";

		if ( ! empty( $prepare ) ) {
			$query = $wpdb->prepare( $query, $prepare );
		}

		$results = $wpdb->get_results( $query, ARRAY_A );

		$counts = array();

		foreach ( $results as $result ) {
			if ( 'process-start' === $result['type'] ) {
				$result['type'] = 'process';
			}

			if ( isset( $counts[ $result['type'] ] ) ) {
				$counts[ $result['type'] ] += $result['count'];
			} else {
				$counts[ $result['type'] ] = $result['count'];
			}
		}

		return $counts;
	}

	public static function get_modules() {
		global $wpdb;

		$items = $wpdb->get_col( "SELECT DISTINCT module FROM {$wpdb->base_prefix}itsec_logs" );

		if ( ! is_array( $items ) ) {
			return array();
		}

		$modules = array();

		foreach ( $items as $module_slug ) {
			$labels = ITSEC_Modules::get_labels( $module_slug );

			if ( ! $labels ) {
				$labels = ITSEC_Modules::get_labels( str_replace( '_', '-', $module_slug ) );
			}

			if ( $labels ) {
				$modules[ $module_slug ] = $labels['title'];
			} else {
				$modules[ $module_slug ] = $module_slug;
			}
		}

		return $modules;
	}

	public static function get_entries( $filters = array(), $limit = 100, $page = 1, $sort_by_column = 'timestamp', $sort_direction = 'DESC', $columns = false ) {
		global $wpdb;


		$valid_columns = array(
			'id',
			'parent_id',
			'module',
			'type',
			'code',
			'timestamp',
			'init_timestamp',
			'remote_ip',
			'user_id',
			'url',
			'memory_current',
			'memory_peak',
		);


		$get_count     = false;
		$min_timestamp = $max_timestamp = false;

		$search = $filters['__search'] ?? '';
		unset( $filters['__search'] );

		if ( isset( $filters['__get_count'] ) ) {
			if ( $filters['__get_count'] ) {
				$get_count = true;
			}

			unset( $filters['__get_count'] );
		}

		if ( isset( $filters['__min_timestamp'] ) ) {
			$min_timestamp = $filters['__min_timestamp'];
			unset( $filters['__min_timestamp'] );
		}

		if ( isset( $filters['__max_timestamp'] ) ) {
			$max_timestamp = $filters['__max_timestamp'];
			unset( $filters['__max_timestamp'] );
		}


		$limit = max( 0, min( 100, intval( $limit ) ) );
		$page  = max( 1, intval( $page ) );

		if ( is_array( $sort_by_column ) ) {
			$regex_valid_columns = '(?:' . implode( '|', $valid_columns ) . ')';

			foreach ( $sort_by_column as $index => $sort_by ) {
				if ( in_array( $sort_by, $valid_columns ) ) {
					$sort_by_column[ $index ] = "$sort_by DESC";
				} elseif ( ! preg_match( "/^$regex_valid_columns\s+(?:DESC|ASC)$/i", $sort_by ) ) {
					unset( $sort_by_column[ $index ] );
				}
			}

			if ( empty( $sort_by_column ) ) {
				$sort_by_column = 'timestamp';
			}
		} elseif ( ! in_array( $sort_by_column, $valid_columns ) ) {
			$sort_by_column = 'timestamp';
		}

		$sort_direction = strtoupper( $sort_direction );
		if ( ! in_array( $sort_direction, array( 'DESC', 'ASC' ) ) ) {
			$sort_direction = 'DESC';
		}

		if ( false === $columns ) {
			$columns = $valid_columns;
		} elseif ( 'all' === $columns ) {
			$columns = array_merge( $valid_columns, array( 'data' ) );
		}


		if ( $get_count ) {
			$query = "SELECT COUNT(*) FROM `{$wpdb->base_prefix}itsec_logs`";
		} else {
			$query = "SELECT " . implode( ', ', $columns ) . " FROM `{$wpdb->base_prefix}itsec_logs`";
		}

		$prepare_args = array();


		$where_entries = array();

		foreach ( (array) $filters as $column => $value ) {
			if ( preg_match( '/^(.+)_not$/', $column, $match ) ) {
				$not    = true;
				$column = $match[1];
			} else {
				$not = false;
			}

			if ( preg_match( '/^(.+)_(min|max)$/', $column, $match ) ) {
				if ( ! in_array( $match[1], $valid_columns ) ) {
					continue;
				}

				if ( 'min' === $match[2] ) {
					$where_entries[] = "'$column'>=%s";
					$prepare_args[]  = $value;
				} else {
					$where_entries[] = "'column'<=%s";
					$prepare_args[]  = $value;
				}
			} elseif ( ! in_array( $column, $valid_columns ) ) {
				continue;
			} elseif ( is_array( $value ) ) {
				if ( ! empty( $value ) ) {
					$exact = [];
					$like  = [];

					foreach ( $value as $term ) {
						if ( false === strpos( $term, '%' ) ) {
							$exact[] = $term;
						} else {
							$like[] = $term;
						}
					}

					$list_wheres = [];

					if ( $exact ) {
						$operator = $not ? 'NOT IN' : 'IN';

						$list_wheres[] = "$column $operator (" . implode( ', ', array_fill( 0, count( $exact ), '%s' ) ) . ")";
						$prepare_args  = array_merge( $prepare_args, $exact );
					}

					if ( $like ) {
						$operator = $not ? 'NOT LIKE' : 'LIKE';

						$list_wheres[] = "(" . implode( ' OR ', array_fill( 0, count( $like ), "{$column} {$operator} %s" ) ) . ")";
						$prepare_args  = array_merge( $prepare_args, $like );
					}

					if ( count( $list_wheres ) === 1 ) {
						$where_entries[] = $list_wheres[0];
					} elseif ( count( $list_wheres ) === 2 ) {
						$where_entries[] = "({$list_wheres[0]} OR {$list_wheres[1]})";
					}
				}
			} elseif ( false !== strpos( $value, '%' ) ) {
				if ( $not ) {
					$where_entries[] = "$column NOT LIKE %s";
				} else {
					$where_entries[] = "$column LIKE %s";
				}
				$prepare_args[] = $value;
			} else {
				if ( $not ) {
					$where_entries[] = "$column<>%s";
				} else {
					$where_entries[] = "$column=%s";
				}
				$prepare_args[] = $value;
			}
		}

		if ( false !== $min_timestamp ) {
			$where_entries[] = 'timestamp>%s';
			$prepare_args[]  = date( 'Y-m-d H:i:s', $min_timestamp );
		}

		if ( false !== $max_timestamp ) {
			$where_entries[] = 'timestamp<%s';
			$prepare_args[]  = date( 'Y-m-d H:i:s', $max_timestamp );
		}

		if ( $search ) {
			$where_entries[] = '(remote_ip LIKE %s OR url LIKE %s)';
			$prepare_args[] = $wpdb->esc_like( $search ) . '%';
			$prepare_args[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		if ( $where_entries ) {
			$query .= ' WHERE ' . implode( ' AND ', $where_entries );
		}

		if ( ! $get_count ) {
			if ( ! is_array( $sort_by_column ) ) {
				$sort_by_column = array( "$sort_by_column $sort_direction" );
			}

			$query .= ' ORDER BY ' . implode( ', ', $sort_by_column );


			if ( $limit > 0 ) {
				$offset = ( $page - 1 ) * $limit;
				$query  .= " LIMIT $offset,$limit";
			}
		}

		if ( $prepare_args ) {
			$query = $wpdb->prepare( $query, $prepare_args );
		}

		if ( $get_count ) {
			return intval( $wpdb->get_var( $query ) );
		}

		$rows = $wpdb->get_results( $query, ARRAY_A );

		if ( is_null( $rows ) ) {
			return new WP_Error( 'itsec-log-util-failed-query', sprintf( esc_html__( 'A query failure prevented the log data from being accessed: %s', 'better-wp-security' ), $wpdb->last_error ) );
		}

		foreach ( $rows as $index => $row ) {
			if ( ! isset( $row['data'] ) ) {
				break;
			}

			$data = unserialize( $row['data'] );

			if ( false !== $data || 'b:0;' === $row['data'] ) {
				$rows[ $index ]['data'] = $data;
			}
		}

		return $rows;
	}

	public static function get_logs_page_screen_options() {
		$defaults = array(
			'per_page'     => 20,
			'default_view' => 'important',
			'color'        => true,
			'show_debug'   => false,
			'show_process' => false,
			'last_seen'    => 0,
		);

		$options = get_user_option( 'itsec_logs_page_screen_options' );

		if ( is_array( $options ) ) {
			$options = array_merge( $defaults, $options );
		} else {
			$options = $defaults;

			if ( $user = wp_get_current_user() ) {
				update_user_option( $user->ID, 'itsec_logs_page_screen_options', $options, true );
			}
		}

		return $options;
	}

	public static function set_logs_page_screen_options( $options ) {
		if ( ! $user = wp_get_current_user() ) {
			return;
		}

		if ( isset( $options['per_page'] ) && ( $options['per_page'] < 1 || $options['per_page'] > 999 ) ) {
			unset( $options['per_page'] );
		}
		if ( isset( $options['default_view'] ) && ! in_array( $options['default_view'], array( 'important', 'all', 'critical-issue' ) ) ) {
			unset( $options['default_view'] );
		}
		if ( isset( $options['last_seen'] ) ) {
			$options['last_seen'] = intval( $options['last_seen'] );

			if ( $options['last_seen'] < 0 || $options['last_seen'] > ITSEC_Core::get_current_time_gmt() ) {
				unset( $options['last_seen'] );
			}
		}

		$options = array_merge( self::get_logs_page_screen_options(), $options );

		update_user_option( $user->ID, 'itsec_logs_page_screen_options', $options, true );
	}

	public static function has_old_log_entries() {
		global $wpdb;

		if ( ! $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}itsec_log'" ) ) {
			return false;
		}

		$num_entries = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}itsec_log" );

		if ( empty( $num_entries ) ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}itsec_log" );
		}

		return true;
	}

	public static function migrate_old_log_entries() {
		global $wpdb;

		$max         = 50;
		$num_entries = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}itsec_log" );
		$num_loops   = min( $max, $num_entries );

		for ( $count = 1; $count <= $num_loops; $count ++ ) {
			$old_entry = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}itsec_log ORDER BY log_date_gmt LIMIT 1", ARRAY_A );
			$entry     = self::get_new_log_entry_from_old( $old_entry );

			$wpdb->insert( "{$wpdb->base_prefix}itsec_logs", $entry );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}itsec_log WHERE log_id=%d", $old_entry['log_id'] ) );
		}

		if ( $num_entries > $max ) {
			return false;
		}

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}itsec_log" );

		return true;
	}

	public static function get_new_log_entry_from_old( $old_entry ) {
		$old_entry['log_data'] = unserialize( $old_entry['log_data'] );

		$entry = array(
			'module'         => $old_entry['log_type'],
			'code'           => $old_entry['log_function'],
			'type'           => 'notice',
			'user_id'        => $old_entry['log_user'],
			'timestamp'      => $old_entry['log_date_gmt'],
			'init_timestamp' => $old_entry['log_date_gmt'],
			'remote_ip'      => $old_entry['log_host'],
			'url'            => $old_entry['log_url'],
		);

		if ( 'lockout' === $old_entry['log_type'] ) {
			if ( isset( $old_entry['log_data']['expires'] ) ) {
				$entry['type'] = 'action';

				if ( empty( $old_entry['log_host'] ) ) {
					$entry['code'] = 'user-lockout';
				} else {
					$entry['code'] = 'host-lockout';
				}
			} else {
				if ( empty( $old_entry['log_host'] ) ) {
					$entry['code'] = 'whitelisted-host-triggered-user-lockout';
				} else {
					$entry['code'] = 'whitelisted-host-triggered-host-lockout';
				}
			}
		} elseif ( 'file_change' === $old_entry['log_type'] ) {
			$entry['type'] = 'warning';
			$entry['code'] = 'changes-found';
			$entry['data'] = $old_entry['log_data'];
		} elseif ( 'malware' === $old_entry['log_type'] ) {
			$entry['code'] = 'scan';
			$entry['data'] = array( 'results' => $old_entry['log_data'] );
		} elseif ( 'backup' === $old_entry['log_type'] ) {
			$entry['code'] = 'details';
		} elseif ( 'four_oh_four' === $old_entry['log_type'] ) {
			$entry['code'] = 'found_404';
		} elseif ( 'ipcheck' === $old_entry['log_type'] ) {
			if ( empty( $old_entry['log_data'] ) ) {
				$entry['code'] = 'failed-login-by-blocked-ip';
			} else {
				$entry['type'] = 'action';
				$entry['code'] = 'ip-blocked';
			}
		} elseif ( 'brute_force' === $old_entry['log_type'] ) {
			if ( 'admin' === $old_entry['log_username'] ) {
				$entry['code'] = 'auto-ban-admin-username';
			} else {
				$entry['code'] = 'invalid-login';
			}
		} elseif ( 'away_mode' === $old_entry['log_type'] ) {
			$entry['code'] = 'away-mode-active';
		} elseif ( 'recaptcha' === $old_entry['log_type'] ) {
			$entry['code'] = 'failed-validation';
		} elseif ( 'user_logging' === $old_entry['log_type'] ) {
			if ( isset( $old_entry['log_data']['post'] ) ) {
				$entry['code'] = 'post-status-changed';
			} elseif ( empty( $old_entry['log_username'] ) ) {
				$entry['code'] = 'user-logged-out';
			} else {
				$entry['code'] = 'user-logged-in';
			}
		}

		if ( isset( $entry['data'] ) ) {
			$entry['data'] = serialize( $entry['data'] );
		}

		return $entry;
	}
}
