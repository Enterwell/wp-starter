<?php

class ITSEC_Sync_Connect_Interstitial extends ITSEC_Login_Interstitial {

	/** @var string */
	private $sync_dashboard = 'https://sync.ithemes.com/manage/site/';

	/**  @var ITSEC_Sync_Connect */
	private $sync_connect;

	/**
	 * ITSEC_Sync_Connect_Interstitial constructor.
	 *
	 * @param ITSEC_Sync_Connect $sync_connect
	 */
	public function __construct( ITSEC_Sync_Connect $sync_connect ) {
		$this->sync_connect = $sync_connect;
	}

	public function render( ITSEC_Login_Interstitial_Session $session, array $args ) {

		$user = $session->get_user();

		if ( $user && $this->sync_connect->user_can_install_and_activate( $user ) ) {
			require_once( __DIR__ . '/templates/connect-prompt.php' );
		} else {
			require_once( __DIR__ . '/templates/connect-unauthorized.php' );
		}

	}

	public function pre_render( ITSEC_Login_Interstitial_Session $session ) {
		add_action( 'login_enqueue_scripts', static function () {
			wp_enqueue_style( 'itsec-sync-connect', plugin_dir_url( __FILE__ ) . 'css/connect.css' );
		} );
	}

	public function is_completion_forced( ITSEC_Login_Interstitial_Session $session ) {
		return false;
	}

	public function has_submit() {
		return true;
	}

	public function submit( ITSEC_Login_Interstitial_Session $session, array $post_data ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		$user = $session->get_user();

		if ( ! $user || ! $this->sync_connect->user_can_install_and_activate( $user ) ) {
			return null;
		}

		// If the plugin is not installed, install it
		if ( ! $this->sync_connect->is_plugin_installed() ) {
			$install = $this->sync_connect->install_plugin();

			// Could not install plugin, return error to manually install
			if ( is_wp_error( $install ) ) {
				return new WP_Error(
					'itsec-sync-connect-plugin-install-failure',
					sprintf(
						__( 'The plugin could not be installed. Please manually install the plugin. Error: %s', 'better-wp-security' ),
						implode( ' ', $install->get_error_messages() )
					)
				);
			}
		}

		// Activate the plugin
		if ( ! is_plugin_active( ITSEC_Sync_Connect::PLUGIN_SLUG ) ) {
			$activate = activate_plugin( ITSEC_Sync_Connect::PLUGIN_SLUG );

			// Could not activate plugin, return error to activate or try reinstalling
			if ( is_wp_error( $activate ) ) {
				return new WP_Error(
					'itsec-sync-connect-plugin-activate-failure',
					sprintf(
						__( 'The plugin could not be activated. Please manually activate the plugin. Error: %s', 'better-wp-security' ),
						implode( ' ', $activate->get_error_messages() )
					)
				);
			}
		}

		$token = $session->get_meta( 'itsec_sync_connect_token' );

		if ( ! $token && isset( $post_data['itsec_sync_connect_token'] ) ) {
			$token = $post_data['itsec_sync_connect_token'];
		}

		// Link to iThemes account
		if ( ! $token ) {
			return new WP_Error( 'itsec-sync-connect-plugin-token-missing', __( 'Missing sync connect token.', 'better-wp-security' ) );
		}

		require_once( WP_PLUGIN_DIR . '/ithemes-sync/functions.php' );
		$sync_nonce = Ithemes_Sync_Functions::generate_sync_nonce( 'itsec-connect' );

		$response = $this->sync_connect->send_activation_request( $user->user_login, $token, $sync_nonce );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		require_once( WP_PLUGIN_DIR . '/ithemes-sync/settings.php' );
		$GLOBALS['ithemes-sync-settings']->add_authentication( $response['site_id'], $response['username'], $response['key'], $user->user_login );

		ITSEC_Log::add_notice( 'sync_connect', 'successful_authentication::' . $user->ID, [
			'post_data' => $post_data,
			'site_id'   => $response['site_id'],
		] );

		wp_redirect( $this->sync_dashboard . $response['site_id'] );
		exit;
	}

	/**
	 * Whether the sync interstitial should be shown to the given user.
	 *
	 * We only show this interstitial if it was requested by the user.
	 *
	 * @param WP_User $user
	 * @param bool    $is_requested
	 *
	 * @return bool
	 */
	public function show_to_user( WP_User $user, $is_requested ) {
		return $is_requested;
	}

	public function get_priority() {
		return PHP_INT_MAX;
	}
}
