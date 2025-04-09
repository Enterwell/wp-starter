<?php

namespace iThemesSecurity\Modules\Strong_Passwords;

use iThemesSecurity\Lib\Config_Password_Requirement;
use iThemesSecurity\Module_Config;
use iThemesSecurity\User_Groups;

final class Strength_Requirement extends Config_Password_Requirement {

	/** @var User_Groups\Matcher */
	private $matcher;

	public function __construct( User_Groups\Matcher $matcher, Module_Config $config, string $code ) {
		parent::__construct( $config, $code );
		$this->matcher = $matcher;
	}

	public function is_password_change_required( \WP_User $user, array $settings ): bool {
		return false;
	}

	public function evaluate( string $password, $user ) {
		return $this->get_password_strength( $user, $password );
	}

	public function validate( $evaluation, $user, array $settings, array $args ) {
		if ( (int) $evaluation === 4 ) {
			return true;
		}

		if ( ! $user = get_userdata( $user->ID ) ) {
			return true;
		}

		$target = $args['target'] ?? User_Groups\Match_Target::for_user( $user );

		if ( ! $this->matcher->matches( $target, $settings['group'] ) ) {
			return true;
		}

		return $this->make_error_message();
	}

	public function get_reason_message( $evaluation, array $settings ): string {
		return $this->make_error_message();
	}

	public function is_always_enabled(): bool {
		return false;
	}

	public function should_evaluate_if_not_enabled(): bool {
		return true;
	}

	public function get_meta_key(): string {
		return \ITSEC_Strong_Passwords::STRENGTH_KEY;
	}

	public function render( \ITSEC_Form $form ) {
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
	 * Get the strong password error message according to the given context.
	 *
	 * @return string
	 */
	private function make_error_message(): string {
		return esc_html__( 'Due to site rules, a strong password is required. Please choose a new password that rates as Strong on the meter.', 'better-wp-security' );
	}

	/**
	 * Calculate the strength of a password.
	 *
	 * @param \WP_User|\stdClass $user
	 * @param string             $password
	 *
	 * @return int
	 */
	private function get_password_strength( $user, $password ) {

		$penalty_strings = [
			get_site_option( 'admin_email' )
		];
		$user_properties = [ 'user_login', 'first_name', 'last_name', 'nickname', 'display_name', 'user_email', 'user_url', 'description' ];

		foreach ( $user_properties as $user_property ) {
			if ( isset( $user->$user_property ) ) {
				$penalty_strings[] = $user->$user_property;
			}
		}

		$results = \ITSEC_Lib::get_password_strength_results( $password, $penalty_strings );

		return $results['score'];
	}
}
