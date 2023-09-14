<?php

final class ITSEC_Security_Check_Pro_Utility {
	private static $api_url = 'https://itsec-ssl-proxy-detect.ithemes.com/';
	private static $config_url = 'https://itsec-ssl-proxy-detect.ithemes.com/config.json';

	/**
	 * Function handler that responds to the SSL Proxy Detect server's Postback request.
	 *
	 * It checks the request is properly authorized and then looks for one of the server's
	 * known IP addresses in $_SERVER. The identified header and SSL support are saved
	 * to the module's settings.
	 *
	 * @return void
	 */
	public static function handle_scan_request() {
		if ( ! isset( $_POST['itsec-security-check'] ) || 'scan' !== $_POST['itsec-security-check'] ) {
			return;
		}

		if ( ! isset( $_POST['site'], $_POST['key'], $_POST['expect'], $_POST['scheme'] ) ) {
			return;
		}

		if ( ! self::validate_key( $_POST['key'] ) ) {
			return;
		}

		if ( isset( $_POST['pid'] ) && is_array( $_POST['pid'] ) ) {
			ITSEC_Log::add_process_update( $_POST['pid'], [
				'post'   => $_POST,
				'server' => ITSEC_Lib::get_server_snapshot(),
			] );
		} else {
			ITSEC_Log::add_debug( 'security-check-pro', 'scan-request', [
				'post'   => $_POST,
				'server' => ITSEC_Lib::get_server_snapshot(),
			] );
		}

		$ip_header = [
			'name'              => '',
			'position_from_end' => - 1,
		];

		if ( ! defined( 'ITSEC_DISABLE_AUTOMATIC_REMOTE_IP_DETECTION' ) || ! ITSEC_DISABLE_AUTOMATIC_REMOTE_IP_DETECTION ) {
			$detected = self::get_remote_ip_index();

			if ( false !== $detected ) {
				if ( is_string( $detected ) ) {
					$ip_header['name'] = $detected;
				} else {
					$ip_header['name']              = $detected[0];
					$ip_header['position_from_end'] = $detected[1];
				}
			}
		}

		if ( 'https' === $_POST['scheme'] && is_ssl() ) {
			$ssl_supported = true;
		} else {
			$ssl_supported = false;
		}

		$settings = ITSEC_Modules::get_settings( 'security-check-pro' );

		$settings['last_scan_timestamp'] = time();
		$settings['ssl_supported']       = $ssl_supported;
		$settings['ip_header']           = $ip_header;
		$settings['remote_ip_index']     = '';

		ITSEC_Modules::set_settings( 'security-check-pro', $settings );
		ITSEC_Storage::save();

		if ( isset( $_POST['pid'] ) && is_array( $_POST['pid'] ) ) {
			ITSEC_Log::add_process_update( $_POST['pid'], [
				'ssl_supported' => $ssl_supported,
				'ip_header'     => $ip_header,
			] );
		}

		header( 'Content-Type: text/plain' );
		echo "<response>{$_POST['expect']}:" . ( empty( $ip_header['name'] ) ? 'false' : 'true' ) . ':' . ( $ssl_supported ? 'true' : 'false' ) . '</response>';
		exit();
	}

	/**
	 * Gets the header containing the remote IP.
	 *
	 * @return string|array Either the header name, if the IP isn't in an indexed position.
	 *                      Otherwise, a tuple of the header name and right-oriented 0-based index.
	 */
	public static function get_remote_ip_index() {
		$remote_ips = self::get_remote_ips();

		if ( is_wp_error( $remote_ips ) ) {
			return false;
		}

		$standard_indexes = array(
			'REMOTE_ADDR',
			'HTTP_X_REAL_IP',
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
		);

		foreach ( $remote_ips as $ip ) {
			foreach ( $standard_indexes as $standard_index ) {
				$index = self::get_index( $ip, $standard_index );

				if ( false !== $index ) {
					return $index;
				}
			}
		}


		foreach ( $remote_ips as $ip ) {
			foreach ( array_keys( $_SERVER ) as $var ) {
				$index = self::get_index( $ip, $var );

				if ( false !== $index ) {
					return $index;
				}
			}
		}


		return false;
	}

	/**
	 * Gets the index that an IP is found at.
	 *
	 * @param string $ip  The IP to locate.
	 * @param string $var The server variable to check.
	 *
	 * @return string|array Either the header name, if the IP isn't in an indexed position.
	 *                      Otherwise, a tuple of the header name and right-oriented 0-based index.
	 */
	public static function get_index( $ip, $var ) {
		if ( ! isset( $_SERVER[ $var ] ) ) {
			return false;
		}

		$header = trim( $_SERVER[ $var ] );

		if ( $header === $ip ) {
			return $var;
		}

		if ( strpos( $header, $ip ) === false ) {
			return false;
		}

		$potential_ips = preg_split( '/[, ]+/', $header );

		foreach ( array_reverse( $potential_ips ) as $index => $potential_ip ) {
			if ( $ip === $potential_ip ) {
				return array( $var, $index );
			}
		}

		if ( preg_match_all( '{(?:for)=(?:"?\[?)([a-z0-9\.:_\-/]*)}i', $header, $matches, PREG_SET_ORDER ) ) {
			foreach ( array_reverse( $matches ) as $index => $match ) {
				if ( $ip === $match[1] ) {
					return array( $var, $index );
				}
			}
		}

		return false;
	}

