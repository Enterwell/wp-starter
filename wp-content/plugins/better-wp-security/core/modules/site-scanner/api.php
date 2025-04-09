<?php

use iThemesSecurity\Lib\Result;
use iThemesSecurity\Site_Scanner\Factory;
use iThemesSecurity\Site_Scanner\Scan;

class ITSEC_Site_Scanner_API {

	const HOST = 'https://itsec-site-scanner.ithemes.com/';
	const ACCEPT = 'application/vnd.site-scanner.ithemes;v=1.1';
	const VERIFY_TOKEN = 'site-scanner-verify';

	/**
	 * Performs the site scan.
	 *
	 * @param int $site_id The site ID to scan. Accepts 0 to scan the main site in a multisite network.
	 *
	 * @return Scan
	 */
	public static function scan( $site_id = 0 ) {
		$factory = ITSEC_Modules::get_container()->get( Factory::class );
		$pid     = ITSEC_Log::add_process_start( 'site-scanner', 'scan', compact( 'site_id' ) );

		if ( $site_id && ! is_main_site( $site_id ) ) {
			$results = self::scan_sub_site( $pid, $site_id );
		} else {
			$results = self::scan_main_site( $pid );
		}

		/** @var array|WP_Error $response */
		$response = $results['response'];
		$cached   = $results['cached'];

		if ( is_wp_error( $response ) ) {
			$response->add_data( array_merge( (array) $response->get_error_data(), [ 'url' => get_site_url( $site_id ) ] ) );

			if ( $response->get_error_message( 'invalid_license' ) ) {
				ITSEC_Modules::set_setting( 'global', 'licensed_hostname_prompt', true );

				$response->add(
					'invalid_license',
					sprintf(
						esc_html__( 'Please %1$sconfirm%2$s your licensing details.', 'better-wp-security' ),
						'<a href="' . esc_url( admin_url( 'options-general.php?page=ithemes-licensing' ) ) . '">',
						'</a>'
					)
				);
			}
		}

		if ( self::is_temporary_server_error( $response ) ) {
			$response->add( 'itsec-temporary-server-error', __( 'Site Scanning is temporarily unavailable, please try again later.', 'better-wp-security' ) );
		}

		$log_data = [ 'results' => $response, 'cached' => $cached ];
		ITSEC_Log::add_process_stop( $pid, $log_data );

		if ( $cached ) {
			$scan = $factory->for_api_response( $response );
		} else {
			$code = ITSEC_Site_Scanner_Util::get_scan_result_code( $response );

			if ( is_wp_error( $response ) ) {
				$id = ITSEC_Log::add_warning( 'site-scanner', $code, $log_data );
			} elseif ( 'error' === $code ) {
				$id = ITSEC_Log::add_warning( 'site-scanner', $code, $log_data );
			} elseif ( 'clean' === $code ) {
				$id = ITSEC_Log::add_notice( 'site-scanner', $code, $log_data );
			} else {
				$id = ITSEC_Log::add_critical_issue( 'site-scanner', $code, $log_data );
			}

			if ( 'file' !== ITSEC_Modules::get_setting( 'global', 'log_type' ) ) {
				$scan = $factory->for_log_id( $id );
			} else {
				$scan = $factory->for_api_response( $response );
			}
		}

		/**
		 * Fires after a site scan has completed.
		 *
		 * @param Scan $scan
		 * @param int  $site_id
		 * @param bool $cached
		 */
		do_action( 'itsec_site_scanner_scan_complete', $scan, $site_id, $cached );

		return $scan;
	}

	/**
	 * Scan the main site.
	 *
	 * @param array $pid
	 *
	 * @return array
	 */
	private static function scan_main_site( array $pid ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! ITSEC_Core::is_licensed() && ! self::has_registered_site_key() ) {
			$registered = self::register_site();

			if ( is_wp_error( $registered ) ) {
				return [ 'cached' => false, 'response' => $registered ];
			}
		}

		$plugins = $themes = [];

		list( $wp_version ) = explode( '-', $GLOBALS['wp_version'] );

		foreach ( get_plugins() as $file => $data ) {
			if ( ! empty( $data['Version'] ) ) {
				$plugins[ dirname( $file ) ] = $data['Version'];
			}
		}

		foreach ( wp_get_themes() as $theme ) {
			$themes[ $theme->get_stylesheet() ] = $theme->get( 'Version' );
		}

