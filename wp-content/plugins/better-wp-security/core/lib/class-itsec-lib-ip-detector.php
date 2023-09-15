<?php

require_once( __DIR__ . '/class-itsec-ip-detector.php' );

/**
 * Class ITSEC_Lib_IP_Detector
 *
 * @internal Use {@see ITSEC_Lib::get_ip()} instead of this class directly.
 */
class ITSEC_Lib_IP_Detector {

	public static function get_proxy_types() {
		$types = array(
			'automatic' => esc_html__( 'Automatic (Insecure)', 'better-wp-security' ),
			'manual'    => esc_html__( 'Manual', 'better-wp-security' ),
			'disabled'  => esc_html__( 'Disabled', 'better-wp-security' ),
		);

		/**
		 * Filters the list of available proxy types.
		 *
		 * @param string[] $types List of available proxy types.
		 */
		return apply_filters( 'itsec_proxy_types', $types );
	}

	/**
	 * Get a list of the available proxy headers.
	 *
	 * @return string[]
	 */
	public static function get_proxy_headers() {
		return apply_filters( 'itsec_filter_remote_addr_headers', array(
			'HTTP_CF_CONNECTING_IP', // CloudFlare
			'HTTP_X_FORWARDED_FOR',  // Squid and most other forward and reverse proxies
			'HTTP_X_REAL_IP',
			'HTTP_X_CLIENT_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_CLUSTER_CLIENT_IP',
		) );
	}

	/**
	 * Build an IP detector instance from the configured settings.
	 *
	 * @return ITSEC_IP_Detector
	 */
	public static function build() {
		$proxy = ITSEC_Modules::get_setting( 'global', 'proxy' );

		return self::build_for_type( $proxy );
	}

	/**
	 * Build a detector for a given proxy type and args.
	 *
	 * @param string $proxy
	 * @param array  $args
	 *
	 * @return ITSEC_IP_Detector
	 */
	public static function build_for_type( $proxy, array $args = [] ) {
		$detector = new ITSEC_IP_Detector( $_SERVER );

		$headers = self::get_proxy_headers();

		/**
		 * Fires when a new IP detector is used.
		 *
		 * The dynamic portion of the hook name, `$proxy`, refers to the proxy type.
		 *
		 * @param bool              $configured Was the detector configured.
		 * @param ITSEC_IP_Detector $detector   The IP detector.
		 * @param array             $args       Additional args to customize the behavior.
		 */
		$configured = apply_filters( "itsec_build_ip_detector_for_{$proxy}", false, $detector, $args );

		if ( ! $configured ) {
			switch ( $proxy ) {
				case 'disabled':
					break;
				case 'manual':
					$header = empty( $args['header'] ) ? ITSEC_Modules::get_setting( 'global', 'proxy_header' ) : $args['header'];

					if ( in_array( $header, $headers, true ) ) {
						$detector->add_header( $header );
					}
					break;
				case 'automatic':
				default:
					foreach ( $headers as $header ) {
						$detector->add_header( $header );
					}
					break;
			}
		}

		$detector->add_header( 'REMOTE_ADDR' );

		return $detector;
	}
}
