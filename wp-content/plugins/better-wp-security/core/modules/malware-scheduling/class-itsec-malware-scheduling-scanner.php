<?php

use iThemesSecurity\Site_Scanner\Scan;

class ITSEC_Malware_Scheduling_Scanner {

	/**
	 * Perform the scheduled scan and send an email summarizing the results.
	 *
	 * @param int $site_id
	 *
	 * @return Scan
	 */
	public static function scan( $site_id = 0 ) {
		$scan = ITSEC_Site_Scanner_API::scan( $site_id );

		if ( $scan->is_error() ) {
			if ( $scan->get_error()->get_error_message( 'itsec-temporary-server-error' ) ) {
				return $scan;
			}

			if ( $scan->get_error()->get_error_message( 'rate_limit_exceeded' ) ) {
				return $scan;
			}
		}

		if ( ITSEC_Lib_Remote_Messages::has_action( 'malware-scanner-disable-malware-warnings' ) ) {
			return $scan;
		}

		self::send_email( $scan );

		return $scan;
	}

	/**
	 * Get the next site ID to scan based on the current site ID.
	 *
	 * @param int $site_id
	 *
	 * @return int|null
	 */
	public static function get_next_site_id( $site_id ) {

		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare( "SELECT `blog_id` FROM {$wpdb->blogs} WHERE `public` = 1 AND `archived` = 0 AND `deleted` = 0 AND `blog_id` > %d ORDER BY `blog_id` ASC LIMIT 1", $site_id ) );

		if ( null === $id ) {
			return null;
		}

		if ( is_main_site( $id ) ) {
			return static::get_next_site_id( $id );
		}

		return (int) $id;
	}

	/**
	 * Sends an email with the scan results.
	 *
	 * @param Scan $scan
	 */
	public static function send_email( $scan ) {

		$nc = ITSEC_Core::get_notification_center();

		if ( ! $nc->is_notification_enabled( 'malware-scheduling' ) ) {
			return;
		}

		ITSEC_Site_Scanner_Mail::send( $scan );
	}
}
