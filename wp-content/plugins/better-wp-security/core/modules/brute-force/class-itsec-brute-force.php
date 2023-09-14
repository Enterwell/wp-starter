<?php

use iThemesSecurity\Lib\Lockout\Host_Context;

class ITSEC_Brute_Force {

	private $settings;

	function run() {

		$this->settings = ITSEC_Modules::get_settings( 'brute-force' );

		add_filter( 'authenticate', array( $this, 'authenticate' ), 10000, 2 ); // Set a very late priority so that we run after actual authentication takes place.
		add_filter( 'itsec_lockout_modules', array( $this, 'itsec_lockout_modules' ) );
		add_filter( 'jetpack_get_default_modules', array( $this, 'jetpack_get_default_modules' ) ); //disable jetpack protect via Geoge Stephanis

	}

	/**
	 * Handle brute force lockout conditions when the site is handling authentication.
	 *
	 * @since 4.0
	 *
	 * @param object $user     user or wordpress error
	 * @param string $username username attempted
	 *
	 * @return WP_User|WP_Error|null
	 */
	public function authenticate( $user, $username = '' ) {
		/** @var ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout;

		if ( is_wp_error( $user ) && $user->get_error_codes() == array( 'empty_username', 'empty_password' ) ) {
			// This is not an authentication attempt. It is simply the login page loading.
			return $user;
		}

		if ( is_wp_error( $user ) || null == $user ) {
			// Failed authentication.

			$details = ITSEC_Lib::get_login_details();
			$SERVER  = ITSEC_Lib::get_server_snapshot();

			if ( 'admin' === $username && $this->settings['auto_ban_admin'] ) {
				ITSEC_Log::add_notice( 'brute_force', 'auto-ban-admin-username', compact( 'details', 'user', 'username', 'SERVER' ) );

				$context = new Host_Context( 'brute_force_admin_user' );
				$context->set_login_username( 'admin' );
				$itsec_lockout->do_lockout( $context );
			} else {
				$context = new Host_Context( 'brute_force' );
				$user_id = false;
				$code    = 'invalid-login';

				if ( empty( $username ) ) {
					$itsec_lockout->check_lockout( false, false, 'brute_force' );
				} else {
					ITSEC_Lib::load( 'login' );
					$found_user = ITSEC_Lib_Login::get_user( $username );
					$user_id    = $found_user ? $found_user->ID : 0;

					if ( ! $user_id ) {
						$context->set_login_username( $username );
						$itsec_lockout->check_lockout( false, $username, 'brute_force' );
						$code = "invalid-login::username-{$username}";
					} else {
						$context->set_login_user_id( $user_id );
						$itsec_lockout->check_lockout( $user_id, false, 'brute_force' );
						$code = "invalid-login::user-{$user_id}";
					}
				}

				ITSEC_Log::add_notice( 'brute_force', $code, compact( 'details', 'user', 'username', 'user_id', 'SERVER' ) );

				$itsec_lockout->do_lockout( $context );
			}
		} else {
			// Successful authentication. Check to ensure that they are not locked out.
			$itsec_lockout->check_lockout( $user, false, 'brute_force' );
		}

		return $user;

	}

	/**
	 * Register Brute Force for lockout
	 *
	 * @since 4.0
	 *
	 * @param array $lockout_modules array of lockout modules
	 *
	 * @return array                   array of lockout modules
	 */
	public function itsec_lockout_modules( $lockout_modules ) {

		$lockout_modules['brute_force'] = array(
			'type'   => 'brute_force',
			'reason' => __( 'too many bad login attempts', 'better-wp-security' ),
			'label'  => __( 'Brute Force', 'better-wp-security' ),
			'host'   => $this->settings['max_attempts_host'],
			'user'   => $this->settings['max_attempts_user'],
			'period' => $this->settings['check_period'],
		);

		$lockout_modules['brute_force_admin_user'] = array(
			'type'   => 'brute_force_admin_user',
			'reason' => __( 'user tried to login as "admin."', 'better-wp-security' ),
			'label'  => __( 'Brute Force "admin" Username', 'better-wp-security' ),
			'host'   => 1,
			'user'   => 1,
			'period' => $this->settings['check_period']
		);

		return $lockout_modules;

	}

	/**
	 * Disables the jetpack protect module
	 *
	 * Sent by George Stephanis
	 *
	 * @since 4.5
	 *
	 * @param array $modules array of Jetpack modules
	 *
	 * @return array array of Jetpack modules
	 */
	public function jetpack_get_default_modules( $modules ) {

		return array_diff( $modules, array( 'protect' ) );

	}

}
