<?php

/**
 * Miscellaneous plugin-wide functions.
 *
 * Various static functions to provide information to modules and other areas throughout the plugin.
 *
 * @since   4.0.0
 * @package iThemes_Security
 *
 */
final class ITSEC_Lib {
	/**
	 * Clear caches.
	 *
	 * Clears popular WordPress caching mechanisms.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $page [optional] true to clear page cache
	 *
	 * @return void
	 */
	public static function clear_caches( $page = false ) {

		//clear APC Cache
		if ( function_exists( 'apc_store' ) ) {
			apc_clear_cache(); //Let's clear APC (if it exists) when big stuff is saved.
		}

		//clear w3 total cache or wp super cache
		if ( function_exists( 'w3tc_pgcache_flush' ) ) {

			if ( true == $page ) {
				w3tc_pgcache_flush();
				w3tc_minify_flush();
			}

			w3tc_dbcache_flush();
			w3tc_objectcache_flush();

		} elseif ( function_exists( 'wp_cache_clear_cache' ) && true == $page ) {

			wp_cache_clear_cache();

		}


		do_action( 'itsec-lib-clear-caches' );
	}

	/**
	 * Creates appropriate database tables.
	 *
	 * @since 4.0.0
	 *
	 * @return true|WP_Error
	 */
	public static function create_database_tables() {
		require_once( ITSEC_Core::get_core_dir() . '/lib/schema.php' );

		return ITSEC_Schema::create_database_tables();
	}

	/**
	 * Gets location of wp-config.php.
	 *
	 * Finds and returns path to wp-config.php
	 *
	 * @since 4.0.0
	 *
	 * @return string path to wp-config.php
	 * */
	public static function get_config() {
		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-config-file.php' );

		return ITSEC_Lib_Config_File::get_wp_config_file_path();
	}

	/**
	 * Return primary domain from given url.
	 *
	 * Returns primary domain name (without subdomains) of given URL.
	 *
	 * @since 4.0.0
	 *
	 * @param string $url URL to filter
	 *
	 * @return string domain name or '*' on error or domain mapped multisite
	 * */
	public static function get_domain( $url ) {
		if ( is_multisite() && function_exists( 'domain_mapping_warning' ) ) {
			return '*';
		}


		$host = parse_url( $url, PHP_URL_HOST );

		if ( false === $host ) {
			return '*';
		}
		if ( 'www.' == substr( $host, 0, 4 ) ) {
			return substr( $host, 4 );
		}

		$host_parts = explode( '.', $host );

		if ( count( $host_parts ) > 2 ) {
			$host_parts = array_slice( $host_parts, - 2, 2 );
		}

		return implode( '.', $host_parts );
	}

	/**
	 * Returns the root of the WordPress install.
	 *
	 * Gets the URI path to the WordPress installation.
	 *
	 * @since 4.0.6
	 *
	 * @return string the root folder
	 */
	public static function get_home_root() {
		if ( isset( $GLOBALS['__itsec_lib_get_home_root'] ) ) {
			return $GLOBALS['__itsec_lib_get_home_root'];
		}

		$url_parts = parse_url( site_url() );

		if ( isset( $url_parts['path'] ) ) {
			$GLOBALS['__itsec_lib_get_home_root'] = trailingslashit( $url_parts['path'] );
		} else {
			$GLOBALS['__itsec_lib_get_home_root'] = '/';
		}

		return $GLOBALS['__itsec_lib_get_home_root'];
	}

	/**
	 * Gets location of .htaccess
	 *
	 * Finds and returns path to .htaccess or nginx.conf if appropriate
	 *
	 * @since 4.0.0
	 *
	 * @return string path to .htaccess
	 */
	public static function get_htaccess() {
		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-config-file.php' );

		return ITSEC_Lib_Config_File::get_server_config_file_path();
	}

	/**
	 * Returns the actual IP address of the user.
	 *
	 * Determines the user's IP address by returning the forwarded IP address if present or
	 * the direct IP address if not.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $use_cache Whether to check the cache, or force the retrieval of a new value.
	 *
	 * @return string The IP address of the user
	 */
	public static function get_ip( $use_cache = true ) {
		if ( isset( $GLOBALS['__itsec_remote_ip'] ) && $use_cache ) {
			return $GLOBALS['__itsec_remote_ip'];
		}

		$ip = apply_filters( 'itsec-get-ip', false );

		if ( false !== $ip ) {
			$ip = filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE );

			if ( $ip ) {
				$GLOBALS['__itsec_remote_ip'] = $ip;

				return $ip;
			}
		}

		self::load( 'ip-detector' );
		$ip = ITSEC_Lib_IP_Detector::build()->get();

		if ( ! $ip ) {
			// If an IP is not found, force it to a localhost IP that would not be blacklisted as this typically
			// indicates a local request that does not provide the localhost IP.
			$ip = '127.0.0.1';
		}

		$GLOBALS['__itsec_remote_ip'] = $ip;

