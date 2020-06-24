<?php

final class ITSEC_Network_Brute_Force_Utilities {
	private static $network_endpoint = 'https://ipcheck-api.ithemes.com/';

	/**
	 * Check visitor IP to see if it is banned by IPCheck.
	 *
	 * @param string $ip
	 *
	 * @return bool true if banned, false otherwise.
	 */
	public static function is_ip_banned( $ip = '' ) {
		if ( ! $ip ) {
			$ip = ITSEC_Lib::get_ip();
		}

		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-ip-tools.php' );

		if ( ! ITSEC_Lib_IP_Tools::validate( $ip ) || ITSEC_Lib::is_ip_whitelisted( $ip ) ) {
			return false;
		}

		return self::get_server_response( 'check-ip', $ip );
	}

	/**
	 * Report visitor IP for blacklistable-offense to IPCheck.
	 *
	 * @param string $ip
	 *
	 * @return bool true if banned, false otherwise.
	 */
	public static function report_ip( $ip = '' ) {
		if ( ! $ip ) {
			$ip = ITSEC_Lib::get_ip();
		}

		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-ip-tools.php' );

		if ( ! ITSEC_Lib_IP_Tools::validate( $ip ) || ITSEC_Lib::is_ip_whitelisted( $ip ) ) {
			return false;
		}

		return self::get_server_response( 'report-ip', $ip );
	}

	private static function get_server_response( $action, $ip ) {
		$api_key    = ITSEC_Modules::get_setting( 'network-brute-force', 'api_key' );
		$api_secret = ITSEC_Modules::get_setting( 'network-brute-force', 'api_secret' );

		if ( ! $api_key || ! $api_secret ) {
			return false;
		}

		$cache = self::get_cache( $ip );

		if ( 'check-ip' === $action ) {
			if ( $cache['cache_ttl'] >= ITSEC_Core::get_current_time_gmt() ) {
				return $cache['block'];
			}
		} elseif ( 'report-ip' === $action ) {
			if ( $cache['report_ttl'] >= ITSEC_Core::get_current_time_gmt() ) {
				return $cache['block'];
			}
		}

		$args = json_encode( array(
			'apikey'    => $api_key,
			'behavior'  => 'brute-force-login',
			'ip'        => $ip,
			'site'      => home_url( '', 'http' ),
			'timestamp' => ITSEC_Core::get_current_time_gmt(),
			'login'     => array(
				'details' => ITSEC_Lib::get_login_details(),
				'agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
			),
		) );

		$response = self::call_api( $action, array(), array(
			'method' => 'POST',
			'body' => array(
				'request'   => $args,
				'signature' => self::hmac_sha1( $api_secret, $action . $args ),
			),
		) );

		if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
			return false;
		}

		$response = json_decode( $response['body'], true );

		if ( ! is_array( $response ) || empty( $response['success'] ) ) {
			return false;
		}

		self::set_cache( $ip, $response );

		$cache_seconds = isset( $response['cache_ttl'] ) ? absint( $response['cache_ttl'] ) : 3600;

		if ( ! empty( $response['block'] ) ) {
			$data = array(
				'expires'     => date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time() + $cache_seconds ),
				'expires_gmt' => date( 'Y-m-d H:i:s', ITSEC_Core::get_current_time_gmt() + $cache_seconds ),
				'type'        => 'host',
			);

			ITSEC_Log::add_action( 'ipcheck', 'ip-blocked', $data );

