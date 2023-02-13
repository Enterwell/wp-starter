<?php

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\User_Groups;

final class ITSEC_Strong_Passwords implements Runnable {

	const STRENGTH_KEY = 'itsec-password-strength';

	/** @var User_Groups\Matcher */
	private $matcher;

	/**
	 * ITSEC_Strong_Passwords constructor.
	 *
	 * @param User_Groups\Matcher $matcher
	 */
	public function __construct( User_Groups\Matcher $matcher ) {
		$this->matcher = $matcher;
	}

	public function run() {
		add_action( 'itsec_register_password_requirements', array( $this, 'register_requirements' ) );
		add_action( 'itsec_register_user_group_settings', [ $this, 'register_group_setting' ] );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );
		add_action( 'resetpass_form', array( $this, 'add_scripts_to_wp_login' ) );
		add_action( 'itsec_password_requirements_change_form', array( $this, 'add_scripts_to_wp_login' ) );
		add_filter( 'random_password', [ $this, 'make_random_password_strong' ], 10, 4 );
	}

	/**
	 * Register the Strong Passwords requirement.
	 */
	public function register_requirements() {
		ITSEC_Lib_Password_Requirements::register( 'strength', array(
			'evaluate'                => array( $this, 'evaluate' ),
			'validate'                => array( $this, 'validate' ),
			'reason'                  => array( $this, 'reason' ),
			'meta'                    => self::STRENGTH_KEY,
			'evaluate_if_not_enabled' => true,
			'defaults'                => array(
				'group' => ITSEC_Modules::get_settings_obj( 'user-groups' )->get_groups_for_all_users(),
			),
			'settings_config'         => array( $this, 'get_settings_config' ),
		) );
	}

	public function register_group_setting( User_Groups\Settings_Registry $registry ) {
		if ( ITSEC_Lib_Password_Requirements::is_requirement_enabled( 'strength' ) ) {
			$registry->register( new User_Groups\Settings_Registration( 'password-requirements', 'requirement_settings.strength.group', User_Groups\Settings_Registration::T_MULTIPLE, static function () {
				return [
					'title'       => __( 'Require Strong Passwords', 'better-wp-security' ),
					'description' => __( 'Force users in the group to use strong passwords.', 'better-wp-security' ),
				];
			} ) );
		}
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
			wp_enqueue_script( 'itsec_strong_passwords', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), ITSEC_Core::get_plugin_build() );
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
			wp_enqueue_script( 'itsec_strong_passwords', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), ITSEC_Core::get_plugin_build() );
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

		while ( $tries < 10 && ITSEC_Lib::get_password_strength_results( $password )->score < 4 ) {
			$password = wp_generate_password( $length, $special_chars, $extra_special_chars );
			$tries++;
		}

		add_filter( 'random_password', [ $this, 'make_random_password_strong' ], 10, 4 );

		return $password;
	}

	/**
	 * Provide the reason string displayed to users on the change password form.
	 *
	 * @param $evaluation
	 *
	 * @return string
	 */
	public function reason( $evaluation ) {
		return esc_html__( 'Due to site rules, a strong password is required for your account. Please choose a new password that rates as strong on the meter.', 'better-wp-security' );
	}

	/**
	 * Evaluate the strength of a password.
	 *
	 * @param string  $password
	 * @param WP_User $user
	 *
	 * @return int
	 */
	public function evaluate( $password, $user ) {
		return $this->get_password_strength( $user, $password );
	}

	/**
	 * Validate whether a password strength is acceptable for a given user.
	 *
	 * @param int              $strength
	 * @param WP_User|stdClass $user
	 * @param array            $settings
	 * @param array            $args
	 *
	 * @return bool
	 */
	public function validate( $strength, $user, $settings, $args ) {
		if ( (int) $strength === 4 ) {
			return true;
		}

		if ( ! $user = get_userdata( $user->ID ) ) {
			return true;
		}

		$target = isset( $args['target'] ) ? $args['target'] : User_Groups\Match_Target::for_user( $user );

		if ( ! $this->matcher->matches( $target, $settings['group'] ) ) {
			return true;
		}

		return $this->make_error_message();
	}

	public function get_settings_config() {
		return array(
			'label'       => esc_html__( 'Strong Passwords', 'better-wp-security' ),
			'description' => esc_html__( 'Force users to use strong passwords as rated by the WordPress password meter.', 'better-wp-security' ),
			'render'      => array( $this, 'render_settings' ),
			'sanitize'    => array( $this, 'sanitize_settings' ),
		);
	}

	/**
	 * Render the Settings Page.
	 *
	 * @param ITSEC_Form $form
	 */
	public function render_settings( $form ) {
		?>
		<tr>
			<th scope="row">
				<label for="itsec-password-requirements-requirement_settings-strength-group">
					<?php esc_html_e( 'User Group', 'better-wp-security' ); ?>
				</label>
			</th>
			<td>
				<?php $form->add_user_groups( 'group', 'password-requirements', 'requirement_settings.strength.group' ); ?>
				<br/>
				<label for="itsec-password-requirements-requirement_settings-strength-group"><?php _e( 'Force users in the selected groups to use strong passwords.', 'better-wp-security' ); ?></label>
			</td>
		</tr>
		<?php
	}

	/**
	 * Get a list of the sanitizer rules to apply.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function sanitize_settings( $settings ) {
		return array(
			array( 'user-groups', 'group', esc_html__( 'User Groups for Strong Passwords', 'better-wp-security' ) ),
		);
	}

	/**
	 * Get the strong password error message according to the given context.
	 *
	 * @return string
	 */
	private function make_error_message() {
		$message = __( '<strong>Error</strong>: Due to site rules, a strong password is required. Please choose a new password that rates as <strong>Strong</strong> on the meter.', 'better-wp-security' );

		return wp_kses( $message, array( 'strong' => array() ) );
	}

	/**
	 * Calculate the strength of a password.
	 *
	 * @param WP_User $user
	 * @param string  $password
	 *
	 * @return int
	 */
	private function get_password_strength( $user, $password ) {

		$penalty_strings = array(
			get_site_option( 'admin_email' )
		);
		$user_properties = array( 'user_login', 'first_name', 'last_name', 'nickname', 'display_name', 'user_email', 'user_url', 'description' );

		foreach ( $user_properties as $user_property ) {
			if ( isset( $user->$user_property ) ) {
				$penalty_strings[] = $user->$user_property;
			}
		}

		$results = ITSEC_Lib::get_password_strength_results( $password, $penalty_strings );

		return $results->score;
	}
}
