<?php

namespace iThemesSecurity\Site_Scanner\Repository;

use iThemesSecurity\Lib\Result;
use iThemesSecurity\Site_Scanner\Vulnerability;
use iThemesSecurity\Site_Scanner\Vulnerability_Issue;

class Vulnerabilities_Repository {

	/** @var \wpdb */
	private $wpdb;

	public function __construct( \wpdb $wpdb ) { $this->wpdb = $wpdb; }

	/**
	 * Finds a vulnerability by its id.
	 *
	 * @param string $id
	 *
	 * @return Result<Vulnerability|null>
	 */
	public function find( string $id ): Result {
		$data = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->wpdb->base_prefix}itsec_vulnerabilities WHERE id = %s", $id
			),
			ARRAY_A
		);

		if ( $this->wpdb->last_error ) {
			return Result::error( new \WP_Error(
				'itsec.site-scanner.vulnerabilities.db-error',
				__( 'Could not lookup a vulnerability.', 'better-wp-security' ),
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
				'itsec.site-scanner.vulnerabilities.invalid-vulnerability',
				__( 'The vulnerability contains invalid data.', 'better-wp-security' ),
				[
					'id'    => $id,
					'error' => $e->getMessage(),
				]
			) );
		}
	}

	/**
	 * Finds or creates a Vulnerability for a Site Scan issue.
	 *
	 * @param Vulnerability_Issue $issue
	 *
	 * @return Result<Vulnerability>
	 */
	public function for_issue( Vulnerability_Issue $issue ): Result {
		$found = $this->find( $issue->get_id() );

		if ( ! $found->is_success() ) {
			return $found;
		}

		$vulnerability = $found->get_data();

		if ( ! $vulnerability ) {
			$vulnerability = new Vulnerability(
				$issue->get_id(),
				$issue->get_meta()['type'],
				$issue->get_meta()['type'] === Vulnerability::T_WORDPRESS
					? ''
					: $issue->get_meta()['software']['slug'],
				$issue->get_meta()['issue']
			);
		}

		return Result::success( $vulnerability );
	}

	/**
	 * Persists a vulnerability.
	 *
	 * @param Vulnerability $vulnerability
	 *
	 * @return Result<Vulnerability>
	 */
	public function persist( Vulnerability $vulnerability ): Result {
		$this->wpdb->replace( $this->wpdb->base_prefix . 'itsec_vulnerabilities', [
			'id'            => $vulnerability->get_id(),
			'software_type' => $vulnerability->get_software_type(),
			'software_slug' => $vulnerability->get_software_slug(),
			'first_seen'    => $vulnerability->get_first_seen()->format( 'Y-m-d H:i:s' ),
			'last_seen'     => $vulnerability->get_last_seen()->format( 'Y-m-d H:i:s' ),
			'resolved_at'   => $vulnerability->get_resolved_at()
				? $vulnerability->get_resolved_at()->format( 'Y-m-d H:i:s' )
				: null,
			'resolved_by'   => $vulnerability->get_resolved_by()
				? $vulnerability->get_resolved_by()->ID
				: 0,
			'resolution'    => $vulnerability->get_resolution(),
			'details'       => wp_json_encode( $vulnerability->get_details() ),
		] );

		if ( $this->wpdb->last_error ) {
			return Result::error( new \WP_Error(
				'itsec.site-scanner.vulnerabilities.db-error',
				__( 'Could not persist a vulnerability.', 'better-wp-security' ),
				[
					'id'    => $vulnerability->get_id(),
					'error' => $this->wpdb->last_error,
				]
			) );
		}

		return Result::success( $vulnerability );
	}

	/**
	 * Fetches vulnerabilities from the DB.
	 *
	 * @param Vulnerabilities_Options $options
	 *
	 * @return Result<Vulnerability[]>
	 */
	public function get_vulnerabilities( Vulnerabilities_Options $options ): Result {

		$sql = "SELECT * FROM {$this->wpdb->base_prefix}itsec_vulnerabilities";

		[ $where, $prepare ] = $this->build_where_clause( $options );

		$sql .= $where;

		$sql .= ' ORDER BY `last_seen` DESC, `id` DESC';

		if ( $options->get_per_page() ) {
			$sql .= sprintf( ' LIMIT %d, %d', $options->get_per_page() * ( $options->get_page() - 1 ), $options->get_per_page() );
		}

		if ( $prepare ) {
			$prepared = $this->wpdb->prepare( $sql, $prepare );
		} else {
			$prepared = $sql;
		}

		$results = $this->wpdb->get_results( $prepared, ARRAY_A );

		if ( $this->wpdb->last_error ) {
			return Result::error( new \WP_Error(
				'itsec.site-scanner.vulnerabilities.db-error',
				__( 'Could not fetch vulnerabilities.', 'better-wp-security' ),
				[
					'error' => $this->wpdb->last_error,
				]
			) );
		}

		return Result::success( array_filter( array_map( [ $this, 'try_hydrate' ], $results ) ) );
	}

	/**
	 * Count vulnerabilities from the DB.
	 *
	 * @param Vulnerabilities_Options $options
	 *
	 * @return Result<int>
	 */
	public function count_vulnerabilities( Vulnerabilities_Options $options ): Result {

		$sql = "SELECT count(*) as c FROM {$this->wpdb->base_prefix}itsec_vulnerabilities";

		[ $where, $prepare ] = $this->build_where_clause( $options );
		$sql .= $where;

		if ( $prepare ) {
			$prepared = $this->wpdb->prepare( $sql, $prepare );
		} else {
			$prepared = $sql;
		}

		$count = $this->wpdb->get_var( $prepared );

		if ( $this->wpdb->last_error ) {
			return Result::error( new \WP_Error(
				'itsec.site-scanner.vulnerabilities.db-error',
				__( 'Could not fetch vulnerabilities.', 'better-wp-security' ),
				[
					'error' => $this->wpdb->last_error,
				]
			) );
		}

		return Result::success( $count );
	}

	private function build_where_clause( Vulnerabilities_Options $options ): array {
		$wheres  = [];
		$prepare = [];

		if ( $types = $options->get_types() ) {
			$wheres[] = sprintf(
				'`software_type` IN (%s)',
				implode( ', ', array_fill( 0, count( $types ), '%s' ) )
			);
			$prepare  = array_merge( $prepare, $types );
		}

		if ( $resolutions = $options->get_resolutions() ) {
			$wheres[] = sprintf(
				'`resolution` IN (%s)',
				implode( ', ', array_fill( 0, count( $resolutions ), '%s' ) )
			);
			$prepare  = array_merge( $prepare, $resolutions );
		}

		if ( $software = $options->get_software() ) {
			$wheres[] = sprintf(
				'(%s)',
				implode( ' OR ', array_map( function ( array $software ) use ( &$prepare ) {
					$prepare[] = $software['type'];

					if ( $software['slug'] ) {
						$prepare[] = $software['slug'];

						return '(`software_type` = %s AND `software_slug` = %s)';
					}

					return '(`software_type` = %s)';
				}, $software ) )
			);
		}

		if ( $first_seen_after = $options->get_first_seen_after() ) {
			$wheres[]  = '`first_seen` > %s';
			$prepare[] = $first_seen_after->format( 'Y-m-d H:i:s' );
		}

		if ( $first_seen_before = $options->get_first_seen_before() ) {
			$wheres[]  = '`first_seen` < %s';
			$prepare[] = $first_seen_before->format( 'Y-m-d H:i:s' );
		}

		if ( $last_seen_after = $options->get_last_seen_after() ) {
			$wheres[]  = '`last_seen` > %s';
			$prepare[] = $last_seen_after->format( 'Y-m-d H:i:s' );
		}

		if ( $last_seen_before = $options->get_last_seen_before() ) {
			$wheres[]  = '`last_seen` < %s';
			$prepare[] = $last_seen_before->format( 'Y-m-d H:i:s' );
		}

		if ( ! $wheres ) {
			return [ '', [] ];
		}

		return [ ' WHERE ' . implode( ' AND ', $wheres ), $prepare ];
	}

	private function try_hydrate( array $data ): ?Vulnerability {
		try {
			return $this->hydrate( $data );
		} catch ( \Exception $e ) {
			return null;
		}
	}

	private function hydrate( array $data ): Vulnerability {
		return new Vulnerability(
			$data['id'],
			$data['software_type'],
			$data['software_slug'],
			json_decode( $data['details'], true ),
			new \DateTimeImmutable( $data['first_seen'], new \DateTimeZone( 'UTC' ) ),
			new \DateTimeImmutable( $data['last_seen'], new \DateTimeZone( 'UTC' ) ),
			$data['resolved_at']
				? new \DateTimeImmutable( $data['resolved_at'], new \DateTimeZone( 'UTC' ) )
				: null,
			$data['resolved_by']
				? ( get_userdata( $data['resolved_by'] ) ?: null )
				: null,
			$data['resolution']
		);
	}
}
