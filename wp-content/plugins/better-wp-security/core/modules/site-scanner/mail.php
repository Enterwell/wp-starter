<?php

use iThemesSecurity\Site_Scanner\Scan;
use iThemesSecurity\Site_Scanner\Status;
use iThemesSecurity\Site_Scanner\Issue;

class ITSEC_Site_Scanner_Mail {

	/**
	 * Sends a notification about the results of the scan.
	 *
	 * @param Scan $scan
	 *
	 * @return bool
	 */
	public static function send( Scan $scan ) {
		$nc   = ITSEC_Core::get_notification_center();
		$mail = static::get_mail( $scan );

		if ( ! $mail ) {
			return true;
		}

		return $nc->send( 'malware-scheduling', $mail );
	}

	/**
	 * Gets the configured Mail template for a Scan.
	 *
	 * @param Scan $scan
	 *
	 * @return ITSEC_Mail|null
	 */
	public static function get_mail( Scan $scan ) {
		$code = $scan->get_code();

		if ( 'clean' === $code ) {
			return null;
		}

		$nc = ITSEC_Core::get_notification_center();

		$mail = $nc->mail();
		$mail->set_subject( static::get_scan_subject( $code ) );
		$mail->set_recipients( $nc->get_recipients( 'malware-scheduling' ) );

		$issues = $scan->count( Status::WARN );
		$errors = count( $scan->get_errors() );
		$lead = '';

		if ( $issues ) {
			$lead = sprintf( esc_html(
				_n(
					'The scheduled site scan found %1$s issue when scanning %2$s.',
					'The scheduled site scan found %1$s issues when scanning %2$s.',
					$issues,
					'better-wp-security'
				)
			), number_format_i18n( $issues ), $scan->get_url() );
		}

		if ( $errors ) {
			if ( $lead ) {
				$lead .= ' ' . sprintf( esc_html(
						_n(
							'The scanner encountered %s additional error.',
							'The scanner encountered %s additional errors.',
							$errors,
							'better-wp-security'
						)
					), number_format_i18n( $errors ) );
			} else {
				$lead = sprintf( esc_html(
					_n(
						'The scheduled site scan encountered %1$s error when scanning %2$s.',
						'The scheduled site scan encountered %1$s errors when scanning %2$s.',
						$errors,
						'better-wp-security'
					)
				), number_format_i18n( $errors ), $scan->get_url() );
			}
		}

		$mail->add_header(
			esc_html__( 'Site Scan', 'better-wp-security' ),
			sprintf(
				esc_html__( 'Site Scan for %s', 'better-wp-security' ),
				'<b>' . ITSEC_Lib::date_format_i18n_and_local_timezone( $scan->get_time()->getTimestamp(), get_option( 'date_format' ) ) . '</b>'
			),
			false,
			$lead,
		);
		static::format_scan_body( $mail, $scan );
		$mail->add_footer( false );

		return $mail;
	}

	/**
	 * Get the subject line for a site scan result.
	 *
	 * @param string $code
	 *
	 * @return string
	 */
	public static function get_scan_subject( $code ) {

		switch ( $code ) {
			case 'scan-failure-server-error':
			case 'scan-failure-client-error':
			case 'error':
				return esc_html__( 'Scheduled site scan resulted in an error', 'better-wp-security' );
			case 'clean':
				return esc_html__( 'Scheduled site scan found no issues.', 'better-wp-security' );
			default:
				require_once( dirname( __FILE__ ) . '/util.php' );

				if ( $codes = ITSEC_Site_Scanner_Util::translate_findings_code( $code ) ) {
					return wp_sprintf( esc_html__( 'Scheduled site scan report: %l', 'better-wp-security' ), $codes );
				}

				return esc_html__( 'Scheduled site scan found warnings', 'better-wp-security' );
		}
	}

