<?php

use iThemesSecurity\Contracts\Runnable;

class ITSEC_Admin_Notices implements Runnable {
	const ACTION = 'itsec-admin-notice';

	/** @var WP_Error[] */
	private $errors = array();

	public function run() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'handle_ajax' ) );

		if ( isset( $_GET['action'] ) && self::ACTION === $_GET['action'] ) {
			add_action( 'admin_init', array( $this, 'handle_admin_action' ) );
		}
	}

	public function rest_api_init() {
		require_once( dirname( __FILE__ ) . '/class-rest-core-admin-notices-controller.php' );

		$controller = new ITSEC_REST_Core_Admin_Notices_Controller();
		$controller->register_routes();
	}

	public function display_notices() {
		foreach ( $this->errors as $error ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . $error->get_error_message() . '</p></div>';
		}

		$hide = array( 'dashboard_page_itsec-dashboard', 'index_page_itsec-dashboard' );

		if ( in_array( get_current_screen()->id, $hide, true ) ) {
			return;
		}

		ITSEC_Lib::load( 'admin-notices' );
		$notices = ITSEC_Lib_Admin_Notices::get_notices();

		if ( ! $notices ) {
			return;
		}

		$nonce = wp_create_nonce( self::ACTION );

		wp_enqueue_script( 'itsec-admin-notices', plugin_dir_url( __FILE__ ) . 'js/admin-notices.js', array( 'jquery', 'wp-util' ), 2 );
		wp_localize_script( 'itsec-admin-notices', 'ITSECAdminNotices', array(
			'nonce' => $nonce,
		) );

		foreach ( $notices as $notice ) {
			$data    = 'data-id="' . esc_attr( $notice->get_id() ) . '"';
			$classes = array(
				'itsec-notice',
				'notice',
				'notice-' . esc_attr( $notice->get_severity() ),
			);

			foreach ( $notice->get_actions() as $slug => $action ) {
				if ( ITSEC_Admin_Notice_Action::S_CLOSE === $action->get_style() ) {
					$classes[] = 'is-dismissible';
					$data      .= ' data-close="' . esc_attr( $slug ) . '"';
					break;
				}
			}

			echo '<div class="' . implode( ' ', $classes ) . '"' . $data . '>';
			$html = $this->format_message( trim( $notice->get_title() . ' ' . $notice->get_message() ), $notice );

			foreach ( $notice->get_actions() as $slug => $action ) {
				if ( ITSEC_Admin_Notice_Action::S_CLOSE === $action->get_style() ) {
					continue;
				}

				$html .= ' ';

				if ( $action->get_uri() ) {
					switch ( $action->get_style() ) {
						case ITSEC_Admin_Notice_Action::S_BUTTON:
							$class = 'button';
							break;
						case ITSEC_Admin_Notice_Action::S_PRIMARY:
							$class = 'button button-primary';
							break;
						default:
							$class = '';
							break;
					}

					$href = add_query_arg( array(
						'action'       => self::ACTION,
						'notice_id'    => $notice->get_id(),
						'itsec_action' => $slug,
						'nonce'        => $nonce,
					), $action->get_uri() );

					$html .= '<a href="' . esc_url( $href ) . '" class="' . esc_attr( $class ) . '">';
					$html .= $action->get_title();
					$html .= '</a>';
				} else {
					switch ( $action->get_style() ) {
						case ITSEC_Admin_Notice_Action::S_BUTTON:
							$class = 'button';
							break;
						case ITSEC_Admin_Notice_Action::S_PRIMARY:
							$class = 'button button-primary';
							break;
						case ITSEC_Admin_Notice_Action::S_LINK:
							$class = 'button-link';
							break;
						default:
							$class = '';
							break;
					}

					$html .= '<button data-action="' . esc_attr( $slug ) . '" class="' . esc_attr( $class ) . '">';
					$html .= $action->get_title();
					$html .= '</button>';
				}
			}

			echo wpautop( $html );

			echo '</div>';
		}
	}

	/**
	 * Formats a message replacing action variables.
	 *
	 * @param string             $message
	 * @param ITSEC_Admin_Notice $notice
	 *
	 * @return string
	 */
	private function format_message( $message, $notice ) {
		foreach ( $notice->get_actions() as $slug => $action ) {
			if ( ! $action->get_uri() ) {
				continue;
			}

			$message = str_replace( "{{ \${$slug} }}", $action->get_uri(), $message );
		}

		return $message;
	}

	public function handle_ajax() {
		$error = $this->handle_action( $_POST );

		if ( is_wp_error( $error ) ) {
			wp_send_json_error( $error );
		}

		wp_send_json_success();
	}

	public function handle_admin_action() {
		$error = $this->handle_action( $_GET );

		if ( is_wp_error( $error ) ) {
			$this->errors[] = $error;
		}
	}

	private function handle_action( $request ) {
		if ( ! isset( $request['notice_id'], $request['itsec_action'], $request['nonce'] ) ) {
			return new WP_Error( 'itsec-admin-notices.invalid-request-format', esc_html__( 'Invalid request format.', 'better-wp-security' ) );
		}

		if ( ! wp_verify_nonce( $request['nonce'], self::ACTION ) ) {
			return new WP_Error( 'itsec-admin-notices.invalid-nonce', esc_html__( 'Request Expired. Please refresh and try again.', 'better-wp-security' ) );
		}

		ITSEC_Lib::load( 'admin-notices' );
		$notices = ITSEC_Lib_Admin_Notices::get_notices( new ITSEC_Admin_Notice_Context(
			wp_get_current_user(),
			wp_doing_ajax() ? ITSEC_Admin_Notice_Context::AJAX : ITSEC_Admin_Notice_Context::ADMIN_ACTION
		) );

		$notice = null;
		foreach ( $notices as $maybe_notice ) {
			if ( (string) $maybe_notice->get_id() === $request['notice_id'] ) {
				$notice = $maybe_notice;
				break;
			}
		}

		if ( ! $notice ) {
			return new WP_Error( 'itsec-admin-notices.invalid-notice', esc_html__( 'Notice not found.', 'better-wp-security' ) );
		}

		$actions = $notice->get_actions();

		if ( ! isset( $actions[ $request['itsec_action'] ] ) ) {
			return new WP_Error( 'itsec-admin-notices.invalid-action', esc_html__( 'Action not found.', 'better-wp-security' ) );
		}

		$data = $request;

		unset( $data['notice_id'], $data['itsec_action'], $data['nonce'], $data['action'] );

		$error = $actions[ $request['itsec_action'] ]->handle( wp_get_current_user(), $data );

		if ( is_wp_error( $error ) ) {
			return $error;
		}

		return null;
	}
}
