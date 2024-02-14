<?php

use iThemesSecurity\Contracts\Runnable;

class ITSEC_Core_Admin implements Runnable {

	public function run() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_notices' ) );
		add_action( 'itsec_dashboard_enqueue_scripts', array( $this, 'enqueue_dashboard_notices_integration' ) );

		add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 9999 );
		add_action( 'admin_footer', array( $this, 'render_notices_root' ) );

		if ( ! ITSEC_Core::is_pro() ) {
			add_filter( 'itsec_meta_links', array( $this, 'add_plugin_meta_links' ) );
		}

		add_filter( 'stellarwp/telemetry/optin_args', [ $this, 'customize_optin' ], 10, 2 );
		add_filter( 'debug_information', [ $this, 'add_site_health_info' ] );
	}

	public function enqueue_admin_notices() {
		if ( $this->should_render_admin_notices() ) {
			wp_enqueue_script( 'itsec-core-admin-notices' );
			wp_enqueue_style( 'itsec-core-admin-notices' );
		}

		global $pagenow;
	}

	public function enqueue_dashboard_notices_integration() {
		wp_enqueue_script( 'itsec-core-admin-notices-dashboard-admin-bar' );
		wp_enqueue_style( 'itsec-core-admin-notices-dashboard-admin-bar' );
		wp_enqueue_style( 'itsec-core-admin-notices' );
	}

	public function render_notices_root() {
		if ( $this->should_render_admin_notices() ) {
			echo '<div id="itsec-admin-notices-root"></div>';
		}
	}

	/**
	 * Register the admin bar menu.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public function admin_bar( $wp_admin_bar ) {
		if ( is_admin() && $this->should_render_admin_notices() ) {
			$wp_admin_bar->add_node( array(
				'title' => __( 'Security', 'better-wp-security' ),
				'id'    => 'itsec_admin_bar_menu',
			) );
		}
	}

	private function should_render_admin_notices() {
		return ITSEC_Core::current_user_can_manage() && ! ITSEC_Modules::get_setting( 'global', 'hide_admin_bar' );
	}

	/**
	 * Adds links to the plugin row meta
	 *
	 * @since 4.0
	 *
	 * @param array $meta Existing meta
	 *
	 * @return array
	 */
	public function add_plugin_meta_links( $meta ) {
		$link   = ITSEC_Core::get_tracking_link( 'https://ithemes.com/security/', 'pluginspage', 'link' );
		$meta[] = '<a href="' . $link . '" target="_blank" rel="noopener noreferrer">' . __( 'Get Support', 'better-wp-security' ) . '</a>';

		return $meta;
	}

	public function customize_optin( $args, $slug ) {
		if ( $slug === 'solid-security' ) {
			$args['plugin_logo']        = plugins_url( '/core/packages/style-guide/src/assets/logo/color.svg', ITSEC_Core::get_plugin_file() );
			$args['plugin_logo_width']  = 260;
			$args['plugin_logo_height'] = 40;
			$args['plugin_logo_alt']    = __( 'Solid Security Logo', 'better-wp-security' );
			$args['plugin_name']        = ITSEC_Core::get_plugin_name();
			$args['permissions_url']    = 'https://go.solidwp.com/opt-in-usage-sharing';
			$args['tos_url']            = 'https://go.solidwp.com/solid-security-terms-usage-modal';
			$args['privacy_url']        = 'https://go.solidwp.com/solid-security-privacy-usage-modal';
			$args['heading']            = __( 'Help us improve Solid Security.', 'better-wp-security' );
			$args['intro']              = $this->get_telemetry_intro();
		}

		return $args;
	}

	public function add_site_health_info( $info ) {
		$settings_by_module = [];
		$settings_count     = 0;

		foreach ( ITSEC_Modules::get_active_modules_to_run() as $module ) {
			$config = ITSEC_Modules::get_config( $module );

			if ( $to_report = $config->get_telemetry_settings() ) {
				$settings = ITSEC_Modules::get_settings( $module );

				foreach ( $to_report as $setting => $type ) {
					$value = $settings[ $setting ];

					if ( $type === 'count' ) {
						$value = count( $settings[ $setting ] );
					} elseif ( $type === 'not-empty' ) {
						if ( is_array( $value ) ) {
							$value = (bool) array_filter( $value );
						} else {
							$value = (bool) $value;
						}
					}

					$settings_count ++;
					$settings_by_module[ $module ][ $setting ] = $value;
				}
			}
		}

		$info['solid-security'] = [
			'label'  => __( 'Solid Security', 'better-wp-security' ),
			'fields' => [
				'pro'           => [
					'label' => __( 'Install Type', 'better-wp-security' ),
					'value' => ITSEC_Core::get_install_type() === 'pro' ? __( 'Pro', 'better-wp-security' ) : __( 'Basic', 'better-wp-security' ),
					'debug' => ITSEC_Core::get_install_type(),
				],
				'initial_build' => [
					'label' => __( 'Initial Build', 'better-wp-security' ),
					'value' => ITSEC_Modules::get_setting( 'global', 'initial_build' ),
					'debug' => ITSEC_Modules::get_setting( 'global', 'initial_build' ),
				],
				'activated'     => [
					'label' => __( 'Activated', 'better-wp-security' ),
					'value' => gmdate( get_option( 'date_format' ), ITSEC_Modules::get_setting( 'global', 'activation_timestamp' ) ),
					'debug' => gmdate( 'Y-m-d H:i:s', ITSEC_Modules::get_setting( 'global', 'activation_timestamp' ) ),
				],
				'patchstack'    => [
					'label' => __( 'Patchstack', 'better-wp-security' ),
					'value' => ITSEC_Core::has_patchstack() ? __( 'Yes', 'better-wp-security' ) : __( 'No', 'better-wp-security' ),
					'debug' => ITSEC_Core::has_patchstack(),
				],
				'modules'       => [
					'label' => __( 'Active Features', 'better-wp-security' ),
					'value' => implode( ', ', array_map( function ( $module ) {
						return ITSEC_Modules::get_labels( $module )['title'];
					}, ITSEC_Modules::get_active_modules() ) ),
					'debug' => ITSEC_Modules::get_active_modules(),
				],
				'settings'      => [
					'label' => __( 'Configured Settings', 'better-wp-security' ),
					'value' => $settings_count,
					'debug' => $settings_by_module,
				],
			],
		];

		return $info;
	}

	private function get_telemetry_intro(): string {
		/* translators: 1. The user's name. */
		return sprintf(
			esc_html__(
				'Hi %s! At Solid, we’re committed to delivering top-notch services, and your valuable insights play a crucial role in helping us achieve that goal.
				We’re excited to invite you to participate in our opt-in program, designed to enhance your experience with Solid Security and contribute to the continuous improvement of StellarWP Products.
				By opting in, you allow our teams to access certain data related to your website data. This information will be used responsibly to gain insights into your preferences and patterns, enabling us to tailor our services and products to better meet your needs.
				Rest assured, we take data privacy seriously, and our usage of your information will adhere to the highest standards, respecting all relevant regulations and guidelines. Your trust means the world to us, and we are committed to maintaining the confidentiality and security of your data.
				To join this initiative and be part of shaping the future of Solid Security and StellarWP, simply click “Allow & Continue” below.',
				'better-wp-security'
			),
			wp_get_current_user()->display_name
		);
	}
}
