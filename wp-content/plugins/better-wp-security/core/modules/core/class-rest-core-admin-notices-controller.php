<?php

class ITSEC_REST_Core_Admin_Notices_Controller extends WP_REST_Controller {

	public function register_routes() {
		register_rest_route( 'ithemes-security/v1', 'admin-notices', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_items' ),
			'permission_callback' => array( $this, 'get_items_permissions_check' ),
		) );

		register_rest_route( 'ithemes-security/v1', 'admin-notices/(?P<notice>[\w\-\.]+)/(?P<action>[\w\-]+)', array(
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => array( $this, 'update_item' ),
			'permission_callback' => array( $this, 'update_item_permissions_check' ),
		) );

		register_rest_route( 'ithemes-security/v1', 'admin-notices/settings', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( 'ITSEC_Core', 'current_user_can_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( 'ITSEC_Core', 'current_user_can_manage' ),
			),
			'schema' => array( $this, 'get_settings_schema' ),
		) );
	}

	public function get_items_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'not_logged_in', esc_html__( 'You must be logged in to view notices.', 'better-wp-security' ), array(
				'status' => WP_Http::UNAUTHORIZED,
			) );
		}

		return true;
	}

	public function get_items( $request ) {
		ITSEC_Lib::load( 'admin-notices' );

		$prepared = array();
		$context  = new ITSEC_Admin_Notice_Context( wp_get_current_user(), 'rest' );

		foreach ( ITSEC_Lib_Admin_Notices::get_notices( $context ) as $notice ) {
			$prepared[] = $this->prepare_item_for_response( $notice, $request );
		}

		return $prepared;
	}

	public function prepare_item_for_response( $item, $request ) {
		if ( ! $item instanceof ITSEC_Admin_Notice ) {
			return array();
		}

		$notice = array(
			'id'       => $item->get_id(),
			'title'    => $item->get_title(),
			'message'  => $item->get_message(),
			'severity' => $item->get_severity(),
			'meta'     => $item->get_meta(),
			'actions'  => array(),
		);

		foreach ( $item->get_actions() as $slug => $action ) {
			if ( $uri = $action->get_uri() ) {
				$uri = add_query_arg( array(
					'action'       => ITSEC_Admin_Notices::ACTION,
					'notice_id'    => $item->get_id(),
					'itsec_action' => $slug,
					'nonce'        => wp_create_nonce( ITSEC_Admin_Notices::ACTION ),
				), $uri );
			}

			$notice['actions'][ $slug ] = array(
				'id'    => $slug,
				'title' => $action->get_title(),
				'style' => $action->get_style(),
				'uri'   => $uri ?: null,
				'route' => $action->get_route() ?: null,
			);
		}

		return $notice;
	}

	public function update_item( $request ) {

		$notice = $this->get_notice_for_request( $request );

		if ( ! $notice ) {
			return new WP_REST_Response( null, 500 );
		}

		$actions = $notice->get_actions();
		$action  = $actions[ $request['action'] ];

		$error = $action->handle( wp_get_current_user(), $request->get_json_params() ? $request->get_json_params() : $request->get_body_params() );

		if ( is_wp_error( $error ) ) {
			return $error;
		}

		return new WP_REST_Response( null, WP_Http::NO_CONTENT );
	}

	public function update_item_permissions_check( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'not_logged_in', esc_html__( 'You must be logged in to view notices.', 'better-wp-security' ) );
		}

		if ( ! $notice = $this->get_notice_for_request( $request ) ) {
			return new WP_Error( 'notice_not_found', esc_html__( 'Notice not found.', 'better-wp-security' ), array(
				'status' => WP_Http::NOT_FOUND,
			) );
		}

		if ( ! array_key_exists( $request['action'], $notice->get_actions() ) ) {
			return new WP_Error( 'action_not_found', esc_html__( 'Action not found.', 'better-wp-security' ), array(
				'status' => WP_Http::NOT_FOUND,
			) );
		}

		return true;
	}

	private function get_notice_for_request( WP_REST_Request $request ) {
		ITSEC_Lib::load( 'admin-notices' );

		$context = new ITSEC_Admin_Notice_Context( wp_get_current_user(), 'rest' );

		foreach ( ITSEC_Lib_Admin_Notices::get_notices( $context ) as $notice ) {
			if ( $notice->get_id() === $request['notice'] ) {
				return $notice;
			}
		}

		return null;
	}

	public function get_settings( WP_REST_Request $request ) {
		ITSEC_Lib::load( 'highlighted-logs' );

		$settings = array(
			'muted_highlights' => array(),
		);

		foreach ( ITSEC_Lib_Highlighted_Logs::get_dynamics() as $slug => $highlight ) {
			$settings['muted_highlights'][ $slug ] = ITSEC_Lib_Highlighted_Logs::is_muted( $slug );
		}

		return new WP_REST_Response( $settings );
	}

	public function update_settings( WP_REST_Request $request ) {
		ITSEC_Lib::load( 'highlighted-logs' );

		if ( $highlights = $request['muted_highlights'] ) {
			foreach ( $highlights as $highlight => $muted ) {
				if ( $muted === ITSEC_Lib_Highlighted_Logs::is_muted( $highlight ) ) {
					continue;
				}

				if ( $muted ) {
					ITSEC_Lib_Highlighted_Logs::mute( $highlight );
				} else {
					ITSEC_Lib_Highlighted_Logs::unmute( $highlight );
				}
			}
		}

		return $this->get_settings( $request );
	}

	public function get_settings_schema() {
		ITSEC_Lib::load( 'highlighted-logs' );

		$muted_highlights = array();

		foreach ( ITSEC_Lib_Highlighted_Logs::get_dynamics() as $slug => $query ) {
			$muted_highlights[ $slug ] = array(
				'type' => 'boolean',
			);
		}

		return array(
			'title'      => 'itsec-admin-notices-settings',
			'type'       => 'object',
			'properties' => array(
				'muted_highlights' => array(
					'type'                 => 'object',
					'properties'           => $muted_highlights,
					'additionalProperties' => false,
				)
			)
		);
	}
}
