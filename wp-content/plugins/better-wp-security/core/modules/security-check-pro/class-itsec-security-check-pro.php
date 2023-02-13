<?php

final class ITSEC_Security_Check_Pro {
	public function __construct() { }

	public function run() {
		if ( defined( 'ITSEC_DISABLE_SECURITY_CHECK_PRO' ) && ITSEC_DISABLE_SECURITY_CHECK_PRO ) {
			return;
		}

		if ( isset( $_POST['itsec-security-check'] ) ) {
			require_once( dirname( __FILE__ ) . '/utility.php' );

			ITSEC_Security_Check_Pro_Utility::handle_scan_request();
		}

		add_action( 'itsec-security-check-before-default-checks', array( $this, 'run_scan' ) );
		add_action( 'itsec-security-check-enable-ssl', array( $this, 'handle_enable_ssl' ) );

		add_filter( 'itsec-ssl-support-probability', array( $this, 'filter_ssl_support_probability' ) );

		if ( ! defined( 'ITSEC_DISABLE_AUTOMATIC_REMOTE_IP_DETECTION' ) || ! ITSEC_DISABLE_AUTOMATIC_REMOTE_IP_DETECTION ) {
			add_filter( 'itsec_proxy_types', array( $this, 'add_security_check_proxy_type' ) );
			add_filter( 'itsec_build_ip_detector_for_security-check', array( $this, 'build_detector' ), 10, 2 );
			add_action( 'itsec_scheduled_health-check', array( $this, 'health_check' ) );
		}
	}

	public function run_scan( $feedback ) {
		require_once( dirname( __FILE__ ) . '/utility.php' );

		ITSEC_Security_Check_Pro_Utility::run_scan( $feedback );
	}

	public function handle_enable_ssl( $data ) {
		require_once( dirname( __FILE__ ) . '/utility.php' );

		ITSEC_Security_Check_Pro_Utility::handle_enable_ssl( $data );
	}

	public function filter_ssl_support_probability( $probability ) {
		if ( ITSEC_Modules::get_setting( 'security-check-pro', 'ssl_supported' ) ) {
			$probability = 100;
		}

		return $probability;
	}

	public function add_security_check_proxy_type( $proxy_types ) {
		return ITSEC_Lib::array_insert_before( 'automatic', $proxy_types, 'security-check', esc_html__( 'Security Check Scan', 'better-wp-security' ) );
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

	public function health_check() {
		ITSEC_Modules::load_module_file( 'feedback.php', 'security-check' );
		require_once( __DIR__ . '/utility.php' );
		ITSEC_Security_Check_Pro_Utility::get_server_response();
	}
}
