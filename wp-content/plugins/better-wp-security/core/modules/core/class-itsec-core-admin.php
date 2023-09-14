<?php

class ITSEC_Core_Admin {
	public function run() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_notices' ) );
		add_action( 'itsec_dashboard_enqueue_scripts', array( $this, 'enqueue_dashboard_notices_integration' ) );

		add_action( 'admin_bar_menu', array( $this, 'admin_bar' ), 9999 );
		add_action( 'admin_footer', array( $this, 'render_notices_root' ) );

		if ( ! ITSEC_Core::is_pro() ) {
			add_filter( 'itsec_meta_links', array( $this, 'add_plugin_meta_links' ) );
		}

		add_action( 'after_plugin_row', array( $this, 'show_plugins_page_rebranding_banner' ), 10, 1 );
		add_action( "wp_ajax_itsec_dismiss_rebranding_plugins_banner", array( $this, 'process_rebranding_dismissal' ) );
	}

	public function enqueue_admin_notices() {
		if ( $this->should_render_admin_notices() ) {
			wp_enqueue_script( 'itsec-core-admin-notices' );
			wp_enqueue_style( 'itsec-core-admin-notices' );
		}

		global $pagenow;

		if ( $pagenow === 'plugins.php' ) {
			$nonce = wp_create_nonce( 'itsec-rebranding-nonce' );
			wp_enqueue_script( 'itsec-plugins-rebranding-notice', plugin_dir_url( __FILE__ ) . 'js/plugins-rebranding-notice.js', array( 'jquery', 'wp-util' ), 2 );
			wp_localize_script( 'itsec-plugins-rebranding-notice', 'ITSECAdminNotices', array(
				'nonce' => $nonce,
			) );
		}
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

	/**
	 * Adds a small banner to the plugins.php admin page
	 *
	 * @param $plugin_file
	 *
	 * @return void
	 */
	public function show_plugins_page_rebranding_banner( $plugin_file ) {
		if ( ! get_site_option('itsec_dismissed_rebranding_plugin_notice') ) {
			$branding_link = ITSEC_Core::is_pro()
				? 'https://go.solidwp.com/security-notice-ithemes-becoming-solidwp'
				: 'https://go.solidwp.com/security-free-notice-ithemes-becoming-solidwp'
			;
			$plugin_file_array = explode( '/', $plugin_file );
			$core_plugin_file_array = explode( '/', \ITSEC_Core::get_plugin_file() );
			if ( end( $plugin_file_array ) === end( $core_plugin_file_array ) ) {
				echo '
					<tr class="itsec-plugin-rebranding-tr">
					<td colspan="4" style="padding: 20px 40px; background: #f0f6fc; border-left: 4px solid #72aee6; box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.1);">
					<div class="itsec-plugin-rebranding-container">
						<h4 class="itsec-rebranding-header">' . __( "iThemes Security is Becoming Solid Security: More Security, Better UIs, and Improved Features", "better-wp-security" ) . '</h4>
						<p>' . __( "We have been working hard for almost a year to bring you incredible new features in the form of our new and improved brand: SolidWP.", "better-wp-security" ) . '</p>
						<a href="' . $branding_link . '">' . __( 'Learn More About Solid Security and SolidWP', 'better-wp-security') . '</a>
						<span class="itsec-plugin-rebranding-notice-dismiss"></span>
					</div>
					</td></tr>';
			}
		}
	}

	public function process_rebranding_dismissal() {
		if ( ! isset( $_POST['notice_id'], $_POST['itsec_action'], $_POST['nonce'] ) ) {
			return new WP_Error( 'itsec-admin-notices.invalid-request-format', esc_html__( 'Invalid request format', 'better-wp-security' ) );
		}

		if ( ! wp_verify_nonce( $_POST['nonce'], 'itsec-rebranding-nonce' ) ) {
			return new WP_Error( 'itsec-admin-notices.invalid-nonce', esc_html__( 'Request Expired. Please refresh and try again.', 'better-wp-security' ) );
		}

		$result = update_site_option( 'itsec_dismissed_rebranding_plugin_notice', true );

		if ( $result ) {
			return wp_send_json_success();
		} else {
			return new WP_Error( 'itsec-admin-notices.updating_site_option_failed', esc_html__( 'Could not dismiss action.', 'better-wp-security' ) );
		}
	}

}
