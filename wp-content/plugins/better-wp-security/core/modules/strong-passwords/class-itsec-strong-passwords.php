<?php

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Lib\Password_Requirement;
use iThemesSecurity\User_Groups;

final class ITSEC_Strong_Passwords implements Runnable {

	const STRENGTH_KEY = 'itsec-password-strength';

	/** @var User_Groups\Matcher */
	private $matcher;

	/** @var Password_Requirement */
	private $requirement;

	/**
	 * ITSEC_Strong_Passwords constructor.
	 *
	 * @param User_Groups\Matcher  $matcher
	 * @param Password_Requirement $requirement
	 */
	public function __construct( User_Groups\Matcher $matcher, Password_Requirement $requirement ) {
		$this->matcher     = $matcher;
		$this->requirement = $requirement;
	}

	public function run() {
		add_action( 'itsec_register_password_requirements', [ $this, 'register_requirements' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'add_scripts' ] );
		add_action( 'resetpass_form', [ $this, 'add_scripts_to_wp_login' ] );
		add_action( 'itsec_password_requirements_change_form', [ $this, 'add_scripts_to_wp_login' ] );
		add_filter( 'random_password', [ $this, 'make_random_password_strong' ], 10, 4 );
	}

	/**
	 * Register the Strong Passwords requirement.
	 */
	public function register_requirements() {
		ITSEC_Lib_Password_Requirements::register( $this->requirement );
	}

	/**
	 * Enqueue script to hide the acknowledge weak password checkbox.
	 *
	 * @return void
	 */
	public function add_scripts() {
		global $pagenow;

		if ( 'profile.php' !== $pagenow ) {
			return;
		}

		if ( ! ITSEC_Lib_Password_Requirements::is_requirement_enabled( 'strength' ) ) {
			return;
		}

		$settings = ITSEC_Lib_Password_Requirements::get_requirement_settings( 'strength' );

		if ( $this->matcher->matches( User_Groups\Match_Target::for_user( wp_get_current_user() ), $settings['group'] ) ) {
			wp_enqueue_script( 'itsec_strong_passwords', plugins_url( 'js/script.js', __FILE__ ), [ 'jquery' ], ITSEC_Core::get_plugin_build() );
		}
	}

	/**
	 * On the reset password and login interstitial form, render the Strong Passwords JS to hide the acknowledge weak password checkbox.
	 *
	 * We have to do this in these late actions so we have access to the correct user data.
	 *
	 * @param WP_User $user
	 */
	public function add_scripts_to_wp_login( $user ) {

		if ( ! ITSEC_Lib_Password_Requirements::is_requirement_enabled( 'strength' ) ) {
			return;
		}

		$settings = ITSEC_Lib_Password_Requirements::get_requirement_settings( 'strength' );

		if ( $this->matcher->matches( User_Groups\Match_Target::for_user( $user ), $settings['group'] ) ) {
			wp_enqueue_script( 'itsec_strong_passwords', plugins_url( 'js/script.js', __FILE__ ), [ 'jquery' ], ITSEC_Core::get_plugin_build() );
		}
	}

	/**
	 * Forces `wp_generate_password()` to generate a password that zxcvbn will treat as strong.
	 *
	 * WordPress uses a 24 character password length in its suggested passwords which isn't always long
	 * enough for zxcvbn to think is secure.
	 *
	 * @param string $password            The generated password.
	 * @param int    $length              The length of password to generate.
	 * @param bool   $special_chars       Whether to include standard special characters.
	 * @param bool   $extra_special_chars Whether to include other special characters.
	 *
	 * @return string
	 */
	public function make_random_password_strong( $password, $length = 12, $special_chars = true, $extra_special_chars = false ) {
		// We can't guarantee that the correct number of arguments will be passed to this filter.
		// If we don't have the extra context, bail.
		if ( func_num_args() <= 1 ) {
			return $password;
		}

		if ( $length < 24 || ! $special_chars || ! ITSEC_Lib_Password_Requirements::is_requirement_enabled( 'strength' ) ) {
			return $password;
		}

		remove_filter( 'random_password', [ $this, 'make_random_password_strong' ] );

		$tries = 0;

		while ( $tries < 10 && ITSEC_Lib::get_password_strength_results( $password )['score'] < 4 ) {
			$password = wp_generate_password( $length, $special_chars, $extra_special_chars );
			$tries ++;
		}

		add_filter( 'random_password', [ $this, 'make_random_password_strong' ], 10, 4 );

		return $password;
	}
}