	/**
	 * Format the scan results into the mail object.
	 *
	 * @param ITSEC_Mail $mail
	 * @param Scan       $scan
	 */
	public static function format_scan_body( ITSEC_Mail $mail, $scan ) {
		$log_url = '';

		if ( $scan->get_id() ) {
			$log_url = ITSEC_Core::get_logs_page_url( [ 'id' => $scan->get_id() ] );
			$log_url = ITSEC_Mail::filter_admin_page_url( $log_url );
		}

		if ( $scan->is_error() ) {
			$mail->add_list( array(
				/* translators: 1. Site name. */
				sprintf( esc_html__( 'An error occurred while running the scheduled site scan on %s:', 'better-wp-security' ), get_bloginfo( 'name', 'display' ) ),
				sprintf( esc_html__( 'Error Message: %s', 'better-wp-security' ), $scan->get_error()->get_error_message() ),
				sprintf( esc_html__( 'Error Code: %s', 'better-wp-security' ), '<code>' . esc_html( $scan->get_error()->get_error_code() ) . '</code>' ),
			), true );

			if ( $log_url ) {
				$mail->add_button( esc_html__( 'View Report', 'better-wp-security' ), $log_url );
			}

			return;
		}

		$mail->start_group( 'report' );

		foreach ( $scan->get_entries() as $entry ) {
			if ( $entry->get_status() !== Status::WARN ) {
				continue;
			}

			$mail->add_list( array_reduce( $entry->get_issues(), static function ( array $list, Issue $issue ) {
				if ( $issue->get_status() !== Status::WARN ) {
					return $list;
				}

				if ( $issue instanceof \iThemesSecurity\Site_Scanner\Vulnerability_Issue ) {
					$item = esc_html( $issue->get_description() ) . '<br>';
					$item .= '<span style="margin-left: 12px; font-size: 14px;">';
					$item .= sprintf( '<a href="%s">%s</a>', esc_url( ITSEC_Mail::filter_admin_page_url( $issue->get_link() ) ), esc_attr__( 'Manage Vulnerability', 'better-wp-security' ) );

					$patchstack = $issue->get_meta()['issue']['references'][0]['refs'][0]['link'] ?? '';

					if ( $patchstack ) {
						$item .= sprintf( ' | <a href="%s">%s</a>', esc_url( $patchstack ), esc_attr__( 'View in Patchstack', 'better-wp-security' ) );
					}
					$item .= '</span>';
				} else {
					$item = sprintf( '<a href="%s">%s</a>', esc_url( $issue->get_link() ), esc_html( $issue->get_description() ) );
				}

				$list[] = $item;

				return $list;
			}, [] ), false, true, $entry->get_title() );
		}

		$errors = count( $scan->get_errors() );

		if ( $errors ) {
			$mail->add_section_heading( esc_html__( 'Scan Errors', 'better-wp-security' ) );
			$mail->add_list( array_map( 'esc_html', wp_list_pluck( $scan->get_errors(), 'message' ) ) );
		}

		$mail->end_group();

		if ( $log_url ) {
			$mail->add_button( esc_html__( 'View Report', 'better-wp-security' ), $log_url );
		}

		$mail->add_divider();
		$vulnerabilities = $scan->find_entry( 'vulnerabilities' );

		if ( $vulnerabilities && $vulnerabilities->count() ) {
			$mail->add_large_text( esc_html__( 'What Actions Should I Take?', 'better-wp-security' ) );
			$mail->add_text(
				esc_html__( 'Vulnerable WordPress plugins and themes are the #1 reason WordPress sites get hacked.', 'better-wp-security' ) .
				' <b>' . esc_html__( 'Either quickly update the vulnerable theme, plugin or WordPress version immediately to the newest version or immediately deactivate and delete the plugin or theme from your WordPress installation until a fix is available.', 'better-wp-security' ) . '</b>',
				'dark'
			);

			if ( $log_url ) {
				$mail->add_section_heading( esc_html__( 'How to View the Report & See Available Updates', 'better-wp-security' ) );
				$mail->add_123_box(
					sprintf(
						esc_html__( '%1$sView the Site Scan Report%2$s available now from your WordPress admin dashboard.', 'better-wp-security' ),
						'<a href="' . esc_url( $log_url ) . '">',
						'</a>'
					),
					esc_html__( 'In the Known Vulnerabilities section of the report, click “Show Details.” If a security fix is available, the report will indicate the latest version number.', 'better-wp-security' ),
					esc_html__( 'If a security fix is available, update the vulnerable plugin or theme as soon as possible from Your WordPress admin dashboard > Updates page.', 'better-wp-security' ) .
					' <a href="' . esc_url( ITSEC_Mail::filter_admin_page_url( admin_url( 'update-core.php' ) ) ) . '">' . esc_html__( 'Log in now to update.', 'better-wp-security' ) . '</a>'
				);
			}
		}

		if ( ! ITSEC_Core::is_pro() ) {
			$mail->add_site_scanner_pro_callout();
		}
	}
}
