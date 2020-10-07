<?php

namespace iThemesSecurity\Site_Scanner\Repository;

use iThemesSecurity\Site_Scanner\Scan;

interface Repository {

	/**
	 * Get the scans matching the given options.
	 *
	 * @param Options $options
	 *
	 * @return mixed
	 */
	public function get_scans( Options $options );

	/**
	 * Count the number of scans that match the given options.
	 *
	 * @param Options $options
	 *
	 * @return int
	 */
	public function count_scans( Options $options );

	/**
	 * Get a scan by id.
	 *
	 * @param int $id The scan id.
	 *
	 * @return Scan|\WP_Error
	 */
	public function get_scan( $id );
}