		$body = [
			'wordpress'   => $wp_version,
			'plugins'     => $plugins,
			'themes'      => $themes,
			'mutedIssues' => wp_list_pluck( ITSEC_Site_Scanner_Util::get_muted_issues(), 'id' ),
		];

		return self::make_request( get_main_site_id(), 'api/scan', 'POST', [], $body, $pid );
	}

	/**
	 * Scan a sub site.
	 *
	 * @param array $pid     The process id for logging.
	 * @param int   $site_id The site ID to scan.
	 *
	 * @return array
	 */
	private static function scan_sub_site( array $pid, int $site_id ) {
		if ( ! self::has_registered_site_key( $site_id ) ) {
			$registered = self::register_site( $site_id );

			if ( is_wp_error( $registered ) ) {
				return [ 'cached' => false, 'response' => $registered ];
			}
		}

		return self::make_request( $site_id, 'api/scan', 'POST', [], [
			'scan' => [
				'url' => self::clean_url( get_home_url( $site_id ) ),
			]
		], $pid );
	}

	/**
	 * Registers a site with the Site Scanner API.
	 *
	 * This is not meant to be used by licensed Solid Security users.
	 *
	 * @param int $site_id The site ID to register.
	 *
	 * @return array|mixed|WP_Error
	 */
	public static function register_site( int $site_id = 0 ) {
		$site_id  = $site_id ?: get_main_site_id();
		$pid      = ITSEC_Log::add_process_start( 'site-scanner', 'register-site', compact( 'site_id' ) );
		$key_pair = self::generate_key_pair();

		if ( is_wp_error( $key_pair ) ) {
			ITSEC_Log::add_process_stop( $pid, $key_pair );

			return $key_pair;
		}

		$url      = self::clean_url( get_home_url( $site_id ) );
		$response = self::make_request( $site_id, 'api/register-site', 'POST', [], [
			'url'          => $url,
			'keyPair'      => $key_pair,
			'verifyTarget' => get_rest_url( $site_id, 'ithemes-security/v1/site-scanner/verify-scan' ),
		], $pid )['response'];

		if ( is_wp_error( $response ) ) {
			ITSEC_Log::add_process_stop( $pid, $response );

			return $response;
		}

		$key = $response['key'];

		self::set_registered_site_key( $site_id, $key );
		ITSEC_Log::add_process_stop( $pid, $response );

		return $key;
	}

	/**
	 * Gets the list of vulnerabilities that have a firewall rule available.
	 *
	 * @param array $vulnerabilities
	 *
	 * @return Result<int[]>
	 */
	public static function get_available_firewall_rules( array $vulnerabilities ): Result {
		[ 'response' => $response ] = self::make_request(
			get_main_site_id(),
			'available-firewall-rules',
			'GET',
			[
				'ids' => array_map( function ( $id ) {
					return str_replace( 'ps-', '', $id );
				}, $vulnerabilities ),
			]
		);

		if ( is_wp_error( $response ) ) {
			return Result::error( $response );
		}

		return Result::success( array_map( function ( $id ) {
			return 'ps-' . $id;
		}, $response ) );
	}

	/**
	 * Make a request to the site scanner API.
	 *
	 * @param int        $site_id The site ID being operated on.
	 * @param string     $route   Route to call.
	 * @param string     $method  HTTP method to use.
	 * @param array      $query   Query vars to pass.
	 * @param array|null $body    Data to be encoded as json.
	 * @param array|null $pid     Process ID to continue making log updates.
	 *
	 * @return array Array of response and cache status.
	 */
	private static function make_request(
		int $site_id,
		string $route,
		string $method,
		array $query = [],
		array $body = null,
		array $pid = null
	) {
		$headers = [
			'Accept' => self::ACCEPT,
		];

		$json = '';

		if ( $body ) {
			$json          = wp_json_encode( $body );
			$headers       = [
				'Content-Type' => 'application/json',
				'Accept'       => self::ACCEPT,
			];
			$authorization = self::get_authorization_header( $site_id, $json );

			if ( is_wp_error( $authorization ) ) {
				return [ 'cached' => false, 'response' => $authorization ];
			}

			if ( is_string( $authorization ) ) {
				$headers['Authorization'] = $authorization;
			}
		}

		if ( $pid ) {
			ITSEC_Log::add_process_update( $pid, compact( 'route', 'method', 'body', 'headers' ) );
		}

		$cache_key = self::build_cache_key( $site_id, $route, $method, $query, $body );
		$cached    = true;

		if ( ( $parsed = get_site_transient( $cache_key ) ) === false ) {
			$cached   = false;
			$response = self::call_api( $route, $query, [
				'body'    => $json,
				'method'  => $method,
				'timeout' => 300,
				'headers' => $headers,
			] );

			if ( is_wp_error( $response ) ) {
				return compact( 'cached', 'response' );
			}

			$parsed = self::parse_response( $response );
			self::maybe_cache( $pid, $cache_key, $response, $parsed );
		}

		return [ 'cached' => $cached, 'response' => $parsed ];
	}

	/**
	 * Gets the Authorization header for a request.
	 *
	 * @param int    $site_id The site id being operated on.
	 * @param string $json    The serialized request body.
	 *
	 * @return string|WP_Error|null The authorization header, a WP_Error if generation failed, null if none is available.
	 */
	private static function get_authorization_header( int $site_id, string $json ) {
		if ( is_main_site( $site_id ) ) {
			$signature = self::generate_signature( $json );

			if ( ! is_wp_error( $signature ) ) {
				return $signature;
			}

			if ( 'non_active_license' !== $signature->get_error_code() ) {
				return $signature;
			}
		}

		$key = self::get_registered_site_key( $site_id );

		if ( ! $key ) {
			return null;
		}

		return 'X-SiteRegistration ' . $key;
	}

	/**
	 * Sign the given request data.
	 *
	 * @param string $json Request body to sign.
	 *
	 * @return string|WP_Error
	 */
	private static function generate_signature( $json ) {
		if ( ! ITSEC_Core::is_pro() ) {
			return new WP_Error( 'non_active_license', __( 'Not a Solid Security Pro install.', 'better-wp-security' ) );
		}

		if ( ! isset( $GLOBALS['ithemes_updater_path'] ) ) {
			return new WP_Error( 'updater_not_available', __( 'Could not find the SolidWP updater.', 'better-wp-security' ) );
		}

		require_once( $GLOBALS['ithemes_updater_path'] . '/keys.php' );
		require_once( $GLOBALS['ithemes_updater_path'] . '/packages.php' );

		$keys = Ithemes_Updater_Keys::get( [ 'ithemes-security-pro' ] );

		if ( empty( $keys['ithemes-security-pro'] ) ) {
			return new WP_Error( 'non_active_license', __( 'Solid Security Pro is not activated.', 'better-wp-security' ) );
		}

		$signature = hash_hmac( 'sha1', $json, $keys['ithemes-security-pro'] );

		if ( ! $signature ) {
			return new WP_Error( 'hmac_failed', __( 'Failed to calculate hmac.', 'better-wp-security' ) );
		}

		$package_details = Ithemes_Updater_Packages::get_full_details();

		if ( empty( $package_details['packages']['ithemes-security-pro/ithemes-security-pro.php']['user'] ) ) {
			return new WP_Error( 'non_active_license', __( 'Solid Security Pro is not activated.', 'better-wp-security' ) );
		}

		$user = $package_details['packages']['ithemes-security-pro/ithemes-security-pro.php']['user'];
		$site = self::clean_url( ITSEC_Core::get_licensed_url() ?: network_home_url() );

		return sprintf( 'X-KeySignature signature="%s" username="%s" site="%s"', $signature, $user, $site );
	}

	/**
	 * Cleans a URL.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	private static function clean_url( $url ) {
		return preg_replace( '|/$|', '', $url );
	}

	/**
	 * Parse a response into a WP_Error or the result.
	 *
	 * @param array $response WP_Http response.
	 *
	 * @return mixed|null|WP_Error
	 */
	private static function parse_response( $response ) {
		$parsed = self::parse_response_body( $response );
		$code   = wp_remote_retrieve_response_code( $response );

		if ( $code >= 400 ) {
			if ( is_wp_error( $parsed ) ) {
				return $parsed;
			}

			if ( ! is_array( $parsed ) ) {
				return new WP_Error( 'invalid_json', __( 'Invalid JSON.', 'better-wp-security' ), wp_remote_retrieve_body( $response ) );
			}

			return new WP_Error(
				$parsed['code'] ?? 'unknown_error',
				$parsed['message'] ?? __( 'Unknown Error', 'better-wp-security' ),
				$parsed['data'] ?? []
			);
		}

		return $parsed;
	}

	/**
	 * Parse the response body out of the response object.
	 *
	 * @param $response
	 *
	 * @return mixed|null|WP_Error
	 */
	private static function parse_response_body( $response ) {
		$body         = wp_remote_retrieve_body( $response );
		$code         = wp_remote_retrieve_response_code( $response );
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );

		if ( 204 === $code ) {
			return null;
		}

		if ( ! $body ) {
			return new WP_Error( 'empty_response_body', __( 'Empty response body.', 'better-wp-security' ) );
		}

		if ( 'application/json' === $content_type ) {
			$decoded = json_decode( $body, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				return new WP_Error( 'invalid_json', sprintf( __( 'Invalid JSON: %s.', 'better-wp-security' ), json_last_error_msg() ) );
			}

			return $decoded;
		}

		return $body;
	}

	/**
	 * Builds the cache key based on the selected route.
	 *
	 * @param int        $site_id
	 * @param string     $route
	 * @param string     $method
	 * @param array|null $body
	 *
	 * @return string
	 */
	private static function build_cache_key( int $site_id, string $route, string $method, array $query, ?array $body ) {
		$body  = $body ? wp_json_encode( $body ) : '';
		$query = self::build_query_string( $query );

		return 'itsec-site-scanner-' . md5( $site_id . $route . $method . $query . $body );
	}

	/**
	 * Maybe cache the response if the cache control allows it.
	 *
	 * @param array  $pid
	 * @param string $cache_key
	 * @param array  $response
	 * @param array  $cache
	 */
	private static function maybe_cache( $pid, $cache_key, $response, $cache ) {
		$cache_control = wp_remote_retrieve_header( $response, 'cache-control' );

		if ( ! $cache_control ) {
			return;
		}

		$keywords = array_map( 'trim', explode( ',', $cache_control ) );

		$mapped = [];

		foreach ( $keywords as $keyword ) {
			if ( false === strpos( $keyword, '=' ) ) {
				$mapped[ $keyword ] = true;
			} else {
				list( $key, $value ) = explode( '=', $keyword, 2 );
				$mapped[ $key ] = $value;
			}
		}

		if ( isset( $mapped['max-age'] ) ) {
			$cached = set_site_transient( $cache_key, $cache, (int) $mapped['max-age'] );

			if ( ! $pid ) {
				return;
			}

			if ( $cached ) {
				ITSEC_Log::add_process_update( $pid, [ 'action' => 'caching-response', 'mapped' => $mapped, 'cache_key' => $cache_key ] );
			} else {
				ITSEC_Log::add_process_update( $pid, [ 'action' => 'caching-response-failed', 'mapped' => $mapped ] );
			}
		}
	}

	/**
	 * Call the API.
	 *
	 * @param string $route Route to call.
	 * @param array  $query Query Args.
	 * @param array  $args  Arguments to pass to {@see wp_remote_request()}.
	 *
	 * @return array|WP_Error
	 */
	private static function call_api( $route, $query, $args ) {
		$url = self::HOST . $route;

		if ( $query ) {
			$url .= '?' . self::build_query_string( $query );
		}

		$url  = apply_filters( 'itsec_site_scanner_api_request_url', $url, $route, $query, $args );
		$args = apply_filters( 'itsec_site_scanner_api_request_args', $args, $url, $route, $query );

		return wp_remote_request( $url, $args );
	}

	/**
	 * Generate a public secret key pair for a sub-site site scan.
	 *
	 * @return array|WP_Error
	 */
	public static function generate_key_pair() {
		$public = wp_generate_password( 64, false );
		$secret = ITSEC_Lib_Opaque_Tokens::create_token( self::VERIFY_TOKEN, [
			'public' => $public,
		] );

		if ( is_wp_error( $secret ) ) {
			return $secret;
		}

		return compact( 'public', 'secret' );
	}

	/**
	 * Gets the public key for the given secret key.
	 *
	 * @param string $secret_key
	 *
	 * @return string|WP_Error
	 */
	public static function get_public_key( $secret_key ) {
		$token = ITSEC_Lib_Opaque_Tokens::verify_and_get_token_data(
			self::VERIFY_TOKEN,
			$secret_key,
			15 * MINUTE_IN_SECONDS
		);

		if ( is_wp_error( $token ) ) {
			return $token;
		}

		return $token['public'];
	}

	/**
	 * Deletes the key pair.
	 *
	 * @param string $secret_key
	 */
	public static function clear_key_pair( $secret_key ) {
		ITSEC_Lib_Opaque_Tokens::delete_token( $secret_key );
	}

	/**
	 * Checks if the site has a registered site key.
	 *
	 * @param int $site_id
	 *
	 * @return bool
	 */
	public static function has_registered_site_key( int $site_id = 0 ): bool {
		$site_id   = $site_id ?: get_main_site_id();
		$site_keys = ITSEC_Modules::get_setting( 'site-scanner', 'registered_sites' );

		if ( ! isset( $site_keys[ $site_id ] ) ) {
			return false;
		}

		return $site_keys[ $site_id ]['url'] === self::clean_url( get_home_url( $site_id ) );
	}

	/**
	 * Gets the registered site ID for a site.
	 *
	 * @param int $site_id The site id to get the key for. Defaults to the current site.
	 *
	 * @return string|null The registered site key if it exists, null otherwise.
	 */
	public static function get_registered_site_key( int $site_id = 0 ) {
		$site_id   = $site_id ?: get_main_site_id();
		$site_keys = ITSEC_Modules::get_setting( 'site-scanner', 'registered_sites' );

		return $site_keys[ $site_id ]['key'] ?? null;
	}

	/**
	 * Sets the registered site key for a site.
	 *
	 * @param int    $site_id
	 * @param string $key
	 */
	public static function set_registered_site_key( int $site_id, string $key ) {
		$registered_sites = ITSEC_Modules::get_setting( 'site-scanner', 'registered_sites' );

		$registered_sites[ $site_id ] = [
			'url' => self::clean_url( get_home_url( $site_id ) ),
			'key' => $key,
		];
		ITSEC_Modules::set_setting( 'site-scanner', 'registered_sites', $registered_sites );
	}

	/**
	 * Check if this is a temporary server error, in which case we should retry the scan at a later point in time,
	 * or if this is an issue with the client that needs to be fixed.
	 *
	 * @param array|WP_Error $results The parsed results from the scan.
	 *
	 * @return bool
	 */
	private static function is_temporary_server_error( $results ) {
		if ( ! is_wp_error( $results ) ) {
			return false;
		}

		$code = $results->get_error_code();

		if ( 'http_request_failed' === $code && strpos( $results->get_error_message(), 'cURL error 52:' ) !== false ) {
			return true;
		}

		$codes = [
			'empty_response_body',
			'invalid_json',
			'internal_server_error',
		];

		return in_array( $code, $codes, true );
	}

	/**
	 * Build a query string from an array of key value pairs.
	 *
	 * This function can use the return value of `parse()` to build a query
	 * string. This function does not modify the provided keys when an array is
	 * encountered (like `http_build_query()` would).
	 *
	 * @param array     $params   Query string parameters.
	 * @param int|false $encoding Set to false to not encode, PHP_QUERY_RFC3986
	 *                            to encode using RFC3986, or PHP_QUERY_RFC1738
	 *                            to encode using RFC1738.
	 */
	private static function build_query_string( array $params, $encoding = PHP_QUERY_RFC3986 ): string {
		if ( ! $params ) {
			return '';
		}

		if ( $encoding === false ) {
			$encoder = function ( string $str ): string {
				return $str;
			};
		} elseif ( $encoding === PHP_QUERY_RFC3986 ) {
			$encoder = 'rawurlencode';
		} elseif ( $encoding === PHP_QUERY_RFC1738 ) {
			$encoder = 'urlencode';
		} else {
			throw new \InvalidArgumentException( 'Invalid type' );
		}

		$qs = '';
		foreach ( $params as $k => $v ) {
			$k = $encoder( (string) $k );
			if ( ! is_array( $v ) ) {
				$qs .= $k;
				$v  = is_bool( $v ) ? (int) $v : $v;
				if ( $v !== null ) {
					$qs .= '=' . $encoder( (string) $v );
				}
				$qs .= '&';
			} else {
				foreach ( $v as $vv ) {
					$qs .= $k;
					$vv = is_bool( $vv ) ? (int) $vv : $vv;
					if ( $vv !== null ) {
						$qs .= '=' . $encoder( (string) $vv );
					}
					$qs .= '&';
				}
			}
		}

		return $qs ? (string) substr( $qs, 0, - 1 ) : '';
	}
}
