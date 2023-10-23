<?php

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Site_Scanner\Repository\Scans_Repository;
use iThemesSecurity\Site_Scanner\Repository\Vulnerabilities_Options;
use iThemesSecurity\Site_Scanner\Repository\Vulnerabilities_Repository;
use iThemesSecurity\Site_Scanner\Scan;
use iThemesSecurity\Site_Scanner\Vulnerability;
use iThemesSecurity\Site_Scanner\Vulnerability_Issue;

class ITSEC_Site_Scanner implements Runnable {

	/** @var Scans_Repository */
	private $repository;

	/** @var Vulnerabilities_Repository */
	private $vulnerabilities;

	public function __construct( Scans_Repository $repository, Vulnerabilities_Repository $vulnerabilities ) {
		$this->repository      = $repository;
		$this->vulnerabilities = $vulnerabilities;
	}

	public function run() {
		add_action( 'itsec_register_highlighted_logs', [ $this, 'register_highlight' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );
		add_action( 'itsec_site_scanner_scan_complete', [ $this, 'extract_vulnerabilities_from_scan' ], 10, 3 );
		add_action( 'activated_plugin', [ $this, 'unresolve_activated_plugin' ] );
		add_action( 'deactivated_plugin', [ $this, 'resolve_deactivated_plugin' ] );
		add_action( 'deleted_plugin', [ $this, 'resolve_deleted_plugin' ], 10, 2 );
		add_action( 'switch_theme', [ $this, 'resolve_switched_theme' ], 10, 3 );
		add_action( 'deleted_theme', [ $this, 'resolve_deleted_theme' ], 10, 2 );

		if ( $this->repository instanceof Runnable ) {
			$this->repository->run();
		}
	}

	public function register_highlight() {
		ITSEC_Lib_Highlighted_Logs::register_dynamic_highlight( 'site-scanner-report', [
			'module' => 'site-scanner',
			'type'   => 'critical-issue',
		] );
	}

	/**
	 * Registers scripts for the site scanner.
	 */
	public function register_scripts() {
		wp_register_script( 'itsec-site-scanner-scan-settings', plugins_url( 'js/scanner.js', __FILE__ ), [ 'jquery', 'wp-i18n', 'itsec-util' ], 1, true );
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

		$vulnerabilities   = [];
		$vulnerability_ids = [];

		if ( ! $entry = $scan->find_entry( 'vulnerabilities' ) ) {
			return;
		}

		foreach ( $entry->get_issues() as $issue ) {
			if ( ! $issue instanceof Vulnerability_Issue ) {
				continue;
			}

			$vulnerability_ids[] = $issue->get_id();
			$found_vulnerability = $this->vulnerabilities->for_issue( $issue );

			if ( $found_vulnerability->is_success() ) {
				$model = $found_vulnerability->get_data();
				$model->seen();

				if ( ! $model->is_muted() ) {
					if ( ! $model->is_software_active() ) {
						$model->deactivated();
					} elseif ( ! $model->is_patched() ) {
						$model->unresolve();
					}
				}

				/**
				 * Fires when a vulnerability has been seen.
				 *
				 * @param Vulnerability       $model
				 * @param Vulnerability_Issue $issue
				 */
				do_action( 'itsec_vulnerability_was_seen', $model, $issue );

				$this->vulnerabilities->persist( $model );
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

		$unresolved_vulnerabilities = $this->vulnerabilities->get_vulnerabilities(
			( new Vulnerabilities_Options() )->set_resolutions( [
				'',
				Vulnerability::R_DEACTIVATED,
				Vulnerability::R_MUTED,
				Vulnerability::R_PATCHED,
			] )
		);

		if ( $unresolved_vulnerabilities->is_success() ) {
			foreach ( $unresolved_vulnerabilities->get_data() as $vulnerability ) {
				if ( ! $vulnerability->is_software_installed() ) {
					if ( ! $vulnerability->is_muted() ) {
						$vulnerability->deleted();
					}
				} elseif ( ! in_array( $vulnerability->get_id(), $vulnerability_ids, true ) ) {
					$vulnerability->updated( false );
				}

				/**
				 * Fires if a vulnerability was not seen in the latest Site Scan.
				 *
				 * @param Vulnerability $vulnerability
				 */
				do_action( 'itsec_vulnerability_not_seen', $vulnerability );

				$this->vulnerabilities->persist( $vulnerability );
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
	 * When a plugin is activated, check if there are any vulnerabilities
	 * that were resolved through deactivation that we need to unresolve.
	 *
	 * @param string $plugin The plugin file.
	 *
	 * @return void
	 */
	public function unresolve_activated_plugin( $plugin ) {
		if ( ! $slug = dirname( $plugin ) ) {
			return;
		}

		$vulnerabilities = $this->vulnerabilities->get_vulnerabilities(
			( new Vulnerabilities_Options() )
				->add_software( Vulnerability::T_PLUGIN, $slug )
				->set_resolutions( [ Vulnerability::R_DEACTIVATED, Vulnerability::R_DELETED ] )
		);

		if ( ! $vulnerabilities->is_success() ) {
			return;
		}

		foreach ( $vulnerabilities->get_data() as $vulnerability ) {
			$vulnerability->unresolve();
			$this->vulnerabilities->persist( $vulnerability );
		}
	}

	/**
	 * When a plugin is deactivated, check if there are any vulnerabilities we need to resolve.
	 *
	 * @param string $plugin The plugin file.
	 *
	 * @return void
	 */
	public function resolve_deactivated_plugin( $plugin ) {
		if ( ! $slug = dirname( $plugin ) ) {
			return;
		}

		$vulnerabilities = $this->vulnerabilities->get_vulnerabilities(
			( new Vulnerabilities_Options() )
				->add_software( Vulnerability::T_PLUGIN, $slug )
				->set_resolutions( [ '' ] )
		);

		if ( ! $vulnerabilities->is_success() ) {
			return;
		}

		$by = null;

		if ( is_user_logged_in() && ! wp_doing_cron() ) {
			$by = wp_get_current_user();
		}

		foreach ( $vulnerabilities->get_data() as $vulnerability ) {
			$vulnerability->deactivated( $by );

			do_action( 'itsec_vulnerability_not_seen', $vulnerability );

			$this->vulnerabilities->persist( $vulnerability );
		}
	}

	/**
	 * When a plugin is deleted, check if there are any vulnerabilities we need to resolve.
	 *
	 * @param string $plugin  The plugin file.
	 * @param bool   $deleted If the plugin was deleted.
	 *
	 * @return void
	 */
	public function resolve_deleted_plugin( $plugin, $deleted ) {
		if ( ! $deleted ) {
			return;
		}

		if ( ! $slug = dirname( $plugin ) ) {
			return;
		}

		$vulnerabilities = $this->vulnerabilities->get_vulnerabilities(
			( new Vulnerabilities_Options() )
				->add_software( Vulnerability::T_PLUGIN, $slug )
				->set_resolutions( [ '', Vulnerability::R_PATCHED, Vulnerability::R_DEACTIVATED ] )
		);

		if ( ! $vulnerabilities->is_success() ) {
			return;
		}

		$by = null;

		if ( is_user_logged_in() && ! wp_doing_cron() ) {
			$by = wp_get_current_user();
		}

		foreach ( $vulnerabilities->get_data() as $vulnerability ) {
			$vulnerability->deleted( $by );

			do_action( 'itsec_vulnerability_not_seen', $vulnerability );

			$this->vulnerabilities->persist( $vulnerability );
		}
	}

	/**
	 * When a theme is switched, check if there are any vulnerabilities we need to resolve.
	 *
	 * @param string   $new_name
	 * @param WP_Theme $new_theme
	 * @param WP_Theme $old_theme
	 *
	 * @return void
	 */
	public function resolve_switched_theme( $new_name, $new_theme, $old_theme ) {
		$vulnerabilities = $this->vulnerabilities->get_vulnerabilities(
			( new Vulnerabilities_Options() )
				->add_software( Vulnerability::T_THEME, $old_theme->get_stylesheet() )
				->set_resolutions( [ '' ] )
		);

		if ( ! $vulnerabilities->is_success() ) {
			return;
		}

		$by = null;

		if ( is_user_logged_in() && ! wp_doing_cron() ) {
			$by = wp_get_current_user();
		}

		foreach ( $vulnerabilities->get_data() as $vulnerability ) {
			$vulnerability->deactivated( $by );

			do_action( 'itsec_vulnerability_not_seen', $vulnerability );

			$this->vulnerabilities->persist( $vulnerability );
		}
	}

	/**
	 * When a theme is deleted, check if there are any vulnerabilities we need to resolve.
	 *
	 * @param string $stylesheet The theme being deleted.
	 * @param bool   $deleted    If the theme was deleted.
	 *
	 * @return void
	 */
	public function resolve_deleted_theme( $stylesheet, $deleted ) {
		if ( ! $deleted ) {
			return;
		}

		$vulnerabilities = $this->vulnerabilities->get_vulnerabilities(
			( new Vulnerabilities_Options() )
				->add_software( Vulnerability::T_THEME, $stylesheet )
				->set_resolutions( [ '', Vulnerability::R_PATCHED, Vulnerability::R_DEACTIVATED ] )
		);

		if ( ! $vulnerabilities->is_success() ) {
			return;
		}

		$by = null;

		if ( is_user_logged_in() && ! wp_doing_cron() ) {
			$by = wp_get_current_user();
		}

		foreach ( $vulnerabilities->get_data() as $vulnerability ) {
			$vulnerability->deleted( $by );

			do_action( 'itsec_vulnerability_not_seen', $vulnerability );

			$this->vulnerabilities->persist( $vulnerability );
		}
	}
}
