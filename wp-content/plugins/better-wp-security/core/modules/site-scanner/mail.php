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
	public static function send( $scan ) {
		if ( ! $scan instanceof Scan ) {
			_doing_it_wrong( __METHOD__, __( 'Must pass Scan instance.', 'better-wp-security' ), '5.8.1' );

			return false;
		}

		$code = $scan->get_code();

		if ( 'clean' === $code ) {
			return true;
		}

		$nc = ITSEC_Core::get_notification_center();

		$mail = $nc->mail();
		$mail->set_subject( static::get_scan_subject( $code ) );
		$mail->set_recipients( $nc->get_recipients( 'malware-scheduling' ) );

		$mail->add_header(
			esc_html__( 'Site Scan', 'better-wp-security' ),
			sprintf(
				esc_html__( 'Site Scan for %s', 'better-wp-security' ),
				'<b>' . ITSEC_Lib::date_format_i18n_and_local_timezone( $scan->get_time()->getTimestamp(), get_option( 'date_format' ) ) . '</b>'
			)
		);
		static::format_scan_body( $mail, $scan );
		$mail->add_footer();

		return $nc->send( 'malware-scheduling', $mail );
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

				return wp_sprintf( esc_html__( 'Scheduled site scan found warnings', 'better-wp-security' ) );
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
			$log_url = add_query_arg( 'id', $scan->get_id(), ITSEC_Core::get_logs_page_url() );
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

		$mail->add_text( $lead );

		$mail->start_group( 'report' );

		foreach ( $scan->get_entries() as $entry ) {
			if ( $entry->get_status() !== Status::WARN ) {
				continue;
			}

			$mail->add_section_heading( $entry->get_title() );
			$mail->add_list( array_map( static function ( Issue $issue ) {
				return sprintf( '<a href="%s">%s</a>', esc_url( $issue->get_link() ), esc_html( $issue->get_description() ) );
			}, $entry->get_issues() ) );
		}

		if ( $errors ) {
			$mail->add_section_heading( esc_html__( 'Scan Errors', 'better-wp-security' ) );
			$mail->add_list( array_map( 'esc_html', wp_list_pluck( $scan->get_errors(), 'message' ) ) );
		}

		$mail->end_group();

		if ( $log_url ) {
			$mail->add_button( esc_html__( 'View Report', 'better-wp-security' ), $log_url );
		}
	}
}
