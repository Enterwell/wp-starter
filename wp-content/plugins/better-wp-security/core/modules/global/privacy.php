<?php

final class ITSEC_Global_Privacy {
	private $settings;

	public function __construct() {
		$this->settings = ITSEC_Modules::get_settings( 'global' );

		add_filter( 'itsec_get_privacy_policy_for_security_logs', array( $this, 'get_privacy_policy_for_security_logs' ) );
		add_filter( 'itsec_get_privacy_policy_for_retention', array( $this, 'get_privacy_policy_for_retention' ) );
		add_filter( 'itsec_get_privacy_policy_for_cookies', array( $this, 'get_privacy_policy_for_cookies' ) );
	}

	public function get_privacy_policy_for_security_logs( $policy ) {
		$suggested_text = '<strong class="privacy-policy-tutorial">' . __( 'Suggested text:', 'better-wp-security' ) . ' </strong>';

		$retention_days = $this->get_retention_days();

		/* Translators: 1: Number of days that data is retained for */
		$policy .= "<p>$suggested_text " . sprintf( esc_html__( 'The IP address of visitors, user ID of logged in users, and username of login attempts are conditionally logged to check for malicious activity and to protect the site from specific kinds of attacks. Examples of conditions when logging occurs include login attempts, log out requests, requests for suspicious URLs, changes to site content, and password updates. This information is retained for %1$d days.', 'better-wp-security' ), $retention_days ) . "</p>\n";

		return $policy;
	}

	public function get_privacy_policy_for_retention( $policy ) {
		$suggested_text = '<strong class="privacy-policy-tutorial">' . __( 'Suggested text:', 'better-wp-security' ) . ' </strong>';

		$retention_days = $this->get_retention_days();

		/* Translators: 1: Number of days that data is retained for */
		$policy .= "<p>$suggested_text " . sprintf( esc_html__( 'Security logs are retained for %1$d days.', 'better-wp-security' ), $retention_days ) . "</p>\n";

		return $policy;
	}

	private function get_retention_days() {
		if ( 'database' === $this->settings['log_type'] ) {
			return $this->settings['log_rotation'];
		} else if ( 'file' === $this->settings['log_type'] ) {
			return $this->settings['file_log_rotation'];
		} else {
			return max( $this->settings['log_rotation'], $this->settings['file_log_rotation'] );
		}
	}

	public function get_privacy_policy_for_cookies( $policy ) {
		$suggested_text = '<strong class="privacy-policy-tutorial">' . __( 'Suggested text:', 'better-wp-security' ) . ' </strong>';

		$policy .= "<p>$suggested_text " . esc_html__( 'A cookie named “itsec_interstitial_browser” is created to track a user’s login process to implement enhanced security features.', 'better-wp-security' ) . "</p>\n";

		return $policy;
	}
}
new ITSEC_Global_Privacy();
