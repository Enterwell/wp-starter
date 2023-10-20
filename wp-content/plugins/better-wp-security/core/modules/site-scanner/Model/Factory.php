<?php

namespace iThemesSecurity\Site_Scanner;

final class Factory {
	const ENTRIES = [ 'vulnerabilities', 'blacklist', 'malware' ];

	/**
	 * Makes a scan result for a log item.
	 *
	 * @param int $log_id The log id.
	 *
	 * @return Scan|\WP_Error
	 */
	public function for_log_id( $log_id ) {
		$log_type = \ITSEC_Modules::get_setting( 'global', 'log_type' );

		if ( 'file' === $log_type ) {
			return new \WP_Error( 'itsec_site_scanner_factory_invalid_log_type', __( 'Logs must be stored in the database to retrieve past scans.', 'better-wp-security' ) );
		}

		$entry = \ITSEC_Log::get_entry( $log_id );

		return $this->for_log_item( $entry );
	}

	/**
	 * Makes a scan result for a log item.
	 *
	 * @param array $log
	 *
	 * @return Scan|\WP_Error
	 */
	public function for_log_item( $log ) {
		$valid = \ITSEC_Site_Scanner_Util::is_log_item_valid_scan( $log );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! isset( $log['data']['results'] ) ) {
			return new \WP_Error( 'itsec_site_scanner_factory_malformed_log_item', __( 'Log item is malformed.', 'better-wp-security' ) );
		}

		$id   = (int) $log['id'];
		$code = $log['code'];
		$time = new \DateTimeImmutable( $log['init_timestamp'], new \DateTimeZone( 'UTC' ) );

		$results = $log['data']['results'];

		if ( is_wp_error( $results ) ) {
			return Scan::as_error( $id, $code, '', $time, $results );
		}

		$url     = isset( $results['url'] ) ? $results['url'] : '';
		$entries = ! empty( $results['entries'] ) ? $this->make_entries( $results['entries'] ) : [];
		$errors  = ! empty( $results['errors'] ) ? $results['errors'] : [];

		return new Scan( $id, $code, $url, $time, $entries, $errors );
	}

	/**
	 * Makes a Scan object from the API response.
	 *
	 * @param array|\WP_Error $response
	 *
	 * @return Scan
	 */
	public function for_api_response( $response ) {
		$id   = 0;
		$code = \ITSEC_Site_Scanner_Util::get_scan_result_code( $response );
		$time = new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );

		if ( is_wp_error( $response ) ) {
			$url = isset( $response->get_error_data()['url'] ) ? $response->get_error_data()['url'] : '';

			return Scan::as_error( $id, $code, $url, $time, $response );
		}

		$url     = isset( $response['url'] ) ? $response['url'] : '';
		$entries = ! empty( $response['entries'] ) ? $this->make_entries( $response['entries'] ) : [];
		$errors  = ! empty( $response['errors'] ) ? $response['errors'] : [];

		return new Scan( $id, $code, $url, $time, $entries, $errors );
	}

	/**
	 * Make a list of entries from the scan data.
	 *
	 * @param array $data
	 *
	 * @return Entry[]
	 */
	protected function make_entries( array $data ) {
		$entries = [];

		foreach ( self::ENTRIES as $slug ) {
			if ( ! isset( $data[ $slug ] ) ) {
				continue;
			}

			$entry = $this->{"make_{$slug}"}( $data[ $slug ] );

			if ( $entry instanceof Entry ) {
				$entries[] = $entry;
			}
		}

		return $entries;
	}

	/**
	 * Make a vulnerability entry from the scan data.
	 *
	 * @param array $data
	 *
	 * @return Entry
	 */
	protected function make_vulnerabilities( $data ) {
		if ( ! $data || ! is_array( $data ) ) {
			return new Entry( 'vulnerabilities', __( 'Known Vulnerabilities', 'better-wp-security' ), Status::CLEAN );
		}

		$issues = [];

		foreach ( $data as $vulnerability ) {
			$link = \ITSEC_Site_Scanner_Util::authenticate_vulnerability_link( $vulnerability['link'] );

			foreach ( $vulnerability['issues'] as $issue ) {
				if ( empty( $issue['id'] ) ) {
					continue;
				}

				$issues[] = new Vulnerability_Issue( $vulnerability, $issue['id'], $link );
			}
		}

		return new Entry( 'vulnerabilities', __( 'Known Vulnerabilities', 'better-wp-security' ), Status::WARN, $issues );
	}

	/**
	 * Make a blacklist entry from the scan data.
	 *
	 * @param array $data
	 *
	 * @return Entry
	 */
	protected function make_blacklist( $data ) {
		$issues = [];
		$status = Status::CLEAN;

		if ( $data && is_array( $data ) ) {
			foreach ( $data as $details ) {
				$issue    = new Blacklist_Issue( $details );
				$issues[] = $issue;

				if ( $issue->get_status() === Status::WARN ) {
					$status = $issue->get_status();
				}
			}
		}

		return new Entry( 'blacklist', __( 'Blocklist', 'better-wp-security' ), $status, $issues );
	}

	/**
	 * Makes a malware entry from the scan data.
	 *
	 * @param array $data
	 *
	 * @return Entry
	 */
	protected function make_malware( $data ) {
		if ( ! $data || ! is_array( $data ) ) {
			return new Entry( 'malware', __( 'Malware', 'better-wp-security' ), Status::CLEAN );
		}

		$issues = [];

		foreach ( $data as $malware ) {
			$issues[] = new Malware_Issue( $malware );
		}

		return new Entry( 'malware', __( 'Malware', 'better-wp-security' ), Status::WARN );
	}
}
