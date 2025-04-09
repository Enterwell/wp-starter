<?php

final class ITSEC_Security_Check_Pro_Privacy {
	public function __construct() {
		add_filter( 'itsec_get_privacy_policy_for_sharing', array( $this, 'get_privacy_policy_for_sharing' ) );
	}

	public function get_privacy_policy_for_sharing( $policy ) {
		/* Translators: 1: Link to SolidWP's privacy policy */
		$policy .= "<p class=\"privacy-policy-tutorial\">" . sprintf( wp_kses( __( 'When running Security Check, solidwp.com will be contacted as part of a process to determine if the site supports TLS/SSL requests. No personal data is sent to solidwp.com as part of this process. Requests to solidwp.com include the site\'s URL. For solidwp.com privacy policy details, please see the <a href="%1$s">SolidWP Privacy Policy</a>.', 'better-wp-security' ), array( 'a' => array( 'href' => array() ) ) ), 'https://go.solidwp.com/solid-privacy-policy' ) . "</p>\n";

		return $policy;
	}
}
new ITSEC_Security_Check_Pro_Privacy();
