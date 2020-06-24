<?php

use \iThemesSecurity\User_Groups;

function itsec_global_filter_whitelisted_ips( $whitelisted_ips ) {
	return array_merge( $whitelisted_ips, ITSEC_Modules::get_setting( 'global', 'lockout_white_list', array() ) );
}

add_filter( 'itsec_white_ips', 'itsec_global_filter_whitelisted_ips', 0 );

/**
 * On every page load, check if the cron test has successfully fired in time.
 *
 * If not, update the cron status and turn off using cron.
 */
function itsec_cron_test_fail_safe() {

	if ( defined( 'ITSEC_DISABLE_CRON_TEST' ) && ITSEC_DISABLE_CRON_TEST ) {
		return;
	}

	$time = ITSEC_Modules::get_setting( 'global', 'cron_test_time' );

	if ( ! $time ) {
		if ( ITSEC_Lib::get_lock( 'cron_test_fail_safe' ) ) {
			ITSEC_Lib::schedule_cron_test();
			ITSEC_Lib::release_lock( 'cron_test_fail_safe' );
		}

		return;
	}

	$threshold = HOUR_IN_SECONDS + DAY_IN_SECONDS;

	if ( ITSEC_Core::get_current_time_gmt() <= $time + $threshold + 5 * MINUTE_IN_SECONDS ) {
		return;
	}

	if ( ! ITSEC_Lib::get_lock( 'cron_test_fail_safe' ) ) {
		return;
	}

	$uncached = ITSEC_Lib::get_uncached_option( 'itsec-storage' );
	$time     = $uncached['global']['cron_test_time'];

	if ( ITSEC_Core::get_current_time_gmt() > $time + $threshold + 5 * MINUTE_IN_SECONDS ) {
		if ( ( ! defined( 'ITSEC_USE_CRON' ) || ! ITSEC_USE_CRON ) && ITSEC_Lib::use_cron() ) {
			ITSEC_Modules::set_setting( 'global', 'use_cron', false );
		}

		ITSEC_Modules::set_setting( 'global', 'cron_status', 0 );
	}

	ITSEC_Lib::schedule_cron_test();
	ITSEC_Lib::release_lock( 'cron_test_fail_safe' );
}

add_action( 'init', 'itsec_cron_test_fail_safe' );

/**
 * Callback for testing whether we should suggest the cron scheduler be enabled.
 *
 * @param int $time
 */
function itsec_cron_test_callback( $time ) {

	$threshold = HOUR_IN_SECONDS + DAY_IN_SECONDS;

	if ( empty( $time ) || ITSEC_Core::get_current_time_gmt() > $time + $threshold ) {
		// Disable cron if the user hasn't set the use cron constant to true.
		if ( ( ! defined( 'ITSEC_USE_CRON' ) || ! ITSEC_USE_CRON ) && ITSEC_Lib::use_cron() ) {
			ITSEC_Modules::set_setting( 'global', 'use_cron', false );
		}

		ITSEC_Modules::set_setting( 'global', 'cron_status', 0 );
	} elseif ( ! ITSEC_Lib::use_cron() ) {
		ITSEC_Modules::set_setting( 'global', 'cron_status', 1 );
		ITSEC_Modules::set_setting( 'global', 'use_cron', true );
	} else {
		ITSEC_Modules::set_setting( 'global', 'cron_status', 1 );
	}

	ITSEC_Lib::schedule_cron_test();
}

add_action( 'itsec_cron_test', 'itsec_cron_test_callback' );

/**
 * Record that a user has logged-in.
 *
 * @param string  $username
 * @param WP_User $user
 */
function itsec_record_first_login( $username, $user ) {

	if ( ! get_user_meta( $user->ID, '_itsec_has_logged_in', true ) ) {
		update_user_meta( $user->ID, '_itsec_has_logged_in', ITSEC_Core::get_current_time_gmt() );
	}
}

add_action( 'wp_login', 'itsec_record_first_login', 15, 2 );

/**
 * Basename the 'thumb' for attachments to prevent directory traversal
 * when deleting the main attachment.
 *
 * @param array $data
 *
 * @return array
 */
function itsec_basename_attachment_thumbs( $data ) {

	if ( isset( $data['thumb'] ) && ITSEC_Modules::get_setting( 'wordpress-tweaks', 'patch_thumb_file_traversal' ) ) {
		$data['thumb'] = basename( $data['thumb'] );
	}

	return $data;
}

add_filter( 'wp_update_attachment_metadata', 'itsec_basename_attachment_thumbs' );

function itsec_register_global_user_group_settings( User_Groups\Settings_Registry $registry ) {
	$registry->register( new User_Groups\Settings_Registration( 'global', 'manage_group', User_Groups\Settings_Registration::T_MULTIPLE, static function () {
		return [
			'title'       => __( 'Manage iThemes Security', 'better-wp-security' ),
			'description' => __( 'Allow users in the group to manage iThemes Security.', 'better-wp-security' ),
		];
	} ) );
}

add_action( 'itsec_register_user_group_settings', 'itsec_register_global_user_group_settings', 0 );
