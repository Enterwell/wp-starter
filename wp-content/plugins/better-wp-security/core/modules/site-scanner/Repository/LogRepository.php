<?php

namespace iThemesSecurity\Site_Scanner\Repository;

use iThemesSecurity\Site_Scanner\Factory;
use iThemesSecurity\Site_Scanner\Scan;

final class LogRepository implements Repository {

	/** @var Factory */
	private $factory;

	/**
	 * LogRepository constructor.
	 *
	 * @param Factory $factory
	 */
	public function __construct( Factory $factory ) { $this->factory = $factory; }

	public function get_scans( Options $options ) {
		$filters = $this->get_filters( $options );

		$page     = $options->get_page();
		$per_page = $options->get_per_page();

		$items = \ITSEC_Log::get_entries( $filters, $per_page, $page, [ 'timestamp', 'id' ], 'DESC', 'all' );
		$scans = [];

		foreach ( $items as $item ) {
			$scan = $this->factory->for_log_item( $item );

			if ( ! is_wp_error( $scan ) ) {
				$scans[] = $scan;
			}
		}

		return $scans;
	}

	public function count_scans( Options $options ) {
		return \ITSEC_Log::get_number_of_entries( $this->get_filters( $options ) );
	}

	public function get_scan( $id ) {
		return $this->factory->for_log_id( $id );
	}

	/**
	 * Get the filters to query the logs.
	 *
	 * @param Options $options
	 *
	 * @return array
	 */
	protected function get_filters( Options $options ) {
		$filters = [
			'module'   => 'site-scanner',
			'type_not' => [ 'process-start', 'process-update', 'process-stop' ],
		];

		if ( $options->get_code() ) {
			$filters['code'] = $options->get_code();
		}

		if ( $options->get_after() ) {
			$filters['__min_timestamp'] = $options->get_after()->getTimestamp();
		}

		if ( $options->get_before() ) {
			$filters['__max_timestamp'] = $options->get_before()->getTimestamp();
		}

		return $filters;
	}
}
