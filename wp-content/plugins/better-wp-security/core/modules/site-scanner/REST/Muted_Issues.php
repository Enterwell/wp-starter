<?php

namespace iThemesSecurity\Site_Scanner\REST;

use iThemesSecurity\Exception\WP_Error;

class Muted_Issues extends \WP_REST_Controller {

	protected $schema;

	public function __construct() {
		$this->namespace = 'ithemes-security/v1';
		$this->rest_base = 'site-scanner/muted-issues';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => [],
			],
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ $this, 'create_item_permissions_check' ],
				'args'                => $this->get_endpoint_args_for_item_schema(),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\w-]+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
			],
			[
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ $this, 'delete_item_permissions_check' ],
			],
			'args'   => [],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function get_items_permissions_check( $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you cannot view muted issues.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		return true;
	}

	public function get_items( $request ) {
		$issues = \ITSEC_Site_Scanner_Util::get_muted_issues();
		$data   = [];

		foreach ( $issues as $issue ) {
			$data[] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $issue, $request ) );
		}

		return new \WP_REST_Response( $data );
	}

	public function get_item_permissions_check( $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you cannot view muted issues.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		return true;
	}

	public function get_item( $request ) {
		if ( ! \ITSEC_Site_Scanner_Util::is_issue_muted( $request['id'] ) ) {
			return new \WP_Error( 'rest_not_found', __( 'This issue has not been muted.', 'better-wp-security' ), array( 'status' => \WP_Http::NOT_FOUND ) );
		}

		$issue = \ITSEC_Site_Scanner_Util::get_muted_issue( $request['id'] );

		return $this->prepare_item_for_response( $issue, $request );
	}

	public function create_item_permissions_check( $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_create', __( 'Sorry, you cannot mute issues.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		return true;
	}

	public function create_item( $request ) {
		if ( \ITSEC_Site_Scanner_Util::is_issue_muted( $request['id'] ) ) {
			return new \WP_Error( 'rest_issue_already_muted', __( 'This issue has already been muted.', 'better-wp-security' ), array( 'status' => \WP_Http::BAD_REQUEST ) );
		}

		$args = [];

		if ( ( $user = $this->has_permission( $request ) ) && $user instanceof \WP_User ) {
			$args['muted_by'] = $user->ID;
		}

		$muted = \ITSEC_Site_Scanner_Util::mute_issue( $request['id'], $args );

		if ( is_wp_error( $muted ) ) {
			return $muted;
		}

		$response = $this->prepare_item_for_response( $muted, $request );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( $this->namespace . '/' . $this->rest_base . $request['id'] ) );

		return $response;
	}

	public function delete_item_permissions_check( $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_delete', __( 'Sorry, you cannot unmute issues.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		return true;
	}

	public function delete_item( $request ) {
		if ( ! \ITSEC_Site_Scanner_Util::is_issue_muted( $request['id'] ) ) {
			return new \WP_Error( 'rest_not_found', __( 'This issue is not muted.', 'better-wp-security' ), array( 'status' => \WP_Http::NOT_FOUND ) );
		}

		$deleted = \ITSEC_Site_Scanner_Util::unmute_issue( $request['id'] );

		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}

		return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	public function prepare_item_for_response( $item, $request ) {
		$response = new \WP_REST_Response( [
			'id'       => $item['id'],
			'muted_by' => $item['muted_by'],
			'muted_at' => date( 'Y-m-d\TH:i:s', $item['muted_at'] ),
		] );
		$response->add_link( 'self', rest_url( $this->namespace . '/' . $this->rest_base . $request['id'] ) );

		if ( $item['muted_by'] ) {
			$response->add_link( 'author', rest_url( '/wp/v2/users/' . $item['muted_by'] ) );
		}

		return $response;
	}

	/**
	 * Checks if the current request has permission to modify muted issues.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_User|WP_Error|false
	 */
	protected function has_permission( \WP_REST_Request $request ) {
		if ( is_user_logged_in() ) {
			return wp_get_current_user();
		}

		if ( $token = \ITSEC_Lib_REST::get_token_from_auth_header( $request->get_header( 'Authorization' ) ) ) {
			$valid = \ITSEC_Site_Scanner_Util::validate_scan_auth_token( $token );

			if ( is_wp_error( $valid ) ) {
				$valid->add_data( [ 'status' => 401 ] );
			}

			return $valid;
		}

		return false;
	}

	public function get_item_schema() {
		if ( ! $this->schema ) {
			$this->schema = [
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'type'       => 'object',
				'properties' => [
					'id'       => [
						'type' => 'string',
					],
					'muted_by' => [
						'type' => 'integer',
					],
					'muted_at' => [
						'type'   => 'string',
						'format' => 'date-time',
					],
				],
			];
		}

		return $this->schema;
	}
}
