<?php

namespace iThemesSecurity\Site_Scanner\Repository;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Site_Scanner\Scan;

final class LatestScanRepository implements Repository, Runnable {

	/** @var Scan */
	private $scan;

	public function run() {
		add_action( 'itsec_site_scanner_scan_complete', function ( Scan $scan ) {
			$this->scan = $scan;
		} );
	}

	public function get_scans( Options $options ) {
		if ( ! $this->scan ) {
			return [];
		}

		if ( $options->get_code() && $options->get_code() !== $this->scan->get_code() ) {
			return [];
		}

		return [ $this->scan ];
	}

	public function count_scans( Options $options ) {
		return $this->scan ? 1 : 0;
	}

	public function get_scan( $id ) {
		if ( $this->scan ) {
			return $this->scan;
		}

		return new \WP_Error( 'itsec_no_recent_scan', __( 'No recent site scan.', 'better-wp-security' ) );
	}
}
