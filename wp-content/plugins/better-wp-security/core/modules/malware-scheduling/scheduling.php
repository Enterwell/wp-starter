<?php

return static function ( ITSEC_Scheduler $scheduler ) {
	if ( ! is_multisite() ) {
		return;
	}

	if ( $scheduler->is_single_scheduled( 'malware-scan-site', null ) ) {
		return;
	}

	if ( ! $site_id = ITSEC_Malware_Scheduling_Scanner::get_next_site_id( 0 ) ) {
		return;
	}

	$scheduler->register_loop( 'malware-scan-site', ITSEC_Scheduler::S_TWICE_DAILY, 600 );
	$scheduler->schedule_loop( 'malware-scan-site', compact( 'site_id' ), [
		'fire_at' => ITSEC_Core::get_current_time_gmt() + ( 60 * 15 ),
	] );
};
