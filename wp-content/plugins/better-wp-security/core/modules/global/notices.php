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

class ITSEC_Admin_Notice_Licensed_Hostname_Prompt implements ITSEC_Admin_Notice {
	public function get_id() {
		return 'licensed-hostname-prompt';
	}

	public function get_title() {
		return '';
	}

	public function get_message() {
		return esc_html__( 'iThemes Security Pro couldn\'t verify the license for this site. An active license is required to authenticate the Site Scanner.', 'better-wp-security' );
	}

	public function get_meta() {
		return [];
	}

	public function get_severity() {
		return self::S_WARN;
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
		return true;
	}

	public function get_actions() {
		return [
			'update' => new ITSEC_Admin_Notice_Action_Link(
				ithemes_updater_get_change_licensed_site_url(
					ITSEC_Core::get_settings_page_url()
				),
				esc_html__( 'Update License', 'better-wp-security' ),
				ITSEC_Admin_Notice_Action::S_PRIMARY,
				static function () {
					ITSEC_Modules::set_setting( 'global', 'licensed_hostname_prompt', false );
				}
			)
		];
	}
}

ITSEC_Lib_Admin_Notices::register(
	new ITSEC_Admin_Notice_Remind_Me(
		new ITSEC_Admin_Notice_Managers_Only(
			new class implements ITSEC_Admin_Notice {
				public function get_id() {
					return 'insecure-ip-detection';
				}

				public function get_title() {
					return __( 'Enable Security Check Pro', 'better-wp-security' );
				}

				public function get_message() {
					return __( 'Automatic IP detection is susceptible to IP spoofing attacks.', 'better-wp-security' ) . ' ' .
					       __( 'We recommend enabling Security Check Pro to securely configure IP detection.', 'better-wp-security' ) . ' ' .
					       __( 'Alternatively, manually configure <a href="{{ $configure }}">Proxy Detection</a> based on your server setup.', 'better-wp-security' );
				}

				public function get_meta() {
					return [];
				}

				public function get_severity() {
					return self::S_WARN;
				}

				public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
					return 'automatic' === ITSEC_Modules::get_setting( 'global', 'proxy' );
				}

				public function get_actions() {
					return [
						'enable'    => new ITSEC_Admin_Notice_Action_Callback(
							ITSEC_Admin_Notice_Action::S_PRIMARY,
							__( 'Enable', 'better-wp-security' ),
							function () {
								if ( ! ITSEC_Modules::is_active( 'security-check-pro' ) ) {
									ITSEC_Modules::activate( 'security-check-pro' );
								}

								ITSEC_Modules::set_setting( 'global', 'proxy', 'security-check' );
							}
						),
						'configure' => ITSEC_Admin_Notice_Action_Link::for_route(
							ITSEC_Core::get_settings_module_route( 'global' ) . '#proxy',
							__( 'Manually Configure', 'better-wp-security' )
						),
					];
				}
			}
		),
		WEEK_IN_SECONDS
	)
);


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

if (
	ITSEC_Core::is_licensed() &&
	ITSEC_Modules::get_setting( 'global', 'licensed_hostname_prompt' ) &&
	function_exists( 'ithemes_updater_get_change_licensed_site_url' )
) {
	ITSEC_Lib_Admin_Notices::register(
		new ITSEC_Admin_Notice_Managers_Only(
			new ITSEC_Admin_Notice_Licensed_Hostname_Prompt()
		)
	);
}
