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

}
