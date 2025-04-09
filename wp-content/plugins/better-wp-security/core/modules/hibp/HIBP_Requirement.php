<?php

namespace iThemesSecurity\Modules\HIBP;

use iThemesSecurity\Lib\Config_Password_Requirement;
use iThemesSecurity\Module_Config;
use iThemesSecurity\User_Groups;
use ITSEC_HIBP_API;

final class HIBP_Requirement extends Config_Password_Requirement {

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
		return ITSEC_HIBP_API::check_breach_count( $password );
	}

	public function validate( $evaluation, $user, array $settings, array $args ) {
		if ( ! $evaluation ) {
			return true;
		}

		if ( ! $user = get_userdata( $user->ID ) ) {
			return true;
		}

		$target = $args['target'] ?? User_Groups\Match_Target::for_user( $user );

		if ( ! $this->matcher->matches( $target, $settings['group'] ) ) {
			return true;
		}

		return esc_html( sprintf( _n( 'This password appeared in a breach %s time. Please choose a new password.', 'This password appeared in a breach %s times. Please choose a new password.', $evaluation, 'better-wp-security' ), number_format_i18n( $evaluation ) ) );
	}

	public function get_reason_message( $evaluation, array $settings ): string {
		$message = _n(
			'Your password was detected %1$s time in password breaches of other websites. Your account hasn\'t been compromised on %2$s, but to keep your account secure, you must update your password now.',
			'Your password was detected %1$s times in password breaches of other websites. Your account hasn\'t been compromised on %2$s, but to keep your account secure, you must update your password now.',
			$evaluation,
			'better-wp-security'
		);

		$link = '<a href="' . esc_attr( home_url( '/' ) ) . '">' . get_bloginfo( 'title', 'display' ) . '</a>';

		$message = esc_html( $message );
		$message = wptexturize( $message );
		$message = sprintf( $message, number_format_i18n( $evaluation ), $link );

		return $message;
	}

	public function is_always_enabled(): bool {
		return false;
	}

	public function should_evaluate_if_not_enabled(): bool {
		return false;
	}

	public function render( \ITSEC_Form $form ) {
		?>
		<tr>
			<th scope="row">
				<label for="itsec-password-requirements-requirement_settings-hibp-group">
					<?php esc_html_e( 'User Group', 'better-wp-security' ); ?>
				</label>
			</th>
			<td>
				<?php $form->add_user_groups( 'group', 'password-requirements', 'requirement_settings.hibp.group' ); ?>
				<br/>
				<label for="itsec-password-requirements-requirement_settings-hibp-group"><?php _e( 'Require users in the selected groups to have passwords that must not appear in a breach.', 'better-wp-security' ); ?></label>
			</td>
		</tr>
		<?php
	}
}