		return $GLOBALS['__itsec_remote_ip'];
	}

	/**
	 * Returns the server type of the plugin user.
	 *
	 * Attempts to figure out what http server the visiting user is running.
	 *
	 * @since 4.0.0
	 *
	 * @return string Server type the user is using. Falls back to 'apache'.
	 */
	public static function get_server() {
		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-utility.php' );

		return ITSEC_Lib_Utility::get_web_server();
	}

	public static function get_whitelisted_ips() {
		return apply_filters( 'itsec_white_ips', array() );
	}

	/**
	 * Determines whether a given IP address is whiteliste
	 *
	 * @param string  $ip              ip to check (can be in CIDR notation)
	 * @param array   $whitelisted_ips ip list to compare to if not yet saved to options
	 * @param boolean $current         whether to whitelist the current ip or not (due to saving, etc)
	 *
	 * @return boolean true if whitelisted or false
	 */
	public static function is_ip_whitelisted( $ip, $whitelisted_ips = null, $current = false ) {

		/** @var ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout;

		$ip = sanitize_text_field( $ip );

		if ( ITSEC_Lib::get_ip() === $ip && $itsec_lockout->is_visitor_temp_whitelisted() ) {
			return true;
		}

		if ( ! class_exists( 'ITSEC_Lib_IP_Tools' ) ) {
			require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-ip-tools.php' );
		}

		if ( is_null( $whitelisted_ips ) ) {
			$whitelisted_ips = self::get_whitelisted_ips();
		}

		if ( $current ) {
			$whitelisted_ips[] = ITSEC_Lib::get_ip(); //add current user ip to whitelist
		}

		if ( ! empty( $_SERVER['SERVER_ADDR'] ) ) {
			$whitelisted_ips[] = $_SERVER['SERVER_ADDR'];
		}

		if ( ! empty( $_SERVER['LOCAL_ADDR'] ) ) {
			$whitelisted_ips[] = $_SERVER['LOCAL_ADDR'];
		}

		foreach ( $whitelisted_ips as $whitelisted_ip ) {
			if ( ITSEC_Lib_IP_Tools::intersect( $ip, ITSEC_Lib_IP_Tools::ip_wild_to_ip_cidr( $whitelisted_ip ) ) ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * Checks if the given IP is banned.
	 *
	 * @param string $ip IP address to check.
	 *
	 * @return bool
	 */
	public static function is_ip_banned( $ip = '' ) {
		$ip = $ip ?: ITSEC_Lib::get_ip();

		if ( ! ITSEC_Lib_IP_Tools::validate( $ip ) ) {
			return false;
		}

		$source = ITSEC_Modules::get_container()->get( \iThemesSecurity\Ban_Hosts\Source::class );
		$source = new \iThemesSecurity\Ban_Hosts\Deprecated_Filter_Source( $source );

		return (bool) $source->find_ban_for_host( $ip );
	}

	/**
	 * Gets the list of banned IPs.
	 *
	 * @return string[]
	 * @deprecated 6.7.0
	 *
	 */
	public static function get_blacklisted_ips() {
		_deprecated_function( __METHOD__, '6.7.0', \iThemesSecurity\Ban_Hosts\Multi_Repository::class );

		if (
			ITSEC_Modules::get_container()->has( \iThemesSecurity\Ban_Users\Database_Repository::class ) &&
			ITSEC_Modules::get_setting( 'ban-users', 'enable_ban_lists' )
		) {
			$repo = ITSEC_Modules::get_container()->get( \iThemesSecurity\Ban_Users\Database_Repository::class );
			$ips  = $repo->get_legacy_hosts();
		} else {
			$ips = [];
		}

		return apply_filters( 'itsec_filter_blacklisted_ips', $ips );
	}

	/**
	 * Determines whether a given IP address is blacklisted.
	 *
	 * @param string $ip              ip to check (can be in CIDR notation)
	 * @param array  $blacklisted_ips ip list to compare to if not yet saved to options
	 *
	 * @return boolean true if blacklisted or false
	 * @deprecated 6.7.0
	 *
	 */
	public static function is_ip_blacklisted( $ip = null, $blacklisted_ips = null ) {
		_deprecated_function( __METHOD__, '6.7.0', 'ITSEC_Lib::is_ip_banned' );

		if ( null !== $blacklisted_ips ) {
			foreach ( $blacklisted_ips as $blacklisted_ip ) {
				if ( ITSEC_Lib_IP_Tools::intersect( $ip, $blacklisted_ip ) ) {
					return true;
				}
			}

			return false;
		}

		return self::is_ip_banned( $ip );
	}

	/**
	 * Set a 404 error.
	 *
	 * Forces the given page to a WordPress 404 error.
	 *
	 * @since 4.0.0
	 *
	 * @return void
	 */
	public static function set_404() {

		global $wp_query;

		status_header( 404 );

		if ( function_exists( 'nocache_headers' ) ) {
			nocache_headers();
		}

		$wp_query->set_404();
		$page_404 = get_404_template();

		if ( 1 < strlen( $page_404 ) ) {

			include( $page_404 );

		} else {

			include( get_query_template( 'index' ) );

		}

		die();

	}

	/**
	 * Increases minimum memory limit.
	 *
	 * This function, adopted from builder, attempts to increase the minimum
	 * memory limit before heavy functions.
	 *
	 * @since 4.0.0
	 *
	 * @param int $new_memory_limit what the new memory limit should be
	 *
	 * @return void
	 */
	public static function set_minimum_memory_limit( $new_memory_limit ) {

		$memory_limit = @ini_get( 'memory_limit' );

		if ( - 1 < $memory_limit ) {

			$unit         = strtolower( substr( $memory_limit, - 1 ) );
			$memory_limit = (int) $memory_limit;

			$new_unit         = strtolower( substr( $new_memory_limit, - 1 ) );
			$new_memory_limit = (int) $new_memory_limit;

			if ( 'm' == $unit ) {

				$memory_limit *= 1048576;

			} elseif ( 'g' == $unit ) {

				$memory_limit *= 1073741824;

			} elseif ( 'k' == $unit ) {

				$memory_limit *= 1024;

			}

			if ( 'm' == $new_unit ) {

				$new_memory_limit *= 1048576;

			} elseif ( 'g' == $new_unit ) {

				$new_memory_limit *= 1073741824;

			} elseif ( 'k' == $new_unit ) {

				$new_memory_limit *= 1024;

			}

			if ( (int) $memory_limit < (int) $new_memory_limit ) {
				@ini_set( 'memory_limit', $new_memory_limit );
			}

		}

	}

	/**
	 * Checks if user exists.
	 *
	 * Checks to see if WordPress user with given id exists.
	 *
	 * @since 4.0.0
	 *
	 * @param int $user_id user id of user to check
	 *
	 * @return bool true if user exists otherwise false
	 *
	 * */
	public static function user_id_exists( $user_id ) {

		global $wpdb;

		//return false if username is null
		if ( '' == $user_id ) {
			return false;
		}

		//queary the user table to see if the user is there
		$saved_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM `" . $wpdb->users . "` WHERE ID= %d;", $user_id ) );

		if ( $saved_id == $user_id ) {

			return true;

		} else {

			return false;

		}

	}

	public static function show_status_message( $message ) {
		echo "<div class=\"updated fade\"><p><strong>$message</strong></p></div>\n";
	}

	public static function show_error_message( $message ) {
		if ( is_wp_error( $message ) ) {
			$message = $message->get_error_message();
		}

		if ( ! is_string( $message ) ) {
			return;
		}

		echo "<div class=\"error\"><p><strong>$message</strong></p></div>\n";
	}

	public static function show_inline_status_message( $message ) {
		echo "<div class=\"updated fade inline\"><p><strong>$message</strong></p></div>\n";
	}

	public static function show_inline_error_message( $message ) {
		if ( is_wp_error( $message ) ) {
			$message = $message->get_error_message();
		}

		if ( ! is_string( $message ) ) {
			return;
		}

		echo "<div class=\"error inline\"><p><strong>$message</strong></p></div>\n";
	}

	/**
	 * Print a WP core notice styled inline.
	 *
	 * @param string|WP_Error $message
	 * @param string          $type
	 *
	 * @return string
	 */
	public static function inline_styled_notice( $message, $type = 'error' ) {
		switch ( $type ) {
			case 'error':
				$bkg = '#dc3232';
				$bdr = '#fbeaea';
				break;
			case 'warning':
				$bkg = '#fff8e5';
				$bdr = '#ffb900';
				break;
			case 'info':
				$bkg = '#e5f5fa';
				$bdr = '#00a0d2';
				break;
			case 'success':
			default:
				$bkg = '#ecf7ed';
				$bdr = '#46b450';
				break;
		}

		if ( is_wp_error( $message ) ) {
			$messages = array();

			foreach ( $message->get_error_codes() as $code ) {
				foreach ( $message->get_error_messages( $code ) as $str ) {
					$messages[] = $str;
				}
			}

			$message = wp_sprintf( '%l', $messages );
		}

		$html = "<div style=\"background: {$bkg};border-left: 4px solid {$bdr};padding: 1px 12px; margin: 5px 0 15px;\">";
		$html .= '<p style="margin: 0.5em 6px 0.5em 0;padding: 2px;vertical-align: bottom;">';
		$html .= is_wp_error( $message ) ? $message->get_error_message() : $message;
		$html .= '</p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Get an error string for all errors in a WP_Error isntance.
	 *
	 * @param WP_Error $error
	 *
	 * @return string[]
	 */
	public static function get_error_strings( WP_Error $error ) {
		$messages = array();

		foreach ( $error->get_error_codes() as $code ) {
			foreach ( $error->get_error_messages( $code ) as $str ) {
				$messages[] = $str;
			}
		}

		return $messages;
	}

	/**
	 * Get a WordPress user object.
	 *
	 * @param int|string|WP_User|bool $user Either the user ID ( must be an int ), the username, a WP_User object,
	 *                                      or false to retrieve the currently logged-in user.
	 *
	 * @return WP_User|false
	 */
	public static function get_user( $user = false ) {
		if ( $user instanceof WP_User ) {
			return $user;
		}

		if ( false === $user ) {
			$user = wp_get_current_user();
		} elseif ( is_int( $user ) ) {
			$user = get_user_by( 'id', $user );
		} elseif ( is_string( $user ) ) {
			$user = get_user_by( 'login', $user );
		} elseif ( is_object( $user ) && isset( $user->ID ) ) {
			$user = get_user_by( 'id', $user->ID );
		} else {
			if ( is_object( $user ) ) {
				$type = 'object(' . get_class( $user ) . ')';
			} else {
				$type = gettype( $user );
			}

			error_log( 'ITSEC_Lib::get_user() called with an invalid $user argument. Received $user variable of type: ' . $type );

			wp_die( 'Internal Server Error' );
		}

		if ( $user instanceof WP_User ) {
			return $user;
		}

		return false;
	}

	/**
	 * Evaluate a password's strength.
	 *
	 * @param string $password
	 * @param array  $penalty_strings Additional strings that if found within the password, will decrease the strength.
	 *
	 * @return array
	 */
	public static function get_password_strength_results( $password, $penalty_strings = array() ) {
		global $itsec_zxcvbn;

		if ( ! $itsec_zxcvbn ) {
			$itsec_zxcvbn = new \iThemesSecurity\Strauss\ZxcvbnPhp\Zxcvbn();
		}

		return $itsec_zxcvbn->passwordStrength( $password, $penalty_strings );
	}

	/**
	 * Retrieve the URL to a website to lookup the location of an IP address.
	 *
	 * @param string|bool $ip IP address to lookup, or false to return a URL to their home page.
	 *
	 * @return string
	 */
	public static function get_trace_ip_link( $ip = false ) {
		if ( empty( $ip ) ) {
			$link = 'https://www.iptrackeronline.com/ithemes.php';
		} else {
			$link = 'http://www.iptrackeronline.com/ithemes.php?ip_address=' . urlencode( $ip );
		}

		return apply_filters( 'itsec_ip_details_link', $link, $ip );
	}

	/**
	 * Whenever a login fails, collect details of the attempt, and forward them to modules.
	 *
	 * @param string $username
	 */
	public static function handle_wp_login_failed( $username ) {
		$details = self::get_login_details();

		do_action( 'itsec-handle-failed-login', $username, $details );
	}

	public static function get_login_details() {
		$authentication_types = array();

		if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$http_auth_type = substr( $_SERVER['HTTP_AUTHORIZATION'], 0, 6 );

			if ( 'Basic ' === $http_auth_type ) {
				$authentication_types[] = 'header_http_basic_auth';
			} elseif ( 'OAuth ' === $http_auth_type ) {
				$authentication_types[] = 'header_http_oauth';
			}
		}

		if ( isset( $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) ) {
			$authentication_types[] = 'header_http_basic_auth';
		}

		if ( ! empty( $_GET['oauth_consumer_key'] ) ) {
			$authentication_types[] = 'query_oauth';
		}

		if ( ! empty( $_POST['oauth_consumer_key'] ) ) {
			$authentication_types[] = 'post_oauth';
		}

		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			$source               = 'xmlrpc';
			$authentication_types = array( 'username_and_password' );
		} elseif ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$source                 = 'rest_api';
			$authentication_types[] = 'cookie';
		} else {
			$source               = 'wp-login.php';
			$authentication_types = array( 'username_and_password' );
		}

		$details = compact( 'source', 'authentication_types' );

		return apply_filters( 'itsec-filter-failed-login-details', $details );
	}

	/**
	 * Reliably provides the URL path.
	 *
	 * It optionally takes a prefix that will be stripped from the path, if present. This is useful for use to get site
	 * URL paths without the site's subdirectory.
	 *
	 * Trailing slashes are not preserved.
	 *
	 * @param string $url    The URL to pull the path from.
	 * @param string $prefix [optional] A string prefix to be removed from the path.
	 *
	 * @return string The URL path.
	 */
	public static function get_url_path( $url, $prefix = '' ) {
		$path = (string) parse_url( $url, PHP_URL_PATH );
		$path = untrailingslashit( $path );

		if ( ! empty( $prefix ) && 0 === strpos( $path, $prefix ) ) {
			return substr( $path, strlen( $prefix ) );
		}

		return '';
	}

	/**
	 * Returns the current request path without the protocol, domain, site subdirectories, or query args.
	 *
	 * This function returns "wp-login.php" when requesting http://example.com/site-path/wp-login.php?action=register.
	 *
	 * @return string The requested site path.
	 */
	public static function get_request_path() {
		if ( ! isset( $GLOBALS['__itsec_lib_get_request_path'] ) ) {
			$request_uri                             = preg_replace( '|//+|', '/', $_SERVER['REQUEST_URI'] );
			$GLOBALS['__itsec_lib_get_request_path'] = self::get_url_path( $request_uri, self::get_home_root() );
		}

		return $GLOBALS['__itsec_lib_get_request_path'];
	}

	/**
	 * Acquire a lock.
	 *
	 * @since 6.3.0
	 *
	 * @param string $name       Lock name.
	 * @param int    $expires_in Number of seconds to hold the lock for.
	 *
	 * @return bool
	 */
	public static function get_lock( $name, $expires_in = 30 ) {

		$pre_check = apply_filters( 'itsec_pre_get_lock', null, $name, $expires_in );

		if ( null !== $pre_check ) {
			return $pre_check;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$main_options = $wpdb->base_prefix . 'options';

		$lock       = "itsec-lock-{$name}";
		$now        = ITSEC_Core::get_current_time_gmt();
		$release_at = $now + $expires_in;

		if ( is_multisite() ) {
			$result = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO `{$main_options}` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, 'no') /* LOCK */", $lock, $release_at ) );
		} else {
			$result = $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO `$wpdb->options` (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, 'no') /* LOCK */", $lock, $release_at ) );
		}

		// The lock exists. See if it has expired.
		if ( ! $result ) {

			if ( is_multisite() && get_current_blog_id() !== 1 ) {
				$locked_until = $wpdb->get_var( $wpdb->prepare( "SELECT `option_value` FROM {$main_options} WHERE `option_name` = %s", $main_options ) );
			} else {
				$locked_until = get_option( $lock );
			}

			if ( ! $locked_until ) {
				// Can't write or read the lock. Bail due to an unknown and hopefully temporary error.
				return false;
			}

			if ( $locked_until > $now ) {
				// The lock still exists and has not expired.
				return false;
			}
		}

		// Ensure that the lock is set properly by triggering all the regular actions and filters.
		if ( ! is_multisite() || get_current_blog_id() === 1 ) {
			update_option( $lock, $release_at );
		} else {
			$wpdb->update( $main_options, array( 'option_value' => $release_at ), array( 'option_name' => $lock ) );

			if ( function_exists( 'wp_cache_switch_to_blog' ) ) {
				// Update persistent object caches
				$current = get_current_blog_id();
				wp_cache_switch_to_blog( 1 );

				$alloptions = wp_cache_get( 'alloptions' );

				if ( is_array( $alloptions ) && isset( $alloptions[ $lock ] ) ) {
					$alloptions[ $lock ] = $release_at;
					wp_cache_set( 'alloptions', $alloptions, 'options' );
				} else {
					wp_cache_set( $lock, $release_at, 'options' );
				}

				wp_cache_switch_to_blog( $current );
			}
		}

		return true;
	}

	/**
	 * Release a lock.
	 *
	 * @since 6.3.0
	 *
	 * @param string $name The lock name.
	 */
	public static function release_lock( $name ) {
		$pre_check = apply_filters( 'itsec_pre_release_lock', null, $name );

		if ( null !== $pre_check ) {
			return;
		}

		$lock = "itsec-lock-{$name}";

		if ( is_multisite() && get_current_blog_id() !== 1 ) {

			/** @var \wpdb $wpdb */
			global $wpdb;
			$main_options = $wpdb->base_prefix . 'options';

			$wpdb->delete( $main_options, array( 'option_name' => $lock ) );

			if ( function_exists( 'wp_cache_switch_to_blog' ) ) {
				// Update persistent object caches
				$current = get_current_blog_id();
				wp_cache_switch_to_blog( 1 );

				$alloptions = wp_cache_get( 'alloptions' );

				if ( is_array( $alloptions ) && isset( $alloptions[ $lock ] ) ) {
					unset( $alloptions[ $lock ] );
					wp_cache_set( 'alloptions', $alloptions, 'options' );
				} else {
					wp_cache_delete( $lock, 'options' );
				}

				wp_cache_switch_to_blog( $current );
			}
		} else {
			delete_option( $lock );
		}
	}

	public static function has_lock( $name ) {

		/** @var \wpdb $wpdb */
		global $wpdb;
		$main_options = $wpdb->base_prefix . 'options';

		$lock = "itsec-lock-{$name}";

		if ( is_multisite() ) {
			$result = $wpdb->get_var( $wpdb->prepare( "SELECT `option_value` FROM `{$main_options}` WHERE `option_name` = %s", $lock ) );
		} else {
			$result = $wpdb->get_var( $wpdb->prepare( "SELECT `option_value` FROM `$wpdb->options` WHERE `option_name` = %s", $lock ) );
		}

		if ( ! $result ) {
			return false;
		}

		if ( (int) $result < ITSEC_Core::get_current_time_gmt() ) {
			return false;
		}

		return true;
	}

	/**
	 * Clear any expired locks.
	 *
	 * The vast majority of locks should be cleared by the same process that acquires them, however, this will clear locks that remain
	 * due to a time out or fatal error.
	 *
	 * @since 3.8.0
	 */
	public static function delete_expired_locks() {

		/** @var \wpdb $wpdb */
		global $wpdb;
		$main_options = $wpdb->base_prefix . 'options';

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT `option_name` FROM {$main_options} WHERE `option_name` LIKE %s AND `option_value` < %d",
			$wpdb->esc_like( 'itsec-lock-' ) . '%', ITSEC_Core::get_current_time_gmt()
		) );

		if ( $rows ) {
			if ( is_multisite() && get_current_blog_id() !== 1 ) {
				if ( function_exists( 'wp_cache_switch_to_blog' ) ) {
					// Update persistent object caches
					$current = get_current_blog_id();
					wp_cache_switch_to_blog( 1 );

					$alloptions = wp_cache_get( 'alloptions' );
					$set_all    = false;

					foreach ( $rows as $row ) {
						$lock = $row->option_name;

						if ( is_array( $alloptions ) && isset( $alloptions[ $lock ] ) ) {
							unset( $alloptions[ $lock ] );
							$set_all = true;
						} else {
							wp_cache_delete( $lock, 'options' );
						}
					}

					if ( $set_all ) {
						wp_cache_set( 'alloptions', $alloptions );
					}

					wp_cache_switch_to_blog( $current );
				}

				$wpdb->query( $wpdb->prepare(
					"DELETE FROM {$main_options} WHERE `option_name` LIKE %s AND `option_value` < %d",
					$wpdb->esc_like( 'itsec-lock-' ) . '%', ITSEC_Core::get_current_time_gmt()
				) );
			} else {
				foreach ( $rows as $row ) {
					delete_option( $row->option_name );
				}
			}
		}
	}

	/**
	 * Replace a tag with a given value.
	 *
	 * Will look in the content for a tag matching the {{ $tag_name }} pattern.
	 *
	 * @param string $content
	 * @param string $tag
	 * @param string $replacement
	 *
	 * @return string
	 */
	public static function replace_tag( $content, $tag, $replacement ) {
		return preg_replace( '/{{ \$' . preg_quote( $tag, '/' ) . ' }}/', $replacement, $content );
	}

	/**
	 * Replace multiple tags.
	 *
	 * @param string $content
	 * @param array  $tags Array of tag names to replacements.
	 *
	 * @return string
	 */
	public static function replace_tags( $content, $tags ) {
		foreach ( $tags as $tag => $replacement ) {
			$content = self::replace_tag( $content, $tag, $replacement );
		}

		return $content;
	}

	/**
	 * Get a percentage value indicating the probability that the site supports SSL.
	 *
	 * The need for a probability value is that a site could appear to support SSL yet the certificate is self-signed.
	 *
	 * @return int
	 */
	public static function get_ssl_support_probability() {
		if ( is_ssl() ) {
			$probability = 50; // The site appears to be on an SSL connection but it could be self-signed or otherwise
			// not valid to a visitor.
		} else {
			$probability = 0;
		}

		return apply_filters( 'itsec-ssl-support-probability', $probability );
	}

	/**
	 * Format a date using date_i18n and convert the time from GMT to local.
	 *
	 * @author Modified from ticket #25331
	 *
	 * @param int|DateTimeInterface $time   The time to use.
	 * @param string                $format Specify the format. If blank, will default to the date and time format settings.
	 *
	 * @return string
	 */
	public static function date_format_i18n_and_local_timezone( $time, $format = '' ) {
		if ( $time instanceof \DateTimeInterface ) {
			$time = $time->getTimestamp();
		}

		if ( ! $format ) {
			$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		}

		return date_i18n( $format, strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $time ) ) ) );
	}

	/**
	 * Displays a human time diff, or a formatted date if the time is too far in the past.
	 *
	 * @param int    $from   Unix timestamp from which the difference begins.
	 * @param int    $to     Optional. Unix timestamp to end the time difference. Default becomes time() if not set.
	 * @param string $format The date format to use. If omitted, the configured date_format is used instead.
	 *
	 * @return string
	 */
	public static function human_time_diff_or_date( int $from, int $to = 0, string $format = '' ): string {
		$to   = $to ?: time();
		$diff = abs( $to - $from );

		if ( $diff < DAY_IN_SECONDS ) {
			return sprintf( __( '%s ago', 'default' ), human_time_diff( $from, $to ) );
		}

		$format = $format ?: get_option( 'date_format' );

		return gmdate( $format, $from );
	}

	/**
	 * Get the value of an option directly from the database, bypassing any caching.
	 *
	 * @param string $option
	 *
	 * @return array|mixed
	 */
	public static function get_uncached_option( $option ) {
		/** @var $wpdb \wpdb */
		global $wpdb;

		$storage = array();

		if ( is_multisite() ) {
			$network_id = get_current_site()->id;
			$row        = $wpdb->get_row( $wpdb->prepare( "SELECT meta_value FROM $wpdb->sitemeta WHERE meta_key = %s AND site_id = %d", $option, $network_id ) );

			if ( is_object( $row ) ) {
				$storage = maybe_unserialize( $row->meta_value );
			}
		} else {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", $option ) );

			if ( is_object( $row ) ) {
				$storage = maybe_unserialize( $row->option_value );
			}
		}

		return $storage;
	}

	/**
	 * Get a dot nested value from an array.
	 *
	 * @param array  $array
	 * @param string $key
	 * @param mixed  $default
	 * @param string $delimeter
	 *
	 * @return mixed
	 */
	public static function array_get( $array, $key, $default = null, $delimeter = '.' ) {
		if ( ! is_array( $array ) ) {
			return $default;
		}

		if ( isset( $array[ $key ] ) ) {
			return $array[ $key ];
		}

		if ( strpos( $key, $delimeter ) === false ) {
			return isset( $array[ $key ] ) ? $array[ $key ] : $default;
		}

		foreach ( explode( $delimeter, $key ) as $segment ) {
			if ( is_array( $array ) && isset( $array[ $segment ] ) ) {
				$array = $array[ $segment ];
			} else {
				return $default;
			}
		}

		return $array;
	}

	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * @param array  $array
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return array
	 */
	public static function array_set( $array, $key, $value ) {
		$keys   = explode( '.', $key );
		$modify = &$array;

		while ( count( $keys ) > 1 ) {
			$key = array_shift( $keys );
			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset( $modify[ $key ] ) || ! is_array( $modify[ $key ] ) ) {
				$modify[ $key ] = [];
			}

			$modify = &$modify[ $key ];
		}

		$modify[ array_shift( $keys ) ] = $value;

		return $array;
	}

	/**
	 * Removes items at the given locations from an array.
	 *
	 * This accepts a dotted path with '*' to represent wildcards.
	 *
	 * @param array  $array
	 * @param string $dotted_path
	 *
	 * @return array
	 */
	public static function array_remove( array $array, string $dotted_path ): array {
		$paths = explode( '.', $dotted_path );

		return self::_array_remove( $array, $paths );
	}

	private static function _array_remove( array $array, array $paths ): array {
		if ( ! $array ) {
			return $array;
		}

		$path = array_shift( $paths );

		if ( '*' === $path ) {
			foreach ( $array as $k => $v ) {
				if ( is_array( $v ) ) {
					$array[ $k ] = self::_array_remove( $v, $paths );
				} elseif ( ! $paths ) {
					// If the last dotted path is a wildcard,
					// remove all elements.
					unset( $array[ $k ] );
				}
			}
		} elseif ( isset( $array[ $path ] ) ) {
			if ( is_array( $array[ $path ] ) && $paths ) {
				$array[ $path ] = self::_array_remove( $array[ $path ], $paths );
			} else {
				unset( $array[ $path ] );
			}
		}

		return $array;
	}

	/**
	 * Removes any number of items from a list.
	 *
	 * Values are loosely compared.
	 *
	 * @param array $array
	 * @param       ...$values
	 *
	 * @return array
	 */
	public static function array_pull( array $array, ...$values ): array {
		return array_values( array_diff( $array, $values ) );
	}

	/**
	 * Merges two arrays recursively such that only arrays are deeply merged.
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	public static function array_merge_recursive_distinct( array $array1, array $array2 ): array {
		$merged = $array1;

		foreach ( $array2 as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				$merged[ $key ] = self::array_merge_recursive_distinct( $merged[ $key ], $value );
			} else {
				$merged[ $key ] = $value;
			}
		}

		return $merged;
	}

	public static function print_r( $data, $args = array() ) {
		require_once( ITSEC_Core::get_core_dir() . '/lib/debug.php' );

		ITSEC_Debug::print_r( $data, $args );
	}

	public static function get_print_r( $data, $args = array() ) {
		require_once( ITSEC_Core::get_core_dir() . '/lib/debug.php' );

		return ITSEC_Debug::get_print_r( $data, $args );
	}

	/**
	 * Check if WP Cron appears to be running properly.
	 *
	 * @return bool
	 */
	public static function is_cron_working() {
		$working = ITSEC_Modules::get_setting( 'global', 'cron_status' );

		return $working === 1;
	}

	/**
	 * Should we be using Cron.
	 *
	 * @return bool
	 */
	public static function use_cron() {
		return ITSEC_Modules::get_setting( 'global', 'use_cron' );
	}

	/**
	 * Schedule a test to see if a user should be suggested to enable the Cron scheduler.
	 */
	public static function schedule_cron_test() {

		if ( defined( 'ITSEC_DISABLE_CRON_TEST' ) && ITSEC_DISABLE_CRON_TEST ) {
			return;
		}

		if ( $crons = _get_cron_array() ) {
			foreach ( $crons as $timestamp => $cron ) {
				if ( isset( $cron['itsec_cron_test'] ) ) {
					return;
				}
			}
		}

		// Get a random time in the next 6-18 hours on a random minute.
		$time = ITSEC_Core::get_current_time_gmt() + mt_rand( 6, 18 ) * HOUR_IN_SECONDS + mt_rand( 1, 60 ) * MINUTE_IN_SECONDS;
		wp_schedule_single_event( $time, 'itsec_cron_test', array( $time ) );
		ITSEC_Modules::set_setting( 'global', 'cron_test_time', $time );
	}

	/**
	 * Remove the forward slash.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function unfwdslash( $string ) {
		return ltrim( $string, '/' );
	}

	/**
	 * Add a forward slash.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function fwdslash( $string ) {
		return '/' . self::unfwdslash( $string );
	}

	/**
	 * Enqueue the itsec_util script.
	 *
	 * Will only be included once per page.
	 *
	 * @param array $args
	 */
	public static function enqueue_util( $args = array() ) {

		static $enqueued = false;

		if ( $enqueued ) {
			return;
		}

		$translations = array(
			'ajax_invalid'      => new WP_Error( 'itsec-settings-page-invalid-ajax-response', __( 'An "invalid format" error prevented the request from completing as expected. The format of data returned could not be recognized. This could be due to a plugin/theme conflict or a server configuration issue.', 'better-wp-security' ) ),
			'ajax_forbidden'    => new WP_Error( 'itsec-settings-page-forbidden-ajax-response: %1$s "%2$s"', __( 'A "request forbidden" error prevented the request from completing as expected. The server returned a 403 status code, indicating that the server configuration is prohibiting this request. This could be due to a plugin/theme conflict or a server configuration issue. Please try refreshing the page and trying again. If the request continues to fail, you may have to alter plugin settings or server configuration that could account for this AJAX request being blocked.', 'better-wp-security' ) ),
			'ajax_not_found'    => new WP_Error( 'itsec-settings-page-not-found-ajax-response: %1$s "%2$s"', __( 'A "not found" error prevented the request from completing as expected. The server returned a 404 status code, indicating that the server was unable to find the requested admin-ajax.php file. This could be due to a plugin/theme conflict, a server configuration issue, or an incomplete WordPress installation. Please try refreshing the page and trying again. If the request continues to fail, you may have to alter plugin settings, alter server configurations, or reinstall WordPress.', 'better-wp-security' ) ),
			'ajax_server_error' => new WP_Error( 'itsec-settings-page-server-error-ajax-response: %1$s "%2$s"', __( 'A "internal server" error prevented the request from completing as expected. The server returned a 500 status code, indicating that the server was unable to complete the request due to a fatal PHP error or a server problem. This could be due to a plugin/theme conflict, a server configuration issue, a temporary hosting issue, or invalid custom PHP modifications. Please check your server\'s error logs for details about the source of the error and contact your hosting company for assistance if required.', 'better-wp-security' ) ),
			'ajax_unknown'      => new WP_Error( 'itsec-settings-page-ajax-error-unknown: %1$s "%2$s"', __( 'An unknown error prevented the request from completing as expected. This could be due to a plugin/theme conflict or a server configuration issue.', 'better-wp-security' ) ),
			'ajax_timeout'      => new WP_Error( 'itsec-settings-page-ajax-error-timeout: %1$s "%2$s"', __( 'A timeout error prevented the request from completing as expected. The site took too long to respond. This could be due to a plugin/theme conflict or a server configuration issue.', 'better-wp-security' ) ),
			'ajax_parsererror'  => new WP_Error( 'itsec-settings-page-ajax-error-parsererror: %1$s "%2$s"', __( 'A parser error prevented the request from completing as expected. The site sent a response that jQuery could not process. This could be due to a plugin/theme conflict or a server configuration issue.', 'better-wp-security' ) ),
		);

		foreach ( $translations as $i => $translation ) {
			$messages = ITSEC_Response::get_error_strings( $translation );

			if ( $messages ) {
				$translations[ $i ] = $messages[0];
			}
		}

		wp_enqueue_script( 'itsec-util', plugins_url( 'admin-pages/js/util.js', __FILE__ ), array( 'jquery' ), ITSEC_Core::get_plugin_build(), true );
		wp_localize_script( 'itsec-util', 'itsec_util', array(
			'ajax_action'  => isset( $args['action'] ) ? $args['action'] : 'itsec_settings_page',
			'ajax_nonce'   => wp_create_nonce( isset( $args['nonce'] ) ? $args['nonce'] : 'itsec-settings-nonce' ),
			'translations' => $translations,
		) );

		$enqueued = true;
	}

	/**
	 * Replace the prefix of a target string with another prefix.
	 *
	 * If the given target does not start with the current prefix, the string
	 * will be returned unmodified.
	 *
	 * @param string $target      String to perform replacement on.
	 * @param string $current     The current prefix.
	 * @param string $replacement The new prefix.
	 *
	 * @return string
	 */
	public static function replace_prefix( $target, $current, $replacement ) {
		if ( 0 !== strpos( $target, $current ) ) {
			return $target;
		}

		$stripped = substr( $target, strlen( $current ) );

		return $replacement . $stripped;
	}

	/**
	 * Convert an iterator to an array.
	 *
	 * @param iterable $iterator
	 *
	 * @return array
	 */
	public static function iterator_to_array( $iterator ) {

		if ( is_array( $iterator ) ) {
			return $iterator;
		}

		// Available since PHP 5.1, but SPL which isn't guaranteed.
		if ( function_exists( 'iterator_to_array' ) ) {
			return iterator_to_array( $iterator );
		}

		$array = array();

		foreach ( $iterator as $key => $value ) {
			$array[ $key ] = $value;
		}

		return $array;
	}

	/**
	 * Inserts a new key/value before the key in the array.
	 *
	 * @param string $key       The key to insert before.
	 * @param array  $array     An array to insert in to.
	 * @param string $new_key   The key to insert.
	 * @param mixed  $new_value The value to insert.
	 *
	 * @return array
	 */
	public static function array_insert_before( $key, $array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
				$new[ $k ] = $value;
			}

			return $new;
		}

		$array[ $new_key ] = $new_value;

		return $array;
	}

	/**
	 * Insert an element after a given key.
	 *
	 * @param string|int $key
	 * @param array      $array
	 * @param string|int $new_key
	 * @param mixed      $new_value
	 *
	 * @return array
	 */
	public static function array_insert_after( $key, $array, $new_key, $new_value ) {
		if ( array_key_exists( $key, $array ) ) {
			$new = array();
			foreach ( $array as $k => $value ) {
				$new[ $k ] = $value;
				if ( $k === $key ) {
					$new[ $new_key ] = $new_value;
				}
			}

			return $new;
		}

		$array[ $new_key ] = $new_value;

		return $array;
	}

	/**
	 * Gets the first key in an array.
	 *
	 * @param array $arr
	 *
	 * @return int|string|null
	 */
	public static function array_key_first( array $arr ) {
		if ( function_exists( 'array_key_first' ) ) {
			return array_key_first( $arr );
		}

		foreach ( $arr as $key => $value ) {
			return $key;
		}

		return null;
	}

	/**
	 * Gets the last ket in an array.
	 *
	 * @param array $arr
	 *
	 * @return int|string|null
	 */
	public static function array_key_last( array $arr ) {
		if ( function_exists( 'array_key_last' ) ) {
			return array_key_last( $arr );
		}

		end( $arr );

		return key( $arr );
	}

	/**
	 * Gets the first item from an array.
	 *
	 * @param array $arr
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public static function first( array $arr, $default = null ) {
		return $arr[ self::array_key_first( $arr ) ] ?? $default;
	}

	/**
	 * Gets the last item from an array.
	 *
	 * @param array $arr
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public static function last( array $arr, $default = null ) {
		return $arr[ self::array_key_last( $arr ) ] ?? $default;
	}

	/**
	 * Plucks a certain field out of each item in the list.
	 *
	 * Similar to {@see wp_list_pluck()} but it supports using methods.
	 *
	 * @param array  $list      The list of items.
	 * @param string $field     The field or method name to use.
	 * @param string $index_key Field from the item to use as keys for the new array.
	 *
	 * @return array
	 */
	public static function pluck( array $list, $field, $index_key = '' ) {
		$output = [];

		foreach ( $list as $i => $item ) {
			$key = $index_key ? static::get( $item, $index_key ) : $i;

			$value = static::get( $item, $field );

			if ( null === $key ) {
				$output[] = $value;
			} else {
				$output[ $key ] = $value;
			}
		}

		return $output;
	}

	/**
	 * Get's a value from an array or object.
	 *
	 * @param array|object $item    The item to retrieve the value from.
	 * @param string       $field   The field or method name to use.
	 * @param null         $default The default value to return if no value is found.
	 *
	 * @return mixed|null
	 */
	public static function get( $item, $field, $default = null ) {
		if ( is_array( $item ) ) {
			return isset( $item[ $field ] ) ? $item[ $field ] : $default;
		}

		if ( is_object( $item ) ) {
			if ( is_callable( [ $item, $field ] ) ) {
				return $item->{$field}();
			}

			return isset( $item->{$field} ) ? $item->{$field} : $default;
		}

		return $default;
	}

	/**
	 * Finds the first item in a list matching the given predicate.
	 *
	 * @param iterable $list
	 * @param callable $predicate
	 *
	 * @return mixed|null
	 */
	public static function find_where( iterable $list, callable $predicate ) {
		foreach ( $list as $item ) {
			if ( $predicate( $item ) ) {
				return $item;
			}
		}

		return null;
	}

	/**
	 * Array unique implementation that allows for non-scalar values.
	 *
	 * Will compare elements using `serialize()`.
	 *
	 * Keys are preserved. If a numeric array is given, the array will be re-indexed.
	 *
	 * @param array $array
	 * @param bool  $stabilize If true, stabilizes the values first according to JSON semantics.
	 *
	 * @return array
	 */
	public static function non_scalar_array_unique( $array, $stabilize = false ) {

		$is_numeric = wp_is_numeric_array( $array );

		$hashes = array();

		foreach ( $array as $key => $value ) {
			if ( $stabilize ) {
				$value = rest_stabilize_value( $value );
			}

			$hash = serialize( $value );

			if ( isset( $hashes[ $hash ] ) ) {
				unset( $array[ $key ] );
			} else {
				$hashes[ $hash ] = 1;
			}
		}

		if ( $is_numeric ) {
			return array_values( $array );
		}

		return $array;
	}

	/**
	 * Parse a complex header that has attributes like quality values.
	 *
	 * @param string $header
	 *
	 * @return array[]
	 * @example Parsing the Accept-Language header.
	 *
	 * "en-US,en;q=0.9,de;q=0.8" transforms to:
	 *
	 * [
	 *     'en-US' => [],
	 *     'en'    => [ 'q' => 0.9 ],
	 *     'de'    => [ 'q' => 0.8' ],
	 * ]
	 *
	 */
	public static function parse_header_with_attributes( $header ) {

		$parsed = array();
		$list   = explode( ',', $header );

		foreach ( $list as $value ) {

			$attrs = array();
			$parts = explode( ';', trim( $value ) );
			$main  = trim( $parts[0], ' <>' );

			foreach ( $parts as $part ) {
				if ( false === strpos( $part, '=' ) ) {
					continue;
				}

				list( $key, $value ) = array_map( 'trim', explode( '=', $part, 2 ) );

				$attrs[ $key ] = trim( $value, '" ' );
			}

			$parsed[ $main ] = $attrs;
		}

		return $parsed;
	}

	/**
	 * Is a particular function allowed to be called.
	 *
	 * Checks disabled functions and the function blacklist.
	 *
	 * @param string $func
	 *
	 * @return bool
	 */
	public static function is_func_allowed( $func ) {

		static $cache = array();
		static $disabled;
		static $suhosin;

		if ( isset( $cache[ $func ] ) ) {
			return $cache[ $func ];
		}

		if ( $disabled === null ) {
			$disabled = preg_split( '/\s*,\s*/', (string) ini_get( 'disable_functions' ) );
		}

		if ( $suhosin === null ) {
			$suhosin = preg_split( '/\s*,\s*/', (string) ini_get( 'suhosin.executor.func.blacklist' ) );
		}

		if ( ! is_callable( $func ) ) {
			return $cache[ $func ] = false;
		}

		if ( in_array( $func, $disabled, true ) ) {
			return $cache[ $func ] = false;
		}

		if ( in_array( $func, $suhosin, true ) ) {
			return $cache[ $func ] = false;
		}

		return $cache[ $func ] = true;
	}

	/**
	 * Get whatever backup plugin is being used on this site.
	 *
	 * @return string
	 */
	public static function get_backup_plugin() {

		$possible = array(
			'backupbuddy/backupbuddy.php',
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'xcloner-backup-and-restore/xcloner.php',
			'duplicator/duplicator.php',
			'backup/backup.php',
			'wp-db-backup/wp-db-backup.php',
			'backupwordpress/backupwordpress.php',
			'blogvault-real-time-backup/blogvault.php',
			'wp-all-backup/wp-all-backup.php',
			'vaultpress/vaultpress.php',
		);

		/**
		 * Filter the list of possible backup plugins.
		 *
		 * @param string[] List of Backup Plugin __FILE__.
		 */
		$possible = apply_filters( 'itsec_possible_backup_plugins', $possible );

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			return '';
		}

		foreach ( $possible as $file ) {
			if ( is_plugin_active( $file ) ) {
				return $file;
			}
		}

		return '';
	}

	/**
	 * Generate a random token.
	 *
	 * @return string Hex token.
	 */
	public static function generate_token() {

		$length = 64;

		try {
			$random = bin2hex( random_bytes( $length / 2 ) );
		} catch ( Exception $e ) {
			$unpacked = unpack( 'H*', wp_generate_password( $length / 2, true, true ) );
			$random   = reset( $unpacked );
		}

		return $random;
	}

	/**
	 * Generate a hash of the token for storage.
	 *
	 * @param string $token
	 *
	 * @return false|string
	 */
	public static function hash_token( $token ) {
		return hash_hmac( self::get_hash_algo(), $token, wp_salt() );
	}

	/**
	 * Check if the provided token matches the stored hashed token.
	 *
	 * @param string $provided_token
	 * @param string $hashed_token
	 *
	 * @return bool
	 */
	public static function verify_token( $provided_token, $hashed_token ) {

		if ( ! $hashed_token || ! $provided_token ) {
			return false;
		}

		return hash_equals( $hashed_token, self::hash_token( $provided_token ) );
	}

	/**
	 * Get the hash algorithm to use.
	 *
	 * PHP can be compiled without the hash extension and the supported hash algos can be variable. WordPress shims
	 * support for md5 and sha1 hashes with hash_hmac.
	 *
	 * @return string
	 */
	public static function get_hash_algo() {

		if ( ! function_exists( 'hash_algos' ) ) {
			return 'sha1';
		}

		$algos = hash_algos();

		if ( in_array( 'sha256', $algos, true ) ) {
			return 'sha256';
		}

		return 'sha1';
	}

	public static function get_url_from_file( $file, $auto_ssl = true, $prevent_recursion = false ) {
		$file = str_replace( '\\', '/', $file );

		$url = '';

		$upload_dir            = ITSEC_Core::get_wp_upload_dir();
		$upload_dir['basedir'] = str_replace( '\\', '/', $upload_dir['basedir'] );

		if ( is_array( $upload_dir ) && ( false === $upload_dir['error'] ) ) {
			if ( 0 === strpos( $file, $upload_dir['basedir'] ) ) {
				$url = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $file );
			} elseif ( false !== strpos( $file, 'wp-content/uploads' ) ) {
				$path_pattern = 'wp-content/uploads';
				$url_base     = $upload_dir['baseurl'];

				if ( is_multisite() && ! ( is_main_network() && is_main_site() && defined( 'MULTISITE' ) ) ) {
					if ( defined( 'MULTISITE' ) ) {
						$mu_path = '/sites/' . get_current_blog_id();
					} else {
						$mu_path = '/' . get_current_blog_id();
					}

					if ( false === strpos( $file, "$path_pattern$mu_path" ) ) {
						$url_base = substr( $url_base, 0, - strlen( $mu_path ) );
					} else {
						$path_pattern .= $mu_path;
					}
				}

				$url = $url_base . substr( $file, strpos( $file, $path_pattern ) + strlen( $path_pattern ) );
			}
		}

		if ( empty( $url ) ) {
			if ( ! isset( $GLOBALS['__itsec_cache_wp_content_dir'] ) ) {
				$GLOBALS['__itsec_cache_wp_content_dir'] = rtrim( str_replace( '\\', '/', WP_CONTENT_DIR ), '/' );
			}
			if ( ! isset( $GLOBALS['__itsec_cache_abspath'] ) ) {
				$GLOBALS['__itsec_cache_abspath'] = rtrim( str_replace( '\\', '/', ABSPATH ), '/' );
			}

			if ( 0 === strpos( $file, $GLOBALS['__itsec_cache_wp_content_dir'] ) ) {
				$url = WP_CONTENT_URL . str_replace( '\\', '/', preg_replace( '/^' . preg_quote( $GLOBALS['__itsec_cache_wp_content_dir'], '/' ) . '/', '', $file ) );
			} elseif ( 0 === strpos( $file, $GLOBALS['__itsec_cache_abspath'] ) ) {
				$url = get_option( 'siteurl' ) . str_replace( '\\', '/', preg_replace( '/^' . preg_quote( $GLOBALS['__itsec_cache_abspath'], '/' ) . '/', '', $file ) );
			}
		}

		if ( empty( $url ) && ! $prevent_recursion ) {
			$url = self::get_url_from_file( realpath( $file ), $auto_ssl, true );
		}

		if ( empty( $url ) ) {
			return '';
		}

		if ( $auto_ssl ) {
			$url = self::fix_url( $url );
		}

		return $url;
	}

	public static function get_file_from_url( $url ) {
		$url = preg_replace( '/^https/', 'http', $url );
		$url = preg_replace( '/\?.*$/', '', $url );

		$file = '';

		$upload_dir = ITSEC_Core::get_wp_upload_dir();

		if ( is_array( $upload_dir ) && ( false === $upload_dir['error'] ) ) {
			if ( 0 === strpos( $url, $upload_dir['baseurl'] ) ) {
				$file = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
			} elseif ( false !== strpos( $url, 'wp-content/uploads' ) ) {
				$path_pattern = 'wp-content/uploads';
				$file_base    = $upload_dir['basedir'];

				if ( is_multisite() && ! ( is_main_network() && is_main_site() && defined( 'MULTISITE' ) ) ) {
					if ( defined( 'MULTISITE' ) ) {
						$mu_path = '/sites/' . get_current_blog_id();
					} else {
						$mu_path = '/' . get_current_blog_id();
					}

					if ( false === strpos( $url, "$path_pattern$mu_path" ) ) {
						$file_base = substr( $file_base, 0, - strlen( $mu_path ) );
					} else {
						$path_pattern .= $mu_path;
					}
				}

				$file = $file_base . substr( $url, strpos( $url, $path_pattern ) + strlen( $path_pattern ) );
			}
		}

		if ( empty( $file ) ) {
			if ( ! isset( $GLOBALS['__itsec_cache_wp_content_url'] ) ) {
				$GLOBALS['__itsec_cache_wp_content_url'] = preg_replace( '/^https/', 'http', WP_CONTENT_URL );
			}
			if ( ! isset( $GLOBALS['__itsec_cache_siteurl'] ) ) {
				$GLOBALS['__itsec_cache_siteurl'] = preg_replace( '/^https/', 'http', get_option( 'siteurl' ) );
			}

			if ( 0 === strpos( $url, $GLOBALS['__itsec_cache_wp_content_url'] ) ) {
				$file = rtrim( WP_CONTENT_DIR, '\\\/' ) . preg_replace( '/^' . preg_quote( $GLOBALS['__itsec_cache_wp_content_url'], '/' ) . '/', '', $url );
			} elseif ( 0 === strpos( $url, $GLOBALS['__itsec_cache_siteurl'] ) ) {
				$file = rtrim( ABSPATH, '\\\/' ) . preg_replace( '/^' . preg_quote( $GLOBALS['__itsec_cache_siteurl'], '/' ) . '/', '', $url );
			}
		}

		return $file;
	}

	public static function fix_url( $url ) {
		if ( is_ssl() ) {
			$url = preg_replace( '|^http://|', 'https://', $url );
		} else {
			$url = preg_replace( '|^https://|', 'http://', $url );
		}

		return $url;
	}

	/**
	 * Set a cookie.
	 *
	 * @param string $name
	 * @param string $value
	 * @param array  $args
	 */
	public static function set_cookie( $name, $value, $args = array() ) {

		$args = wp_parse_args( array(
			'length'    => 0,
			'http_only' => true,
		), $args );

		$expires = $args['length'] ? ITSEC_Core::get_current_time_gmt() + $args['length'] : 0;

		setcookie( $name, $value, $expires, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), $args['http_only'] );
	}

	/**
	 * Clear a cookie.
	 *
	 * @param string $name
	 */
	public static function clear_cookie( $name ) {
		setcookie( $name, ' ', ITSEC_Core::get_current_time_gmt() - YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, false, false );
	}

	/**
	 * Is the current request a loopback request.
	 *
	 * @return bool
	 */
	public static function is_loopback_request() {
		return in_array( self::get_ip(), ITSEC_Modules::get_setting( 'global', 'server_ips' ), true );
	}

	/**
	 * Version of {@see wp_slash()} that won't cast numbers to strings.
	 *
	 * @param array|string $value
	 *
	 * @return array|string
	 */
	public static function slash( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				if ( is_array( $v ) ) {
					$value[ $k ] = self::slash( $v );
				} elseif ( is_string( $v ) ) {
					$value[ $k ] = addslashes( $v );
				}
			}
		} elseif ( is_string( $value ) ) {
			$value = addslashes( $value );
		}

		return $value;
	}

	/**
	 * Format as a ISO 8601 date.
	 *
	 * @param int|string|\DateTimeInterface $date Epoch or strtotime compatible date.
	 *
	 * @return string|false
	 */
	public static function to_rest_date( $date = 0 ) {
		if ( ! $date ) {
			$date = ITSEC_Core::get_current_time_gmt();
		} elseif ( $date instanceof \DateTimeInterface ) {
			$date = $date->getTimestamp();
		} elseif ( ! is_int( $date ) ) {
			$date = strtotime( $date );
		}

		return gmdate( 'Y-m-d\TH:i:sP', $date );
	}

	/**
	 * Flatten an array.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public static function flatten( $array ) {
		if ( ! is_array( $array ) ) {
			return array( $array );
		}

		$merge = array();

		foreach ( $array as $value ) {
			$merge[] = self::flatten( $value );
		}

		return $merge ? call_user_func_array( 'array_merge', $merge ) : array();
	}

	/**
	 * Preload REST API requests.
	 *
	 * @param array $requests
	 *
	 * @return array
	 */
	public static function preload_rest_requests( $requests ) {
		$preload = array();

		foreach ( $requests as $key => $config ) {
			if ( is_string( $config ) ) {
				$key    = $config;
				$config = array( 'route' => $config );
			}

			$request = new WP_REST_Request(
				isset( $config['method'] ) ? $config['method'] : 'GET',
				$config['route']
			);

			if ( ! empty( $config['query'] ) ) {
				$request->set_query_params( $config['query'] );
			}

			$response = rest_do_request( $request );

			if ( $response->get_status() >= 200 && $response->get_status() < 300 ) {
				rest_send_allow_header( $response, rest_get_server(), $request );

				$preload[ $key ] = array(
					'body'    => rest_get_server()->response_to_data( $response, ! empty( $config['embed'] ) ),
					'headers' => $response->get_headers()
				);
			}
		}

		return $preload;
	}

	/**
	 * Check if the given string starts with the given needle.
	 *
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public static function str_starts_with( $haystack, $needle ) {
		return 0 === strpos( $haystack, $needle );
	}

	public static function str_ends_with( $haystack, $needle ) {
		return '' === $needle || substr_compare( $haystack, $needle, - strlen( $needle ) ) === 0;
	}

	/**
	 * Load a library class definition.
	 *
	 * @param string $name
	 */
	public static function load( $name ) {
		require_once( dirname( __FILE__ ) . "/lib/class-itsec-lib-{$name}.php" );
	}

	/**
	 * Combine multiple WP_Error instances.
	 *
	 * @param WP_Error|null ...$errors
	 *
	 * @return WP_Error
	 */
	public static function combine_wp_error( ...$errors ) {
		$combined = new WP_Error();

		self::add_to_wp_error( $combined, ...$errors );

		return $combined;
	}

	/**
	 * Add the subsequent WP Error data to the first WP Error instance.
	 *
	 * @param WP_Error      $add_to
	 * @param WP_Error|null ...$errors
	 */
	public static function add_to_wp_error( WP_Error $add_to, ...$errors ) {
		foreach ( $errors as $error ) {
			if ( $error ) {
				foreach ( $error->get_error_codes() as $code ) {
					foreach ( $error->get_error_messages( $code ) as $message ) {
						$add_to->add( $code, $message );
					}

					$data = $error->get_error_data( $code );

					if ( null !== $data ) {
						$add_to->add_data( $data, $code );
					}
				}
			}
		}
	}

	/**
	 * Render a file with only the given vars in context.
	 *
	 * @param string $file
	 * @param array  $context
	 * @param bool   $echo
	 *
	 * @return string|void
	 */
	public static function render( $file, $context = array(), $echo = true ) {
		$__echo = $echo;
		$__file = $file;

		extract( $context, EXTR_OVERWRITE );
		unset( $file, $context, $echo );

		if ( ! $__echo ) {
			ob_start();
		}

		require( $__file );

		if ( ! $__echo ) {
			return ob_get_clean() ?: '';
		}
	}

	/**
	 * Utility to mark this page as not cacheable.
	 */
	public static function no_cache() {
		nocache_headers();

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
	}

	/**
	 * Get the WordPress branch version.
	 *
	 * @return string
	 * @example 5.2.4 => 5.2
	 *
	 */
	public static function get_wp_branch() {
		$version = get_bloginfo( 'version' );

		list( $major, $minor ) = explode( '.', $version );

		return $major . '.' . $minor;
	}

	/**
	 * Are two lists equal ignoring order.
	 *
	 * @param array         $a
	 * @param array         $b
	 * @param callable|null $cmp
	 *
	 * @return bool
	 */
	public static function equal_sets( array $a, array $b, callable $cmp = null ) {
		if ( $cmp ) {
			usort( $a, $cmp );
			usort( $b, $cmp );
		} else {
			sort( $a );
			sort( $b );
		}

		return $a === $b;
	}

	/**
	 * Convert the return val from {@see ITSEC_Modules::set_settings()} to a WP_Error object.
	 *
	 * @param array $updated
	 *
	 * @return WP_Error|null
	 */
	public static function updated_settings_to_wp_error( $updated ) {
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		if ( $updated['saved'] ) {
			return null;
		}

		if ( $updated['errors'] ) {
			$error = self::combine_wp_error( ...$updated['errors'] );
		} else {
			$error = new \WP_Error( 'itsec.settings.set-failed', __( 'Failed to update settings.', 'better-wp-security' ), [ 'status' => \WP_Http::BAD_REQUEST ] );
		}

		return $error;
	}

	/**
	 * Sanitize the list of roles.
	 *
	 * @param string[] $roles
	 *
	 * @return array
	 */
	public static function sanitize_roles( $roles ) {
		return array_filter( $roles, static function ( $role ) {
			return (bool) get_role( $role );
		} );
	}

	/**
	 * Get a snapshot of $_SERVER properties.
	 *
	 * @return array
	 */
	public static function get_server_snapshot() {
		$whitelist = [
			'REQUEST_TIME',
			'REQUEST_TIME_FLOAT',
			'REQUEST_METHOD',
			'HTTPS',
			'REQUEST_SCHEME',
			'SERVER_PROTOCOL',
			'SCRIPT_FILENAME',
		];

		return array_filter( $_SERVER, static function ( $key ) use ( $whitelist ) {
			if ( $key === 'HTTP_COOKIE' ) {
				return false;
			}

			if ( self::str_starts_with( $key, 'HTTP_' ) ) {
				return true;
			}

			if ( self::str_starts_with( $key, 'CONTENT_' ) ) {
				return true;
			}

			return in_array( $key, $whitelist, true );
		}, ARRAY_FILTER_USE_KEY );
	}

	/**
	 * Version of {@see is_super_admin()} that operates on a `WP_User` instance.
	 *
	 * This bypasses an issue where {@see is_super_admin()} cannot be used during the `determine_current_user` filter since
	 * `is_super_admin` has a side effect of querying for the current user, causing an infinite loop.
	 *
	 * @param WP_User $user
	 *
	 * @return bool
	 */
	public static function is_super_admin( WP_User $user ) {
		if ( ! $user->exists() ) {
			return false;
		}

		if ( is_multisite() ) {
			$super_admins = get_super_admins();
			if ( is_array( $super_admins ) && in_array( $user->user_login, $super_admins ) ) {
				return true;
			}
		} else {
			if ( $user->has_cap( 'delete_users' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Performs a {@see dbDelta()} but reports any errors encountered.
	 *
	 * @param string $delta
	 *
	 * @return WP_Error
	 */
	public static function db_delta_with_error_handling( $delta ) {
		global $wpdb, $EZSQL_ERROR;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$err_count     = is_array( $EZSQL_ERROR ) ? count( $EZSQL_ERROR ) : 0;
		$showed_errors = $wpdb->show_errors( false );

		dbDelta( $delta );

		if ( $showed_errors ) {
			$wpdb->show_errors();
		}

		$wp_error = new WP_Error();

		if ( is_array( $EZSQL_ERROR ) ) {
			for ( $i = $err_count, $i_max = count( $EZSQL_ERROR ); $i < $i_max; $i ++ ) {
				$error = $EZSQL_ERROR[ $i ];

				if ( empty( $error['error_str'] ) || empty( $error['query'] ) || 0 === strpos( $error['query'], 'DESCRIBE ' ) ) {
					continue;
				}

				$wp_error->add( 'db_delta_error', $error['error_str'] );
			}
		}

		return $wp_error;
	}

	/**
	 * Get info used to help evaluate requirements according to
	 * {@see ITSEC_Lib::evaluate_requirements()}.
	 *
	 * @return array[]
	 */
	public static function get_requirements_info(): array {
		return [
			'server' => [
				'php'        => explode( '-', PHP_VERSION )[0],
				'extensions' => [
					'OpenSSL' => self::is_func_allowed( 'openssl_verify' ),
				],
			]
		];
	}

	/**
	 * Evaluate whether this site passes the given requirements.
	 *
	 * @param array $requirements
	 *
	 * @return WP_Error
	 */
	public static function evaluate_requirements( array $requirements ) {
		$schema = [
			'type'                 => 'object',
			'additionalProperties' => false,
			'properties'           => [
				'version'       => [
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => [
						'pro'  => [
							'type'     => 'string',
							'required' => true,
						],
						'free' => [
							'type'     => 'string',
							'required' => true,
						],
					],
				],
				'ssl'           => [
					'type' => 'boolean',
				],
				'feature-flags' => [
					'type'  => 'array',
					'items' => [
						'type' => 'string',
					],
				],
				'multisite'     => [
					'type' => 'string',
					'enum' => [ 'enabled', 'disabled' ],
				],
				'server'        => [
					'type'       => 'object',
					'properties' => [
						'php'        => [
							'type' => 'string',
						],
						'extensions' => [
							'type'  => 'array',
							'items' => [
								'type' => 'string',
								'enum' => [ 'OpenSSL' ],
							],
						],
					],
				],
			],
		];

		if ( ITSEC_Core::is_development() ) {
			$valid_requirements = rest_validate_value_from_schema( $requirements, $schema );

			if ( is_wp_error( $valid_requirements ) ) {
				return $valid_requirements;
			}
		}

		$error = new WP_Error();

		foreach ( $requirements as $kind => $requirement ) {
			switch ( $kind ) {
				case 'version':
					$key     = ITSEC_Core::is_pro() ? 'pro' : 'free';
					$version = $requirement[ $key ];

					if ( version_compare( ITSEC_Core::get_plugin_version(), $version, '<' ) ) {
						$error->add(
							'version',
							sprintf( __( 'You must be running at least version %s of iThemes Security.', 'better-wp-security' ), $version )
						);
					}

					break;
				case 'ssl':
					if ( $requirement !== is_ssl() ) {
						$error->add(
							'ssl',
							$requirement ? __( 'Your site must support SSL.', 'better-wp-security' ) : __( 'Your site must not support SSL.', 'better-wp-security' )
						);
					}
					break;
				case 'feature-flags':
					foreach ( $requirement as $flag ) {
						if ( ! ITSEC_Lib_Feature_Flags::is_enabled( $flag ) ) {
							$error->add(
								'feature-flags',
								sprintf(
									__( 'The \'%s\' feature flag must be enabled.', 'better-wp-security' ),
									( ITSEC_Lib_Feature_Flags::get_flag_config( $flag )['title'] ?? $flag ) ?: $flag
								)
							);
						}
					}
					break;
				case 'multisite':
					if ( $requirement === 'enabled' && ! is_multisite() ) {
						$error->add(
							'multisite',
							__( 'Multisite must be enabled.', 'better-wp-security' )
						);
					} elseif ( $requirement === 'disabled' && is_multisite() ) {
						$error->add(
							'multisite',
							__( 'Multisite is not supported.', 'better-wp-security' )
						);
					}
					break;
				case 'server':
					$info = self::get_requirements_info();

					if ( isset( $requirement['php'] ) && version_compare( $info['server']['php'], $requirement['php'], '<' ) ) {
						$error->add( 'server', sprintf( __( 'You must be running PHP version %s or later.', 'better-wp-security' ), $requirement['php'] ) );
					}

					$missing = array_filter( $requirement['extensions'] ?? [], function ( $extension ) use ( $info ) {
						return empty( $info['server']['extensions'][ $extension ] );
					} );

					if ( $missing ) {
						if ( count( $missing ) === 1 ) {
							$message = sprintf( __( 'The %s PHP extension is required.', 'better-wp-security' ), ITSEC_Lib::first( $missing ) );
						} else {
							$message = wp_sprintf(
								_n(
									'The following PHP extension is required: %l.',
									'The following PHP extensions are required: %l.',
									count( $missing ),
									'better-wp-security'
								),
								$missing
							);
						}

						$error->add( 'server', $message );
					}
					break;
			}
		}

		return $error;
	}

	/**
	 * Converts a JSON Schema to a WP-CLI synopsis.
	 *
	 * @param array $schema
	 *
	 * @return array
	 */
	public static function convert_schema_to_cli_synopsis( array $schema ) {
		$synopsis = [];

		$required = isset( $schema['required'] ) ? $schema['required'] : [];

		if ( isset( $schema['properties'] ) ) {
			foreach ( $schema['properties'] as $property => $config ) {
				$param = [
					'name' => $property,
				];

				if ( 'boolean' === $config['type'] ) {
					$param['type'] = 'flag';
				} else {
					$param['type'] = 'assoc';
				}

				if ( array_key_exists( 'default', $config ) ) {
					$param['default'] = $config['default'];
				}

				if ( isset( $config['enum'] ) ) {
					$param['options'] = $config['enum'];
				}

				if ( ( ! isset( $config['required'] ) || true !== $config['required'] ) && ! in_array( $property, $required, true ) ) {
					$param['optional'] = true;
				}

				if ( isset( $config['description'] ) ) {
					$param['description'] = $config['description'];
				}

				$synopsis[] = $param;
			}
		}

		if ( ! empty( $schema['additionalProperties'] ) ) {
			$synopsis[] = [
				'type' => 'generic',
			];
		}

		return $synopsis;
	}

	/**
	 * Decode a string with URL-safe Base64.
	 *
	 * @param string $input A Base64 encoded string
	 *
	 * @return string A decoded string
	 */
	public static function url_safe_b64_decode( $input ) {
		$remainder = strlen( $input ) % 4;
		if ( $remainder ) {
			$padlen = 4 - $remainder;
			$input  .= str_repeat( '=', $padlen );
		}

		return base64_decode( strtr( $input, '-_', '+/' ) );
	}

	/**
	 * Encode a string with URL-safe Base64.
	 *
	 * @param string $input The string you want encoded
	 *
	 * @return string The base64 encode of what you passed in
	 */
	public static function url_safe_b64_encode( $input ) {
		return str_replace( '=', '', strtr( base64_encode( $input ), '+/', '-_' ) );
	}

	/**
	 * Compares the WordPress version with the given version.
	 *
	 * @param string $version   The version to compare with.
	 * @param string $operator  The operator.
	 * @param bool   $allow_dev Whether to treat dev versions as stable.
	 *
	 * @return bool
	 */
	public static function wp_version_compare( $version, $operator, $allow_dev = true ) {
		global $wp_version;

		if ( $allow_dev ) {
			list( $wp_version ) = explode( '-', $wp_version );
		}

		return version_compare( $wp_version, $version, $operator );
	}

	/**
	 * Checks if the WordPress version is at least the given version.
	 *
	 * @param string $version   The version to check WP for.
	 * @param bool   $allow_dev Whether to treat dev versions as stable.
	 *
	 * @return bool
	 */
	public static function is_wp_version_at_least( $version, $allow_dev = true ) {
		return static::wp_version_compare( $version, '>=', $allow_dev );
	}

	/**
	 * Gets the WordPress login URL.
	 *
	 * @param string $action   A particular login action to use.
	 * @param string $redirect Where to redirect the user to after login.
	 * @param string $scheme   The scheme to use. Accepts `login_post` for form submissions.
	 *
	 * @return string
	 */
	public static function get_login_url( $action = '', $redirect = '', $scheme = 'login' ) {
		if ( 'login_post' === $scheme || ( $action && 'login' !== $action ) ) {
			$url = 'wp-login.php';

			if ( $action ) {
				$url = add_query_arg( 'action', urlencode( $action ), $url );
			}

			if ( $redirect ) {
				$url = add_query_arg( 'redirect_to', urlencode( $redirect ), $url );
			}

			$url = site_url( $url, $scheme );
		} else {
			$url = wp_login_url( $redirect );

			if ( $action ) {
				$url = add_query_arg( 'action', urlencode( $action ), $url );
			}
		}

		if ( function_exists( 'is_wpe' ) && is_wpe() ) {
			$url = add_query_arg( 'wpe-login', 'true', $url );
		}

		return apply_filters( 'itsec_login_url', $url, $action, $redirect, $scheme );
	}

	/**
	 * Extends a service definition, ignoring if the service has been frozen.
	 *
	 * @param \iThemesSecurity\Strauss\Pimple\Container $c
	 * @param string            $id
	 * @param callable          $extend
	 *
	 * @return bool
	 */
	public static function extend_if_able( \iThemesSecurity\Strauss\Pimple\Container $c, string $id, callable $extend ): bool {
		try {
			$c->extend( $id, $extend );

			return true;
		} catch ( \iThemesSecurity\Strauss\Pimple\Exception\FrozenServiceException $e ) {
			return false;
		}
	}

	/**
	 * Resolve JSON Schema refs.
	 *
	 * @param array $schema
	 *
	 * @return array
	 */
	public static function resolve_schema_refs( array $schema ): array {
		if ( isset( $schema['definitions'] ) ) {
			array_walk( $schema, [ static::class, 'resolve_ref' ], $schema['definitions'] );
		}

		return $schema;
	}

	/**
	 * Resolves $ref entries at any point in the config.
	 *
	 * Currently, only a simplified form of JSON Pointers are supported where `/` is the only
	 * allowed control character.
	 *
	 * Additionally, the `$ref` keyword must start with `#/definitions`.
	 *
	 * @param mixed  $value       The incoming value.
	 * @param string $key         The array key.
	 * @param array  $definitions The shared definitions.
	 */
	private static function resolve_ref( &$value, $key, $definitions ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		if ( isset( $value['$ref'] ) ) {
			$ref   = str_replace( '#/definitions/', '', $value['$ref'] );
			$value = \ITSEC_Lib::array_get( $definitions, $ref, null, '/' );

			return;
		}

		array_walk( $value, [ static::class, 'resolve_ref' ], $definitions );
	}

	/**
	 * Generates a v4 UUID using a CSPRNG.
	 *
	 * @return string
	 */
	public static function generate_uuid4(): string {
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0x0fff ) | 0x4000,
			wp_rand( 0, 0x3fff ) | 0x8000,
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff ),
			wp_rand( 0, 0xffff )
		);
	}

	public static function recursively_json_serialize( $value ) {
		if ( $value instanceof JsonSerializable ) {
			return $value->jsonSerialize();
		}

		if ( is_array( $value ) ) {
			foreach ( $value as $k => $v ) {
				$value[ $k ] = self::recursively_json_serialize( $v );
			}
		}

		return $value;
	}
}
