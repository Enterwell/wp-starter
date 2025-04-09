<?php

use iThemesSecurity\Lib\Result;
use iThemesSecurity\Lib\Tools\Config_Tool;
use iThemesSecurity\Lib\Tools\Tools_Registry;

final class ITSEC_Security_Check_Pro {
	public function run() {
		if ( defined( 'ITSEC_DISABLE_SECURITY_CHECK_PRO' ) && ITSEC_DISABLE_SECURITY_CHECK_PRO ) {
			return;
		}

		if ( isset( $_POST['itsec-security-check'] ) ) {
			require_once( dirname( __FILE__ ) . '/utility.php' );

			ITSEC_Security_Check_Pro_Utility::handle_scan_request();
		}

		add_filter( 'itsec-ssl-support-probability', [ $this, 'filter_ssl_support_probability' ] );

		if ( ! defined( 'ITSEC_DISABLE_AUTOMATIC_REMOTE_IP_DETECTION' ) || ! ITSEC_DISABLE_AUTOMATIC_REMOTE_IP_DETECTION ) {
			add_filter( 'itsec_proxy_types', [ $this, 'add_security_check_proxy_type' ] );
			add_filter( 'itsec_build_ip_detector_for_security-check', [ $this, 'build_detector' ], 10, 2 );
		}

		add_action( 'itsec_register_tools', [ $this, 'register_tools' ] );
	}

	public function filter_ssl_support_probability( $probability ) {
		if ( ITSEC_Modules::get_setting( 'security-check-pro', 'ssl_supported' ) ) {
			$probability = 100;
		}

		return $probability;
	}

	public function add_security_check_proxy_type( $proxy_types ) {
		return ITSEC_Lib::array_insert_after( 'automatic', $proxy_types, 'security-check', esc_html__( 'Security Check Scan (Recommended)', 'better-wp-security' ) );
	}

	/**
	 * Add the remote IP index to the detector.
	 *
	 * @param bool              $configured
	 * @param ITSEC_IP_Detector $detector
	 *
	 * @return bool
	 */
	public function build_detector( $configured, ITSEC_IP_Detector $detector ) {
		$ip_header = ITSEC_Modules::get_setting( 'security-check-pro', 'ip_header' );

		if ( $ip_header['name'] ) {
			$detector->add_header( $ip_header['name'], $ip_header['position_from_end'], ITSEC_IP_Detector::FROM_RIGHT );

			return true;
		}

		$index = ITSEC_Modules::get_setting( 'security-check-pro', 'remote_ip_index' );

		if ( ! $index ) {
			return $configured;
		}

		if ( is_string( $index ) ) {
			$detector->add_header( $index );
		} elseif ( is_array( $index ) && 2 === count( $index ) ) {
			$detector->add_header( $index[0], (int) $index[1] );
		}

		return true;
	}

	/**
	 * Registers tools.
	 *
	 * @param Tools_Registry $registry
	 */
	public function register_tools( Tools_Registry $registry ) {
		$registry->register( new class( 'security-check-pro', ITSEC_Modules::get_config( 'security-check-pro' ) ) extends Config_Tool {
			public function run( array $form = [] ): Result {
				$response = ITSEC_Security_Check_Pro_Utility::get_server_response();

				if ( is_wp_error( $response ) ) {
					return Result::error( $response );
				}

				$result = Result::success();

				if ( ! empty( $response['remote_ip'] ) ) {
					$result->add_success_message( __( 'Identified remote IP entry to protect against IP spoofing.', 'better-wp-security' ) );
				}

				if ( ! empty( $response['ssl_supported'] ) ) {
					$result->add_success_message( __( 'Your site supports SSL.', 'better-wp-security' ) );

					if ( ITSEC_Modules::is_active( 'ssl' ) ) {
						$result->add_info_message( __( 'Requests for http pages are redirected to https as recommended.', 'better-wp-security' ) );
					} else {
						$result->add_info_message( __( 'Redirecting all http page requests to https is highly recommended as it protects login details from being stolen when using public WiFi or insecure networks.', 'better-wp-security' ) );
					}
				} else {
					$result->add_warning_message( __( 'Your site does not support SSL.', 'better-wp-security' ) );
				}

				return $result;
			}
		} );
	}
}
