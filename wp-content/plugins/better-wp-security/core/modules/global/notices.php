<?php

class ITSEC_Admin_Notice_Network_Brute_Force_Promo implements ITSEC_Admin_Notice {
	public function get_id() {
		return 'network-brute-force-promo';
	}

	public function get_title() {
		return '';
	}

	public function get_message() {
		return esc_html__( 'New! Take your site security to the next level by activating iThemes Brute Force Network Protection.', 'better-wp-security' );
	}

	public function get_severity() {
		return self::S_INFO;
	}

	public function get_meta() {
		return array();
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
		return true;
	}

	public function get_actions() {
		$url = ITSEC_Core::get_settings_module_url( 'network-brute-force' );

		if ( ! ITSEC_Modules::is_active( 'network-brute-force' ) ) {
			$url = add_query_arg( 'enable', 'network-brute-force', $url );
			$url = wp_nonce_url( $url, 'itsec-enable-network-brute-force', 'itsec-enable-nonce' );
		}

		return array(
			'register' => new ITSEC_Admin_Notice_Action_Link(
				$url,
				esc_html__( 'Get Free API Key', 'better-wp-security' ),
				ITSEC_Admin_Notice_Action::S_PRIMARY
			),
		);
	}
}

if ( ITSEC_Core::is_temp_disable_modules_set() ) {
	ITSEC_Lib_Admin_Notices::register(
		new ITSEC_Admin_Notice_Managers_Only(
			new ITSEC_Admin_Notice_Static(
				'disable-modules', esc_html__( 'The ITSEC_DISABLE_MODULES define is set. All iThemes Security protections are disabled. Please make the necessary settings changes and remove the define as quickly as possible.', 'better-wp-security' ), '', ITSEC_Admin_Notice::S_WARN
			)
		)
	);
}

if ( ! ITSEC_Modules::is_active( 'network-brute-force' ) || ! ITSEC_Modules::get_setting( 'network-brute-force', 'api_secret' ) ) {
	ITSEC_Lib_Admin_Notices::register(
		new ITSEC_Admin_Notice_Globally_Dismissible(
			new ITSEC_Admin_Notice_Managers_Only(
				new ITSEC_Admin_Notice_Network_Brute_Force_Promo()
			)
		)
	);
}
