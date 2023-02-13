<?php

final class ITSEC_File_Change_Admin {

	public static function enqueue_scanner() {
		$logs_page_url = ITSEC_Core::get_logs_page_url( 'file_change' );

		ITSEC_Lib::enqueue_util();
		wp_enqueue_script( 'itsec-file-change-scanner', plugins_url( 'js/file-scanner.js', __FILE__ ), array( 'jquery', 'heartbeat', 'itsec-util' ), ITSEC_Core::get_plugin_build(), true );
		wp_localize_script( 'itsec-file-change-scanner', 'ITSECFileChangeScannerl10n', array(
			'button_text'          => __( 'Scan Files Now', 'better-wp-security' ),
			'scanning_button_text' => __( 'Scanning...', 'better-wp-security' ),
			'no_changes'           => __( 'No changes were detected.', 'better-wp-security' ),
			'found_changes'        => sprintf( __( 'Changes were detected. Please check the <a href="%s" target="_blank" rel="noopener noreferrer">logs</a> for details.', 'better-wp-security' ), esc_url( add_query_arg( 'id', '#REPLACE_ID#', $logs_page_url ) ) ),
			'unknown_error'        => __( 'An unknown error occured. Please try again later', 'better-wp-security' ),
			'already_running'      => sprintf( __( 'A scan is already in progress. Please check the <a href="%s" target="_blank" rel="noopener noreferrer">logs page</a> at a later time for the results of the scan.', 'better-wp-security' ), esc_url( $logs_page_url ) ),
		) );
	}
}

new ITSEC_File_Change_Admin();
