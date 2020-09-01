<?php

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Site_Scanner\Repository\Repository;
use iThemesSecurity\Site_Scanner\Scan;
use iThemesSecurity\Site_Scanner\Vulnerability;

class ITSEC_Site_Scanner {

	public function run() {
		add_action( 'itsec_register_highlighted_logs', array( $this, 'register_highlight' ) );
		add_action( 'itsec_site_scanner_scan_complete', array( $this, 'extract_vulnerabilities_from_scan' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );

		$repository = ITSEC_Modules::get_container()->get( Repository::class );

		if ( $repository instanceof Runnable ) {
			$repository->run();
		}
	}

	public function register_highlight() {
		ITSEC_Lib_Highlighted_Logs::register_dynamic_highlight( 'site-scanner-report', array(
			'module' => 'site-scanner',
			'type'   => 'critical-issue',
		) );
	}

	/**
	 * Extracts vulnerabilities from a site scan result, and stores them.
	 *
	 * @param Scan $scan
	 * @param int  $site_id
	 * @param bool $cached
	 */
	public function extract_vulnerabilities_from_scan( $scan, $site_id, $cached ) {
		if ( $scan->is_error() ) {
			return;
		}

		if ( $cached ) {
			return;
		}

		if ( $site_id && ! is_main_site( $site_id ) ) {
			return; // Vulnerabilities aren't checked on sub site scans.
		}

		$vulnerabilities = array();

		if ( $entry = $scan->find_entry( 'vulnerabilities' ) ) {
			foreach ( $entry->get_issues() as $issue ) {
				if ( ! $issue instanceof Vulnerability ) {
					continue;
				}

				$vulnerability = $issue->get_meta();

				$vulnerability['issues'] = [
					[
						'title'    => $issue->get_description(),
						'fixed_in' => $issue->get_fixed_in(),
					]
				];
				$vulnerability['link']   = $issue->get_link();

				unset( $vulnerability['issue'] );
				$vulnerabilities[] = $vulnerability;
			}
		}

		$existing = ITSEC_Modules::get_setting( 'site-scanner', 'vulnerabilities' );

		if ( $existing !== $vulnerabilities ) {
			ITSEC_Modules::set_setting( 'site-scanner', 'vulnerabilities', $vulnerabilities );

			/**
			 * Fires when the detected software vulnerabilities have changed.
			 *
			 * @param array $vulnerabilities The new vulnerabilities set.
			 * @param array $existing        The existing vulnerabilities.
			 */
			do_action( 'itsec_software_vulnerabilities_changed', $vulnerabilities, $existing );
		}
	}

	/**
	 * Registers scripts for the site scanner.
	 */
	public function register_scripts() {
		wp_register_script( 'itsec-site-scanner-scan-settings', plugins_url( 'js/scanner.js', __FILE__ ), array( 'jquery', 'wp-i18n', 'itsec-util' ), 1, true );
	}
}
