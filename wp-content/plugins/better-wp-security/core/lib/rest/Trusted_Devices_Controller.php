<?php

namespace iThemesSecurity\Lib\REST;

use WP_REST_Request;

final class Trusted_Devices_Controller extends \WP_REST_Controller {

	const ID_PATTERN = '/(?P<id>[\\w_:-]+)';
	protected $namespace = 'ithemes-security/v1';
	protected $rest_base = 'trusted-devices' . '/(?P<user>[\d]+)';

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => array_merge(
					[
						'user' => [
							'type' => 'integer',
						],
					],
					$this->get_collection_params(),
				)
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
		register_rest_route( $this->namespace, $this->rest_base . self::ID_PATTERN, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
				'args'                => [
					'user'    => [
						'type' => 'integer',
					],
					'id'      => [
						'type' => 'string',
					],
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
				],
			],
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ $this, 'update_item_permissions_check' ],
				'args'                => array_merge(
					[
						'user' => [
							'type' => 'integer',
						],
						'id'   => [
							'type' => 'string',
						],
					],
					$this->get_endpoint_args_for_item_schema( 'PUT' )
				),
			],
			'schema'      => [ $this, 'get_public_item_schema' ],
			'allow_batch' => [ 'v1' => true ],
		] );
	}

	public function get_items_permissions_check( $request ) {
		if ( ! \ITSEC_Core::current_user_can_manage() && get_current_user_id() !== $request['user'] ) {
			return new \WP_Error(
				'rest_cannot_view',
				__( 'Sorry, you are not allowed to view other trusted devices', 'better-wp-security' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		if ( ! \ITSEC_Lib_Fingerprinting::is_current_fingerprint_safe() ) {
			return new \WP_Error(
				'rest_solid_unknown_device',
				__( 'Sorry, you cannot manage trusted devices from an unknown device.', 'better-wp-security' ),
				[ 'status' => \WP_Http::FORBIDDEN ]
			);
		}

		return true;
	}

	public function get_items( $request ) {
		$data = [];
		$user = get_userdata( $request['user'] );

		if ( ! $user ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Sorry, that user does not exist', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$args = [
			'per_page'         => $request['per_page'],
			'page'             => $request['page'],
			'status'           => $request['status'],
			'last_seen_before' => rest_parse_date( $request['last_seen_before'] ),
			'last_seen_after'  => rest_parse_date( $request['last_seen_after'] ),
			'search'           => $request['search'],
		];

		$results = \ITSEC_Fingerprint::get_all_for_user( $user, $args );

		foreach ( $results as $result ) {
			$data[] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $result, $request ) );
		}

		$count = \ITSEC_Fingerprint::count_all_for_user( $user, $args );

		$response = new \WP_REST_Response( $data );

		if ( ! is_wp_error( $count ) ) {
			\ITSEC_Lib_REST::paginate( $request, $response, $count, $this->namespace . '/trusted-devices/' . $user->ID  );
		}

		return $response;
	}

	public function get_item_permissions_check( $request ) {
		if ( ! \ITSEC_Core::current_user_can_manage() && get_current_user_id() !== $request['user'] ) {
			return new \WP_Error(
				'rest_cannot_view',
				__( 'Sorry, you are not allowed to view other trusted devices', 'better-wp-security' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		if ( ! \ITSEC_Lib_Fingerprinting::is_current_fingerprint_safe() ) {
			return new \WP_Error(
				'rest_solid_unknown_device',
				__( 'Sorry, you cannot manage trusted devices from an unknown device.', 'better-wp-security' ),
				[ 'status' => \WP_Http::FORBIDDEN ]
			);
		}

		return true;
	}

	public function get_item( $request ) {
		$user = get_userdata( $request['user'] );

		if ( ! $user ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Sorry, that user does not exist', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$trusted_device = \ITSEC_Fingerprint::get_by_uuid( $request['id'] );

		if ( ! $trusted_device ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Sorry, that device does not exist', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		if ( $user->ID !== $trusted_device->get_user()->ID ) {
			return new \WP_Error(
				'rest_cannot_view',
				__( 'Sorry you are not allowed to view that trusted device', 'better-wp-security' ),
				[ 'status' => \WP_Http::FORBIDDEN ]
			);
		}

		return $this->prepare_item_for_response( $trusted_device, $request );
	}

	public function update_item_permissions_check( $request ) {
		if ( ! \ITSEC_Core::current_user_can_manage() && get_current_user_id() !== $request['user'] ) {
			return new \WP_Error(
				'rest_cannot_update',
				__( 'Sorry, you are not allowed to update other trusted devices', 'better-wp-security' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		if ( ! \ITSEC_Lib_Fingerprinting::is_current_fingerprint_safe() ) {
			return new \WP_Error(
				'rest_solid_unknown_device',
				__( 'Sorry, you cannot manage trusted devices from an unknown device.', 'better-wp-security' ),
				[ 'status' => \WP_Http::FORBIDDEN ]
			);
		}

		return true;
	}

	public function update_item( $request ) {
		$user = get_userdata( $request['user'] );

		if ( ! $user ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Sorry, that user does not exist', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$trusted_device = \ITSEC_Fingerprint::get_by_uuid( $request['id'] );

		if ( ! $trusted_device ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Sorry, that device does not exist', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		if ( ! $trusted_device->can_change_status() ) {
			return new \WP_Error(
				'rest_action_forbidden',
				__( 'Sorry, this device cannot be updated', 'better-wp-security' ),
				[ 'status' => \WP_Http::FORBIDDEN ]
			);
		}

		$new_status = $request['status']['raw'];

		if ( $new_status === 'approved' ) {
			$trusted_device->approve();

			return $this->prepare_item_for_response( $trusted_device, $request );
		} elseif ( $new_status === 'denied' ) {
			$trusted_device->deny();

			return $this->prepare_item_for_response( $trusted_device, $request );
		} else {
			return new \WP_Error(
				'rest_action_forbidden',
				__( 'Cannot update trusted device status', 'better-wp-security' ),
				[ 'status' => \WP_Http::FORBIDDEN ]
			);
		}
	}

	private function translate_item_rendered_string( $status ) {
		switch ( $status ) {
			case 'approved':
				return __( 'Approved', 'better-wp-security' );
			case 'auto-approved':
				return __( 'Auto-Approved', 'better-wp-security' );
			case 'pending-auto-approve':
				return __( 'Pending-Auto-Approve', 'better-wp-security' );
			case 'pending':
				return __( 'Pending', 'better-wp-security' );
			case 'denied':
				return __( 'Denied', 'better-wp-security' );
		}
	}

	public function prepare_item_for_response( $item, $request ): \WP_REST_Response {
		if ( ! $item instanceof \ITSEC_Fingerprint ) {
			return new \WP_REST_Response();
		}

		$approved_at = '';

		if ( $item->get_approved_at() ) {
			$approved_at = mysql_to_rfc3339( $item->get_approved_at()->format( 'Y-m-d H:i:s' ) );
		}

		$fingerprint_info = \ITSEC_Fingerprinting::get_fingerprint_info( $item, array( 'maps' => false ) );

		$data = [
			'id'              => $item->get_uuid(),
			'status'          => [
				'raw'      => $item->get_status(),
				'rendered' => $this->translate_item_rendered_string( $item->get_status() ),
			],
			'uses'            => (int) $item->get_uses(),
			'created_at'      => mysql_to_rfc3339( $item->get_created_at()->format( 'Y-m-d H:i:s' ) ),
			'last_seen'       => mysql_to_rfc3339( $item->get_last_seen()->format( 'Y-m-d H:i:s' ) ),
			'approved_at'     => $approved_at,
			'location'        => $fingerprint_info['location'],
			'ip'              => $fingerprint_info['ip'],
			'browser'         => $fingerprint_info['browser'],
			'browser_version' => $fingerprint_info['browser_ver'],
			'platform'        => $fingerprint_info['platform'],
		];

		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		return $response;
	}

	protected function prepare_links( \ITSEC_Fingerprint $fingerprint ): array {
		return [
			'self' => [
				'href' => rest_url( sprintf(
					'%s/trusted-devices/%d/%s',
					$this->namespace,
					$fingerprint->get_user()->ID,
					$fingerprint->get_uuid(),
				) ),
			],
		];
	}

	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';
		$params['status']             = [
			'type' => 'string',
			'enum' => [
				'approved',
				'auto-approved',
				'pending-auto-approve',
				'pending',
				'denied'
			]
		];
		$params['last_seen_before']   = [
			'type'   => 'string',
			'format' => 'date-time',
		];
		$params['last_seen_after']    = [
			'type'   => 'string',
			'format' => 'date-time',
		];

		return $params;
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'type'       => 'object',
			'properties' => [
				'status' => [
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => [
						'raw'      => [
							'type' => 'string',
							'enum' => [
								'approved',
								'auto-approved',
								'pending-auto-approve',
								'pending',
								'denied'
							]
						],
						'rendered' => [
							'type' => 'string',
						]
					]
				]
			]
		];

		return $this->schema;
	}
}