			return true;
		}

		return false;
	}

	private static function set_cache( $ip, $response ) {
		$cache = self::get_cache( $ip );
		$time  = ITSEC_Core::get_current_time_gmt();

		if ( isset( $response['block'] ) ) {
			$cache['block'] = (boolean) $response['block'];
		}

		if ( isset( $response['cache_ttl'] ) ) {
			$cache['cache_ttl'] = intval( $response['cache_ttl'] ) + $time;
		} elseif ( 0 === $cache['cache_ttl'] ) {
			$cache['cache_ttl'] = $time + HOUR_IN_SECONDS;
		}

		if ( isset( $response['report_ttl'] ) ) {
			$cache['report_ttl'] = intval( $response['report_ttl'] ) + $time;
		}

		$transient_time = max( $cache['cache_ttl'], $cache['report_ttl'] ) - $time;


		set_site_transient( "itsec_ipcheck_$ip", $cache, $transient_time );
	}

	private static function get_cache( $ip ) {
		$cache = get_site_transient( "itsec_ipcheck_$ip" );

		$defaults = array(
			'block'      => false,
			'cache_ttl'  => 0,
			'report_ttl' => 0,
		);

		if ( ! is_array( $cache ) ) {
			return $defaults;
		}

		return array_merge( $defaults, $cache );
	}

	/**
	 * Calculates the HMAC of a string using SHA1.
	 *
	 * there is a native PHP hmac function, but we use this one for
	 * the widest compatibility with older PHP versions
	 *
	 * @param string $key  the shared secret key used to generate the mac
	 * @param string $data data to be signed
	 *
	 *
	 * @return  string    base64 encoded hmac
	 */
	private static function hmac_sha1( $key, $data ) {
		if ( strlen( $key ) > 64 ) {
			$key = pack( 'H*', sha1( $key ) );
		}

		$key  = str_pad( $key, 64, chr( 0x00 ) );
		$ipad = str_repeat( chr( 0x36 ), 64 );
		$opad = str_repeat( chr( 0x5c ), 64 );
		$hmac = pack( 'H*', sha1( ( $key ^ $opad ) . pack( 'H*', sha1( ( $key ^ $ipad ) . $data ) ) ) );

		return base64_encode( $hmac );
	}

	/**
	 * Retrieve an API key from the IPCheck server
	 *
	 * @since 4.5
	 *
	 * @param string $email the email address to associate with the key
	 * @param bool   $optin true to optin to mailing list else false
	 *
	 * @return string|WP_Error The API key or a WP_Error object.
	 */
	public static function get_api_key( $email, $optin ) {
		$email = sanitize_text_field( trim( $email ) );

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'itsec-network-brute-force-utilities-get-api-key-bad-email', sprintf( __( 'The supplied email address (%s) is invalid. A valid email address is required in order to sign up for the Network Bruteforce Protection by iThemes.', 'better-wp-security' ), $email ) );
		}

		$response = self::call_api( 'request-key', array(
			'email' => $email,
			'optin' => $optin,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['body'] ) ) {
			return new WP_Error( 'itsec-network-brute-force-utilities-get-api-key-failed-get-request', __( 'An unknown error prevented the API key request from succeeding. This problem could be due to a server configuration or plugin compatibility issue. Please wait a few minutes and try again.', 'better-wp-security' ) );
		}

		$body = json_decode( $response['body'], true );

		if ( ! is_array( $body ) ) {
			return new WP_Error( 'itsec-network-brute-force-utilities-get-api-key-bad-response', __( 'An unknown error prevented the API key request from succeeding. The request for an API key returned an unrecognized response. Please wait a few minutes and try again.', 'better-wp-security' ) );
		}

		if ( isset( $body['error']['message'] ) ) {
			return new WP_Error(
				'itsec-network-brute-force-utilities-get-api-key-' . ( isset( $body['error']['type'] ) ? $body['error']['type'] : 'unknown' ),
				sprintf( __( 'There was an error returned from the Network Brute Force Protection API: %1$s', 'better-wp-security' ), $body['error']['message'] )
			);
		}

		$key = trim( sanitize_text_field( $body['apikey'] ) );

		if ( empty( $key ) ) {
			return new WP_Error( 'itsec-network-brute-force-utilities-get-api-key-bad-response', __( 'An unknown error prevented the API key request from succeeding. The request for an API key returned an empty key. Please wait a few minutes and try again.', 'better-wp-security' ) );
		}

		return $key;
	}

	/**
	 * Activate an IPCheck API Key
	 *
	 * @since 4.5
	 *
	 * @param string $api_key the API key to activate
	 *
	 * @return string|WP_Error IPCheck activation secret or a WP_Error object.
	 */
	public static function activate_api_key( $api_key ) {
		$api_key = sanitize_text_field( trim( $api_key ) );

		if ( empty( $api_key ) ) {
			return new WP_Error( 'itsec-network-brute-force-utilities-activate-api-key-empty-key', __( 'An unknown error prevented the API key secret request from succeeding. The request for an API key submitted an empty key. Please wait a few minutes and try again.', 'better-wp-security' ) );
		}

		$response = self::call_api( 'activate-key', array(
			'apikey' => $api_key,
			'site'   => home_url( '', 'http' ),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['body'] ) ) {
			return new WP_Error( 'itsec-network-brute-force-utilities-activate-api-key-failed-get-request', __( 'An unknown error prevented the API key secret request from succeeding. This problem could be due to a server configuration or plugin compatibility issue. Please wait a few minutes and try again.', 'better-wp-security' ) );
		}

		$body = json_decode( $response['body'], true );

		if ( ! is_array( $body ) || ! isset( $body['secret'] ) ) {
			// If this is an error with a message, show that to the user
			if ( ! empty( $body['error'] ) && ! empty( $body['error']['message'] ) ) {
				return new WP_Error( 'itsec-network-brute-force-utilities-activate-api-key-error-response', sprintf( __( 'There was an error returned from the Network Brute Force Protection API: %1$s', 'better-wp-security' ), $body['error']['message'] ) );
			}

			return new WP_Error( 'itsec-network-brute-force-utilities-activate-api-key-bad-response', __( 'An unknown error prevented the API key secret request from succeeding. The request for an API key secret returned an unrecognized response. Please wait a few minutes and try again.', 'better-wp-security' ) );
		}

		$secret = trim( sanitize_text_field( $body['secret'] ) );

		if ( empty( $secret ) ) {
			return new WP_Error( 'itsec-network-brute-force-utilities-activate-api-key-bad-response', __( 'An unknown error prevented the API key secrete request from succeeding. The request for an API key secret returned an empty key secret. Please wait a few minutes and try again.', 'better-wp-security' ) );
		}

		return $secret;
	}

	private static function call_api( $action, $query = array(), $args = array() ) {

		$url = self::$network_endpoint;
		$url = add_query_arg( 'action', $action, $url );

		if ( $query ) {
			$url = add_query_arg( $query, $url );
		}

		if ( ! isset( $args['user-agent'] ) ) {
			if ( ITSEC_Core::is_pro() ) {
				$args['user-agent'] = 'iThemes Security Pro/';
			} else {
				$args['user-agent'] = 'iThemes Security/';
			}

			$args['user-agent'] .= ITSEC_Core::get_plugin_version() . '-' . ITSEC_Core::get_plugin_build() . '; WordPress/' . get_bloginfo( 'version' );
		}

		$url  = apply_filters( 'itsec_ipcheck_api_request_url', $url, $action, $query, $args );
		$args = apply_filters( 'itsec_ipcheck_api_request_args', $args, $url, $action, $query );

		return wp_remote_request( $url, $args );
	}
}
