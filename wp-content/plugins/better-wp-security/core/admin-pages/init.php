<?php


final class ITSEC_Admin_Page_Loader {
	private $page_id;

	public function __construct() {
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'add_admin_pages' ) );
		} else {
			add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		}

		add_action( 'wp_ajax_itsec_logs_page', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_itsec_help_page', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_itsec_debug_page', array( $this, 'handle_ajax_request' ) );
		add_action( 'wp_ajax_itsec-set-user-setting', array( $this, 'handle_user_setting' ) );

		// Filters for validating user settings
		add_filter( 'itsec-user-setting-valid-itsec-settings-view', array( $this, 'validate_view' ), null, 2 );
	}

	public function add_admin_pages() {
		$onboarded  = ITSEC_Core::is_onboarded();
		$capability = ITSEC_Core::get_required_cap();
		$page_refs  = array();

		if ( $onboarded && current_user_can( 'itsec_dashboard_menu' ) ) {
			$parent = 'itsec-dashboard';
			add_menu_page( __( 'Security', 'better-wp-security' ), __( 'Security', 'better-wp-security' ), 'itsec_dashboard_menu', $parent, array( $this, 'show_page' ) );
			$page_refs[] = add_submenu_page( $parent, __( 'Dashboard', 'better-wp-security' ), __( 'Dashboard', 'better-wp-security' ), 'itsec_dashboard_menu', 'itsec-dashboard', array( $this, 'show_page' ) );
		} else {
			$parent = 'itsec';
			add_menu_page( __( 'Setup', 'better-wp-security' ), __( 'Security', 'better-wp-security' ), $capability, $parent, array( $this, 'show_page' ) );
		}

		$page_refs[] = add_submenu_page( $parent, __( 'iThemes Security Settings', 'better-wp-security' ), $onboarded ? __( 'Settings', 'better-wp-security' ) : __( 'Setup', 'better-wp-security' ), $capability, 'itsec', array( $this, 'show_page' ) );

		$page_refs = apply_filters( 'itsec-admin-page-refs', $page_refs, $capability, array( $this, 'show_page' ), $parent );

		if ( $onboarded ) {
			$page_refs[] = add_submenu_page( $parent, __( 'iThemes Security Logs', 'better-wp-security' ), __( 'Logs', 'better-wp-security' ), $capability, 'itsec-logs', array( $this, 'show_page' ) );
		}

		if ( ! ITSEC_Core::is_pro() || ITSEC_Core::is_development() ) {
			$page_refs[] = add_submenu_page( $parent, '', '<span style="color:#7ABEED">' . __( 'Get More Security', 'better-wp-security' ) . '</span>', $capability, 'itsec-go-pro', array( $this, 'show_page' ) );
		}

		if ( defined( 'ITSEC_DEBUG' ) && ITSEC_DEBUG ) {
			$page_refs[] = add_submenu_page( $parent, __( 'iThemes Security Debug', 'better-wp-security' ), __( 'Debug', 'better-wp-security' ), $capability, 'itsec-debug', array( $this, 'show_page' ) );
		}

		foreach ( $page_refs as $page_ref ) {
			add_action( "load-$page_ref", array( $this, 'load' ) );
		}
	}

	private function get_page_id() {
		global $plugin_page;

		if ( isset( $this->page_id ) ) {
			return $this->page_id;
		}

		if ( wp_doing_ajax() ) {
			if ( isset( $_REQUEST['action'] ) && preg_match( '/^itsec_(.+)_page$/', $_REQUEST['action'], $match ) ) {
				$this->page_id = $match[1];
			}
		} elseif ( strpos( $plugin_page, 'itsec-' ) === 0 ) {
			$this->page_id = substr( $plugin_page, 6 );
		} elseif ( strpos( $plugin_page, 'itsec' ) === 0 ) {
			$this->page_id = 'settings';
		}

		if ( ! isset( $this->page_id ) ) {
			$this->page_id = '';
		}

		return $this->page_id;
	}

	public function load() {
		$this->load_file( 'page-%s.php' );
	}

	public function show_page() {
		$page_id = $this->get_page_id();

		if ( 'settings' === $page_id ) {
			$url = network_admin_url( 'admin.php?page=itsec' );
		} else {
			$url = network_admin_url( 'admin.php?page=itsec-' . $this->get_page_id() );
		}

		do_action( 'itsec-page-show', $url );
	}

	public function handle_ajax_request() {
		$this->load_file( 'page-%s.php' );

		do_action( 'itsec-page-ajax' );
	}

	private function load_file( $file ) {
		$id = $this->get_page_id();

		if ( empty( $id ) ) {
			if ( isset( $GLOBALS['pagenow'], $_GET['page'] ) && 'admin.php' === $GLOBALS['pagenow'] && strpos( $_GET['page'], 'itsec-' ) === 0 ) {
				$id = substr( $_GET['page'], 6 );
			} else {
				return;
			}
		}

		$id = str_replace( '_', '-', $id );

		$file = __DIR__ . '/' . sprintf( $file, $id );
		$file = apply_filters( "itsec-admin-page-file-path-$id", $file );

		if ( is_file( $file ) ) {
			require_once( $file );
		}
	}

	public function handle_user_setting() {
		if ( 'itsec-settings-view' !== $_REQUEST['setting'] ) {
			wp_send_json_error();
		}

		$_REQUEST['setting'] = sanitize_title_with_dashes( $_REQUEST['setting'] );

		if ( ! wp_verify_nonce( $_REQUEST['itsec-user-setting-nonce'], 'set-user-setting-' . $_REQUEST['setting'] ) ) {
			wp_send_json_error();
		}

		if ( ! apply_filters( 'itsec-user-setting-valid-' . $_REQUEST['setting'], true, $_REQUEST['value'] ) ) {
			wp_send_json_error();
		}

		if ( false === update_user_meta( get_current_user_id(), $_REQUEST['setting'], $_REQUEST['value'] ) ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	public function validate_view( $valid, $view ) {
		return in_array( $view, array( 'grid', 'list' ) );
	}
}

new ITSEC_Admin_Page_Loader();
