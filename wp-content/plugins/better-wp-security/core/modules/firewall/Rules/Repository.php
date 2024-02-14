<?php

namespace iThemesSecurity\Modules\Firewall\Rules;

use iThemesSecurity\Lib\Result;

class Repository implements Loader {

	/** @var \wpdb */
	private $wpdb;

	/** @var string */
	private $table;

	public function __construct( \wpdb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->base_prefix . 'itsec_firewall_rules';
	}

	/**
	 * Finds a firewall rule by its id.
	 *
	 * @param int $id
	 *
	 * @return Result<Rule|null>
	 */
	public function find( int $id ): Result {
		$data = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d", $id
			),
			ARRAY_A
		);

		if ( $this->wpdb->last_error ) {
			return Result::error( new \WP_Error(
				'itsec.firewall.db-error',
				__( 'Could not lookup a firewall rule.', 'better-wp-security' ),
				[
					'id'    => $id,
					'error' => $this->wpdb->last_error,
				]
			) );
		}

		if ( ! $data ) {
			return Result::success();
		}

		try {
			return Result::success( $this->hydrate( $data ) );
		} catch ( \Exception $e ) {
			return Result::error( new \WP_Error(
				'itsec.firewall.invalid-rule',
				__( 'The firewall rule contains invalid data.', 'better-wp-security' ),
				[
					'id'    => $id,
					'error' => $e->getMessage(),
				]
			) );
		}
	}

	/**
	 * Persists a rule to the database.
	 *
	 * @param Rule $rule
	 *
	 * @return Result<Rule>
	 */
	public function persist( Rule $rule ): Result {
		if ( $rule->get_id() ) {
			$this->wpdb->update(
				$this->table,
				[
					'name'      => $rule->get_name(),
					'paused_at' => $rule->get_paused_at() ? $rule->get_paused_at()->format( 'Y-m-d H:i:s' ) : null,
					'config'    => wp_json_encode( $rule->get_config() ),
				],
				[ 'id' => $rule->get_id(), ],
				[],
				[ 'id' => '%d' ]
			);
		} else {
			$this->wpdb->insert( $this->table, [
				'provider'      => $rule->get_provider(),
				'provider_ref'  => $rule->get_provider_ref(),
				'name'          => $rule->get_name(),
				'vulnerability' => $rule->get_vulnerability(),
				'config'        => wp_json_encode( $rule->get_config() ),
				'created_at'    => $rule->get_created_at()->format( 'Y-m-d H:i:s' ),
				'paused_at'     => $rule->get_paused_at() ? $rule->get_paused_at()->format( 'Y-m-d H:i:s' ) : null,
			] );
		}

		if ( $this->wpdb->last_error || ! $this->wpdb->rows_affected ) {
			return Result::error( new \WP_Error(
				'itsec.firewall.db-error',
				__( 'Could not persist a firewall rule.', 'better-wp-security' ),
				[
					'id'    => $rule->get_id(),
					'error' => $this->wpdb->last_error,
				]
			) );
		}

		if ( ! $rule->get_id() && $insert_id = $this->wpdb->insert_id ) {
			\Closure::bind( function () use ( $insert_id ) {
				$this->id = $insert_id;
			}, $rule, $rule )();
		}

		/**
		 * Fires when a firewall rule is saved.
		 *
		 * @param Rule $rule
		 */
		do_action( 'itsec_firewall_saved_rule', $rule );

		return Result::success( $rule );
	}

	/**
	 * Deletes a firewall rule.
	 *
	 * @param Rule $rule
	 *
	 * @return Result
	 */
	public function delete( Rule $rule ): Result {
		$this->wpdb->delete( $this->table, [ 'id' => $rule->get_id() ], [ 'id' => '%d' ] );

		if ( $this->wpdb->last_error || ! $this->wpdb->rows_affected ) {
			return Result::error( new \WP_Error(
				'itsec.firewall.db-error',
				__( 'Could not delete a firewall rule.', 'better-wp-security' ),
				[
					'id'    => $rule->get_id(),
					'error' => $this->wpdb->last_error,
				]
			) );
		}

		/**
		 * Fires when a firewall rule is deleted.
		 *
		 * @param Rule $rule
		 */
		do_action( 'itsec_firewall_deleted_rule', $rule );

		return Result::success();
	}

	/**
	 * Deletes any rules matching the given query options.
	 *
	 * @param Rules_Options $options
	 *
	 * @return Result<int> On success, the count of rules deleted.
	 */
	public function delete_rules( Rules_Options $options ): Result {
		$sql = "DELETE FROM {$this->table}";

		[ $where, $prepare ] = $this->build_where_clause( $options );

		$sql .= $where;

		$this->wpdb->query( $this->wpdb->prepare( $sql, $prepare ) );

		if ( $this->wpdb->last_error ) {
			return Result::error( new \WP_Error(
				'itsec.firewall.db-error',
				__( 'Could not delete firewall rules.', 'better-wp-security' ),
				[
					'error' => $this->wpdb->last_error,
				]
			) );
		}

		return Result::success( $this->wpdb->rows_affected );
	}

	/**
	 * Fetches rules from the DB.
	 *
	 * @param Rules_Options $options
	 *
	 * @return Result<Rule[]>
	 */
	public function get_rules( Rules_Options $options ): Result {
		$sql = "SELECT * FROM {$this->table}";

		[ $where, $prepare ] = $this->build_where_clause( $options );

		$sql .= $where;

		$sql .= ' ORDER BY `id` DESC';

		if ( $options->get_per_page() ) {
			$sql .= sprintf( ' LIMIT %d, %d', $options->get_per_page() * ( $options->get_page() - 1 ), $options->get_per_page() );
		}

		if ( $prepare ) {
			$execute = $this->wpdb->prepare( $sql, $prepare );
		} else {
			$execute = $sql;
		}

		$results = $this->wpdb->get_results( $execute, ARRAY_A );

		if ( $this->wpdb->last_error ) {
			return Result::error( new \WP_Error(
				'itsec.firewall.db-error',
				__( 'Could not fetch firewall rules.', 'better-wp-security' ),
				[
					'error' => $this->wpdb->last_error,
				]
			) );
		}

		return Result::success( array_filter( array_map( [ $this, 'try_hydrate' ], $results ) ) );
	}

	/**
	 * Count rules in the DB.
	 *
	 * @param Rules_Options $options
	 *
	 * @return Result<int>
	 */
	public function count_rules( Rules_Options $options ): Result {

		$sql = "SELECT count(*) as c FROM {$this->table}";

		[ $where, $prepare ] = $this->build_where_clause( $options );
		$sql .= $where;

		$count = $this->wpdb->get_var( $this->wpdb->prepare( $sql, $prepare ) );

		if ( $this->wpdb->last_error ) {
			return Result::error( new \WP_Error(
				'itsec.firewall.db-error',
				__( 'Could not count firewall rules.', 'better-wp-security' ),
				[
					'error' => $this->wpdb->last_error,
				]
			) );
		}

		return Result::success( (int) $count );
	}

	private function build_where_clause( Rules_Options $options ): array {
		$wheres  = [];
		$prepare = [];

		if ( $providers = $options->get_providers() ) {
			$wheres[] = sprintf(
				'`provider` IN (%s)',
				implode( ', ', array_fill( 0, count( $providers ), '%s' ) )
			);
			$prepare  = array_merge( $prepare, $providers );
		}

		if ( $vulnerabilities = $options->get_vulnerabilities() ) {
			$wheres[] = sprintf(
				'`vulnerability` IN (%s)',
				implode( ', ', array_fill( 0, count( $vulnerabilities ), '%s' ) )
			);
			$prepare  = array_merge( $prepare, $vulnerabilities );
		}

		if ( $options->get_paused() === true ) {
			$wheres[] = '`paused_at` IS NOT NULL';
		} elseif ( $options->get_paused() === false ) {
			$wheres[] = '`paused_at` IS NULL';
		}

		if ( $search = $options->get_search() ) {
			$wheres[]  = '`name` LIKE %s';
			$prepare[] = '%' . $this->wpdb->esc_like( $search ) . '%';
		}

		if ( $provider_refs = $options->get_provider_refs() ) {
			$wheres[] = sprintf(
				'(%s)',
				implode( ' OR ', array_map( function ( array $rule ) use ( &$prepare ) {
					$prepare[] = $rule['provider'];
					$prepare[] = $rule['ref'];

					return '(`provider` = %s AND `provider_ref` = %s)';
				}, $provider_refs ) )
			);
		}

		if ( ! $wheres ) {
			return [ '', [] ];
		}

		return [ ' WHERE ' . implode( ' AND ', $wheres ), $prepare ];
	}

	/**
	 * Loads the set of firewall rules to execute.
	 *
	 * @return array
	 */
	public function load_rules(): array {
		$rows = $this->wpdb->get_results( "SELECT `id`, `vulnerability`, `config` FROM {$this->table} WHERE `paused_at` IS NULL", ARRAY_A );

		if ( $this->wpdb->last_error ) {
			return [];
		}

		return array_filter( array_map( function ( $row ) {
			$data = json_decode( $row['config'], true );

			if ( ! is_array( $data ) ) {
				return null;
			}

			$id = $row['id'];

			if ( $row['vulnerability'] ) {
				$id .= '|' . $row['vulnerability'];
			}

			return array_merge( $data, [ 'id' => $id ] );
		}, $rows ) );
	}

	private function try_hydrate( array $data ): ?Rule {
		try {
			return $this->hydrate( $data );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	private function hydrate( array $data ): Rule {
		return new Rule(
			$data['id'],
			$data['provider'],
			$data['provider_ref'],
			$data['name'],
			$data['vulnerability'],
			json_decode( $data['config'], true ),
			new \DateTimeImmutable( $data['created_at'], new \DateTimeZone( 'UTC' ) ),
			$data['paused_at'] ? new \DateTimeImmutable( $data['paused_at'], new \DateTimeZone( 'UTC' ) ) : null
		);
	}
}
