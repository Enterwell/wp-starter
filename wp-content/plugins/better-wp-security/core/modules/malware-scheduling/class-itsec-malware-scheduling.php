<?php

use iThemesSecurity\Site_Scanner\Scan;

class ITSEC_Malware_Scheduling {

	// Approximately 10 hours since first try
	const MAX_TRIES = 8;

	public function run() {
		add_action( 'itsec_scheduled_malware-scan', array( $this, 'run_scan' ) );
		add_action( 'itsec_scheduled_malware-scan-site', array( $this, 'run_sub_site_scan' ) );

		add_filter( 'itsec_notifications', array( $this, 'register_notification' ) );
		add_filter( 'itsec_malware-scheduling_notification_strings', array( $this, 'notification_strings' ) );

		ITSEC_Core::get_scheduler()->register_loop( 'malware-scan-site', ITSEC_Scheduler::S_TWICE_DAILY, 600 );
	}

	/**
	 * Run the malware scan.
	 *
	 * @param ITSEC_Job $job
	 */
	public function run_scan( $job ) {

		require_once( dirname( __FILE__ ) . '/class-itsec-malware-scheduling-scanner.php' );

		$scan = ITSEC_Malware_Scheduling_Scanner::scan();

		if ( $this->retry_if_rate_limited( $job, $scan ) ) {
			return;
		}

		if ( $scan->is_error() && $scan->get_error()->get_error_message( 'itsec-temporary-server-error' ) ) {
			$retry = $job->is_retry();

			if ( $retry > self::MAX_TRIES ) {
				ITSEC_Malware_Scheduling_Scanner::send_email( $scan );
			} else {
				$job->reschedule_in( $this->minutes_until_retry( $retry + 1 ) );
			}
		}
	}

	/**
	 * Run the malware scan for a sub site.
	 *
	 * @param ITSEC_Job $job
	 */
	public function run_sub_site_scan( $job ) {
		$data = $job->get_data();

		if ( empty( $data['site_id'] ) ) {
			return; // Todo maybe reschedule if things get corrupted.
		}

		$scan = ITSEC_Malware_Scheduling_Scanner::scan( $data['site_id'] );

		if ( $this->retry_if_rate_limited( $job, $scan ) ) {
			return;
		}

		if ( $scan->is_error() && $scan->get_error()->get_error_message( 'itsec-temporary-server-error' ) ) {
			$job->reschedule_in( HOUR_IN_SECONDS );

			return;
		}

		$next = ITSEC_Malware_Scheduling_Scanner::get_next_site_id( $data['site_id'] );

		if ( null === $next ) {
			$job->schedule_new_loop( array( 'site_id' => ITSEC_Malware_Scheduling_Scanner::get_next_site_id( 0 ) ) );
		} else {
			$job->schedule_next_in_loop( array( 'site_id' => $next ) );
		}
	}

	/**
	 * Schedule a retry if we have been rate limited.
	 *
	 * @param ITSEC_Job $job
	 * @param Scan      $scan
	 *
	 * @return bool
	 */
	private function retry_if_rate_limited( ITSEC_Job $job, $scan ) {
		if ( ! $scan->is_error() ) {
			return false;
		}

		if ( ! in_array( 'rate_limit_exceeded', $scan->get_error()->get_error_codes(), true ) ) {
			return false;
		}

		$data = $scan->get_error()->get_error_data( 'rate_limit_exceeded' );

		if ( is_array( $data ) && isset( $data['wait'] ) ) {
			$seconds = $data['wait'] + 30;
		} else {
			$seconds = 30 * MINUTE_IN_SECONDS;
		}

		$job->reschedule_in( $seconds );

		return true;
	}

	/**
	 * Determine the number of minutes we should wait before contacting Sucuri.
	 *
	 * Exponential back-off modified to have a minimum of 10 minute difference.
	 *
	 * [ ( 2ˣ-1 ) / 2 ] + 10x
	 *
	 * @param int $try Try count.
	 *
	 * @return int
	 */
	private function minutes_until_retry( $try ) {
		return (int) floor( ( ( pow( 2, $try ) - 1 ) / 2 ) + ( 10 * $try ) );
	}

	/**
	 * Register the Scheduled Malware Scan notification.
	 *
	 * @param array $notifications
	 *
	 * @return array
	 */
	public function register_notification( $notifications ) {
		$notifications['malware-scheduling'] = array(
			'recipient' => ITSEC_Notification_Center::R_USER_LIST,
			'optional'  => true,
			'module'    => 'malware-scheduling',
		);

		return $notifications;
	}

	/**
	 * Register the strings for the Scheduled Malware Scan notification.
	 *
	 * @return array
	 */
	public function notification_strings() {
		return array(
			'label'       => __( 'Site Scan Results', 'better-wp-security' ),
			'description' => sprintf(
				__( 'The %1$sSite Scan Scheduling%2$s module sends an email if it discovers an issue or has repeated difficulty conducting the scan.', 'better-wp-security' ),
				ITSEC_Core::get_link_for_settings_route( ITSEC_Core::get_settings_module_route( 'malware-scheduling' ) ),
				'</a>'
			),
		);
	}
}
