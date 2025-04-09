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

		add_action( 'show_user_profile', array( $this, 'render_profile_fields' ), 9 );
		add_action( 'edit_user_profile', array( $this, 'render_profile_fields' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_profile_scripts' ) );
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

		if ( $onboarded ) {
			$page_refs[] = add_submenu_page( $parent, __( 'Site Scans', 'better-wp-security' ), __( 'Site Scans', 'better-wp-security' ), $capability, 'itsec-site-scan', array( $this, 'show_page' ) );
			$page_refs[] = add_submenu_page( $parent, __( 'Firewall', 'better-wp-security' ), __( 'Firewall', 'better-wp-security' ), $capability, 'itsec-firewall', array( $this, 'show_page' ) );
			$page_refs[] = add_submenu_page( $parent, __( 'Vulnerabilities', 'better-wp-security' ), __( 'Vulnerabilities', 'better-wp-security' ), $capability, 'itsec-vulnerabilities', array( $this, 'show_page' ) );
			$page_refs[] = add_submenu_page( $parent, __( 'User Security', 'better-wp-security' ), __( 'User Security', 'better-wp-security' ), $capability, 'itsec-user-security', array( $this, 'show_page' ) );
		}

		$page_refs[] = add_submenu_page( $parent, __( 'Solid Security Settings', 'better-wp-security' ), $onboarded ? __( 'Settings', 'better-wp-security' ) : __( 'Setup', 'better-wp-security' ), $capability, 'itsec', array(
			$this,
			'show_page'
		) );

		if ( $onboarded ) {
			$page_refs[] = add_submenu_page( $parent, __( 'Tools', 'better-wp-security' ), __( 'Tools' ), $capability, 'itsec-tools', array( $this, 'show_page' ) );
		}

		$page_refs = apply_filters( 'itsec-admin-page-refs', $page_refs, $capability, array( $this, 'show_page' ), $parent );

		if ( $onboarded ) {
			$page_refs[] = add_submenu_page( $parent, __( 'Solid Security Logs', 'better-wp-security' ), __( 'Logs', 'better-wp-security' ), $capability, 'itsec-logs', array( $this, 'show_page' ) );
		}

		if ( ! ITSEC_Core::is_pro() || ITSEC_Core::is_development() ) {
			$page_refs[] = add_submenu_page( $parent, '', '<span style="color:#7ABEED">' . __( 'Get More Security', 'better-wp-security' ) . '</span>', $capability, 'itsec-go-pro', array( $this, 'show_page' ) );
		}

		if ( defined( 'ITSEC_DEBUG' ) && ITSEC_DEBUG ) {
			$page_refs[] = add_submenu_page( $parent, __( 'Solid Security Debug', 'better-wp-security' ), __( 'Debug', 'better-wp-security' ), $capability, 'itsec-debug', array( $this, 'show_page' ) );
		}

		foreach ( $page_refs as $page_ref ) {
			add_action( "load-$page_ref", array( $this, 'load' ) );
			add_action( "admin_print_scripts-$page_ref", array( $this, 'enqueue' ), 0 );
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

	public function enqueue() {
		foreach ( ITSEC_Modules::get_available_modules() as $module ) {
			$handle = "itsec-{$module}-global";

			if ( wp_script_is( $handle, 'registered' ) ) {
				wp_enqueue_script( $handle );
			}

			if ( wp_style_is( $handle, 'registered' ) ) {
				wp_enqueue_style( $handle );
			}
		}

		ITSEC_Lib::preload_request_for_data_store(
			'ithemes-security/core',
			'receiveIndex',
			'/ithemes-security/v1',
			[ 'context' => 'help' ]
		);

		ITSEC_Lib::preload_request_for_data_store(
			'ithemes-security/modules',
			'receiveModules',
			'/ithemes-security/v1/modules',
			[ 'context' => 'edit', '_embed' => 1 ]
		);
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

	/**
	 * Render the profile fields for managing user security.
	 *
	 * @param WP_User $user
	 */
	public function render_profile_fields( $user ) {
		?>
		<div id="itsec-profile-root" data-user="<?php echo esc_attr( $user->ID ); ?>" data-can-manage="<?php echo esc_attr( ITSEC_Core::current_user_can_manage() ); ?>"></div>
		<noscript>
			<div class="notice notice-warning notice-alt below-h2"><p><?php esc_html_e( 'You must enable JavaScript to manage Solid Security Settings.', 'better-wp-security' ); ?></p></div>
		</noscript>
		<?php
	}

	/**
	 * Enqueues JavaScript for the profile fields manager.
	 *
	 * @return void
	 */
	public function enqueue_profile_scripts() {
		global $pagenow, $user_id;

		if ( $pagenow !== 'profile.php' && $pagenow !== 'user-edit.php' ) {
			return;
		}

		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return;
		}

		$preload_requests = [];
		$preload = ITSEC_Lib::preload_rest_requests( $preload_requests );

		wp_enqueue_script( 'itsec-pages-profile' );
		wp_enqueue_style( 'itsec-pages-profile' );
		wp_add_inline_script(
			'itsec-pages-profile',
			sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload ) )
		);

		$request = new WP_REST_Request( 'GET', '/wp/v2/users/me' );
		$request->set_query_params( [ 'context' => 'edit' ] );
		$response = rest_do_request( $request );

		if ( ! $response->is_error() ) {
			wp_add_inline_script( 'itsec-pages-profile', sprintf(
				"wp.data.dispatch('%s').receiveCurrentUserId( %d );",
				'ithemes-security/core',
				$response->get_data()['id']
			) );
			wp_add_inline_script( 'itsec-pages-profile', sprintf(
				"wp.data.dispatch('%s').receiveUser( %s );",
				'ithemes-security/core',
				wp_json_encode( rest_get_server()->response_to_data( $response, false ) )
			) );
		}

		foreach ( ITSEC_Modules::get_active_modules_to_run() as $module ) {
			$handle = "itsec-{$module}-profile";

			if ( wp_script_is( $handle, 'registered' ) ) {
				wp_enqueue_script( $handle );
			}

			if ( wp_style_is( $handle, 'registered' ) ) {
				wp_enqueue_style( $handle );
			}
		}

		/**
		 * Fires when scripts are enqueued for the User Profile JS code.
		 *
		 * @param WP_User $user
		 */
		do_action( 'itsec_enqueue_profile', $user );
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
