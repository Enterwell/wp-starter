<?php

class Ithemes_Sync_Verb_ITSEC_Latest_File_Scan extends Ithemes_Sync_Verb {
	public static $name = 'itsec-latest-file-scan';
	public static $description = 'Get the latest results from the file scan.';

	public function run( $arguments ) {
		if ( ! ITSEC_Modules::is_active( 'file-change' ) ) {
			return new WP_Error( 'itsec-sync-verb-file-change-not-active', __( 'File Change module is not active.', 'better-wp-security' ) );
		}

		return ITSEC_File_Change::get_latest_changes();
	}
}
