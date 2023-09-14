<?php

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

/**
 * Handle the loopback callback test.
 */
function itsec_security_check_loopback_callback() {
	if ( ! isset( $_POST['hash'], $_POST['exp'] ) ) {
		wp_die();
	}

	$hash = $_POST['hash'];
	$exp  = $_POST['exp'];

	$expected = hash_hmac( 'sha1', "itsec-check-loopback|{$exp}", wp_salt() );

	if ( ! hash_equals( $hash, $expected ) ) {
		wp_die();
	}

	if ( $exp < ITSEC_Core::get_current_time_gmt() ) {
		wp_die();
	}

	echo ITSEC_Lib::get_ip();
	die;
}

add_action( 'admin_post_nopriv_itsec-check-loopback', 'itsec_security_check_loopback_callback' );

use iThemesSecurity\Lib\Tools\Config_Tool;
use iThemesSecurity\Lib\Tools\Tools_Registry;
use \iThemesSecurity\Lib\Result;

add_action( 'itsec_register_tools', function ( Tools_Registry $registry ) {
	$registry->register( new class( 'identify-server-ips', ITSEC_Modules::get_config( 'global' ) ) extends Config_Tool {
		public function run( array $form = [] ): Result {
			$dns      = $this->check_server_ips();
			$loopback = $this->do_loopback();

			if ( is_wp_error( $dns ) && is_wp_error( $loopback ) ) {
				$error = new WP_Error(
					'itsec.tool.identify-server-ips.failed',
					__( 'Could not identify server IPs', 'better-wp-security' )
				);
				$error->merge_from( $dns );
				$error->merge_from( $loopback );

				return Result::error( $error );
			}

			$server_ips = ITSEC_Modules::get_setting( 'global', 'server_ips' );

			if ( is_array( $dns ) ) {
				$server_ips = array_merge( $server_ips, $dns );
			}

			if ( is_string( $loopback ) ) {
				$server_ips[] = $loopback;
			}

			$server_ips = array_unique( $server_ips );

			ITSEC_Modules::set_setting( 'global', 'server_ips', $server_ips );

			if ( ! $server_ips ) {
				return Result::error( new WP_Error(
					'itsec.tool.identify-server-ips.no-ips',
					__( 'No server IPs found.', 'better-wp-security' )
				) );
			}

			$result = Result::success( $server_ips );
			$result->add_success_message( wp_sprintf( __( 'Identified server IPs: %l.', 'better-wp-security' ), array_map( static function ( $ip ) {
				return sprintf( '“%s”', $ip );
			}, $server_ips ) ) );

			if ( is_wp_error( $dns ) ) {
				$result->add_warning_message( ...$dns->get_error_messages() );
			}

			if ( is_wp_error( $loopback ) ) {
				$result->add_warning_message( ...$loopback->get_error_messages() );
			}

			return $result;
		}

		/**
		 * Checks server IPs using DNS records.
		 *
		 * @return array|WP_Error
		 */
		private function check_server_ips() {
			$response = dns_get_record( parse_url( site_url(), PHP_URL_HOST ), DNS_A + ( defined( 'DNS_AAAA' ) ? DNS_AAAA : 0 ) );

			if ( ! $response ) {
				return new WP_Error( 'itsec.tool.identify-server-ips.no-dns', __( 'No DNS records found.', 'better-wp-security' ) );
			}

			$ips = [];

			foreach ( $response as $record ) {
				if ( isset( $record['ipv6'] ) ) {
					$ips[] = $record['ipv6'];
				}

				if ( isset( $record['ip'] ) ) {
					$ips[] = $record['ip'];
				}
			}

			return $ips;
		}

		/**
		 * Checks server IPs by making a loopback request.
		 *
		 * @return string|WP_Error
		 */
		private function do_loopback() {
			$exp    = ITSEC_Core::get_current_time_gmt() + 60;
			$action = 'itsec-check-loopback';
			$hash   = hash_hmac( 'sha1', "{$action}|{$exp}", wp_salt() );

			$response = wp_remote_post( admin_url( 'admin-post.php' ), [
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
				'body'      => compact( 'action', 'hash', 'exp' ),
			] );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$ip = trim( wp_remote_retrieve_body( $response ) );

			if ( ! ITSEC_Lib_IP_Tools::validate( $ip ) ) {
				return new WP_Error(
					'itsec.tool.identify-server-ips.invalid-ip',
					sprintf( __( 'Invalid IP returned: %s', 'better-wp-security' ), esc_attr( $ip ) )
				);
			}

			return $ip;
		}
	} );
} );