	/**
	 * Contacts the SSL Proxy Detect server and returns the detected valeus.
	 *
	 * @return array|WP_Error An array with a `remote_ip` entry if IP detection was successful,
	 *                        and a `ssl_supported` entry if SSL is supported.
	 */
	public static function get_server_response() {
		$pid  = ITSEC_Log::add_process_start( 'security-check-pro', 'start-scan' );
		$data = array(
			'site' => get_home_url(),
			'key'  => self::get_key(),
			'pid'  => $pid,
		);

		$remote_post_args = array(
			'timeout' => 60,
			'body'    => $data,
		);

		$response = wp_remote_post( self::$api_url, $remote_post_args );

		if ( is_wp_error( $response ) ) {
			ITSEC_Log::add_process_stop( $pid, $response );

			if ( 'connect() timed out!' === $response->get_error_message() ) {
				return new WP_Error( 'http_request_failed', __( 'The server was unable to be contacted.', 'better-wp-security' ) );
			}

			return $response;
		}

		if ( '' === trim( $response['body'] ) ) {
			ITSEC_Log::add_process_stop( $pid, [
				'status' => wp_remote_retrieve_response_code( $response ),
				'body'   => $response['body'],
			] );

			return new WP_Error( 'itsec-security-check-pro-empty-response', __( 'An error occurred when communicating with the iThemes Security Check server: The server returned a blank response.', 
'better-wp-security' ) );
		}

		$body = json_decode( $response['body'], true );

		ITSEC_Log::add_process_stop( $pid, [
			'status' => wp_remote_retrieve_response_code( $response ),
			'body'   => $body,
		] );

		if ( is_null( $body ) ) {
			return new WP_Error( 'itsec-security-check-pro-non-json-response', __( 'An error occurred when communicating with the iThemes Security Check server: The server did not return JSON data when JSON data was expected.', 'better-wp-security' ) );
		}

		if ( isset( $body['error'], $body['error']['code'], $body['error']['message'] ) ) {
			return new WP_Error( 'itsec-security-check-pro-' . $body['error']['code'], sprintf( __( 'An error occurred when communicating with the iThemes Security Check server: %s (%s)', 'better-wp-security' ), $body['error']['message'], $body['error']['code'] ) );
		}

		if ( empty( $body['complete'] ) ) {
			return new WP_Error( 'itsec-security-check-pro-scan-incomplete', __( 'The iThemes Security Check server could not contact your site. Please wait a few minutes and try again.', 'better-wp-security' ) );
		}

		return $body;
	}

	/**
	 * Validates that the given key matches the expected hash and is not expired.
	 *
	 * @param string    $key     The provided authorization key.
	 * @param int|false $expires The allowed expiration in seconds. Defaults to 2 minutes.
	 *
	 * @return bool
	 */
	public static function validate_key( $key, $expires = false ) {
		$salt = ITSEC_Modules::get_setting( 'security-check-pro', 'key_salt' );
		$key  = trim( $key );

		if ( empty( $salt ) ) {
			return false; // Only validate if a salt has been stored.
		}

		if ( ! preg_match( '/^(\d+):([a-f0-9]+)$/', $key, $matches ) ) {
			return false;
		}

		if ( false === $expires ) {
			$expires = 2 * MINUTE_IN_SECONDS; // keys expire every 2 minutes by default.
		}

		$time = $matches[1];
		$hash = $matches[2];

		if ( time() > $time + $expires ) {
			return false;
		}

		$calculated_hash = hash_hmac( 'md5', $time, $salt );

		return hash_equals( $calculated_hash, $hash );
	}

	/**
	 * Gets the key passed to the SSL Proxy Detect server to authorize its callback request.
	 *
	 * If a salt has not yet been generated, one is stored and saved to settings.
	 *
	 * @return string
	 */
	public static function get_key() {
		$salt = ITSEC_Modules::get_setting( 'security-check-pro', 'key_salt' );

		if ( empty( $salt ) ) {
			$salt = wp_generate_password( 60, true, true );
			ITSEC_Modules::set_setting( 'security-check-pro', 'key_salt', $salt );
			ITSEC_Storage::save();
		}

		$time = time();
		$hash = hash_hmac( 'md5', $time, $salt );

		return "$time:$hash";
	}

	/**
	 * Get the list of remote IPs that the SSL Proxy Detect server may be issuing requests from.
	 *
	 * @return string[]|WP_Error
	 */
	public static function get_remote_ips() {
		$remote_ips = apply_filters( 'itsec-security-check-pro-remote-ips', array() );

		if ( is_array( $remote_ips ) && ! empty( $remote_ips ) ) {
			return $remote_ips;
		}

		$settings = ITSEC_Modules::get_settings( 'security-check-pro' );

		if ( $settings['remote_ips_timestamp'] + ( 5 * MINUTE_IN_SECONDS ) > time() && ! empty( $settings['remote_ips'] ) ) {
			return $settings['remote_ips'];
		}

		$response = wp_remote_get( self::$config_url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = $response['body'];
		$data = json_decode( $body, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'itsec_security_check_pro_invalid_json_response', json_last_error_msg() );
		}

		if ( ! is_array( $data ) || ! isset( $data['ips'] ) || ! is_array( $data['ips'] ) ) {
			return new WP_Error( 'itsec_security_check_pro_malformed_response', __( 'The response body is missing the "ips" entry.', 'better-wp-security' ) );
		}

		$settings['remote_ips_timestamp'] = time();
		$settings['remote_ips']           = $data['ips'];

		ITSEC_Modules::set_settings( 'security-check-pro', $settings );

		return $data['ips'];
	}
}
