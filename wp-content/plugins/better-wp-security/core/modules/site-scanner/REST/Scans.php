<?php

namespace iThemesSecurity\Site_Scanner\REST;

use iThemesSecurity\Site_Scanner\Factory;
use iThemesSecurity\Site_Scanner\Repository\Options;
use iThemesSecurity\Site_Scanner\Repository\Repository;
use iThemesSecurity\Site_Scanner\Scan;
use iThemesSecurity\Site_Scanner\Status;

class Scans extends \WP_REST_Controller {

	/** @var Repository */
	private $repository;

	/**
	 * Scans constructor.
	 *
	 * @param Repository $repository
	 */
	public function __construct( Repository $repository ) {
		$this->repository = $repository;
		$this->namespace  = 'ithemes-security/v1';
		$this->rest_base  = 'site-scanner/scans';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			],
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ $this, 'create_item_permissions_check' ],
				'args'                => [
					'site_id' => [
						'type'    => 'integer',
						'default' => 0,
					],
				]
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>\d+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
				'args'                => [
					'id'      => [
						'type' => 'integer',
					],
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
				],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function create_item_permissions_check( $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_create', __( 'Sorry, you cannot perform site scans.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		return true;
	}

	public function create_item( $request ) {
		$scan = \ITSEC_Site_Scanner_API::scan( $request['site_id'] );

		$response = $this->prepare_item_for_response( $scan, $request );
		$response->set_status( 201 );
		$response->header( 'Location', rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $scan->get_id() ) ) );

		return $response;
	}

	public function get_items_permissions_check( $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you cannot view site scans.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		return true;
	}

	public function get_items( $request ) {
		$page     = (int) $request['page'];
		$per_page = (int) $request['per_page'];

		$options = ( new Options() )
			->set_page( $page )
			->set_per_page( $per_page );

		if ( $request['code'] ) {
			$options->set_code( $request['code'] );
		}

		if ( $request['before'] ) {
			$options->set_before( new \DateTimeImmutable( $request['before'], new \DateTimeZone( 'UTC' ) ) );
		}

		if ( $request['after'] ) {
			$options->set_after( new \DateTimeImmutable( $request['after'], new \DateTimeZone( 'UTC' ) ) );
		}

		$total = $this->repository->count_scans( $options );
		$scans = $this->repository->get_scans( $options );

		$responses = [];

		foreach ( $scans as $scan ) {
			$responses[] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $scan, $request ) );
		}

		$response = new \WP_REST_Response( $responses );

		$max_pages = ceil( $total / $per_page );

		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$base           = add_query_arg( urlencode_deep( $request_params ), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}

		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	public function get_item_permissions_check( $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you cannot view site scans.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		return true;
	}

	public function get_item( $request ) {
		$scan = $this->repository->get_scan( $request['id'] );

		if ( is_wp_error( $scan ) ) {
			$scan->add_data( [ 'status' => \WP_Http::NOT_FOUND ] );

			return $scan;
		}

		return $this->prepare_item_for_response( $scan, $request );
	}

	/**
	 * Prepares a scan item for response.
	 *
	 * @param Scan             $item
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = [
			'id'          => $item->get_id(),
			'time'        => $item->get_time()->format( \ITSEC_Lib_REST::DATE_FORMAT ),
			'url'         => $item->get_url(),
			'code'        => $item->get_code(),
			'description' => \ITSEC_Site_Scanner_Util::get_scan_code_description( $item->get_code() ),
			'status'      => $item->get_status(),
			'entries'     => [],
			'errors'      => [],
		];

		foreach ( $item->get_entries() as $entry ) {
			$data['entries'][] = [
				'slug'   => $entry->get_slug(),
				'title'  => $entry->get_title(),
				'status' => $entry->get_status(),
			];
		}

		if ( $item->is_error() ) {
			$data['errors'][] = [
				'code'    => $item->get_error()->get_error_code(),
				'message' => $item->get_error()->get_error_message(),
				'data'    => $item->get_error()->get_error_data(),
			];
		}

		foreach ( $item->get_errors() as $error ) {
			$data['errors'][] = [
				'code'    => $error['code'],
				'message' => $error['message'],
			];
		}

		$data     = $this->filter_response_by_context( $data, $request['context'] ?: 'view' );
		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $item->get_id() ) );

		return $response;
	}

	/**
	 * Prepares a list of links for the scan item.
	 *
	 * @param int $scan_id
	 *
	 * @return array
	 */
	protected function prepare_links( $scan_id ) {
		return [
			'self' => [
				'href' => rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $scan_id ) ),
			],

			\ITSEC_Lib_REST::get_link_relation( 'site-scan-issues' ) => [
				'href'       => rest_url( sprintf( '%s/%s/%s/issues', $this->namespace, $this->rest_base, $scan_id ) ),
				'embeddable' => true,
			],
		];
	}

	/**
	 * Checks if the current request has permission to interact with scans.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_User|\WP_Error|false
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
					'id'          => [
						'type'    => 'integer',
						'context' => [ 'view', 'embed' ],
					],
					'time'        => [
						'type'    => 'string',
						'format'  => 'date-time',
						'context' => [ 'view', 'embed' ],
					],
					'url'         => [
						'type'    => 'string',
						'format'  => 'uri',
						'context' => [ 'view', 'embed' ],
					],
					'code'        => [
						'type'    => 'string',
						'context' => [ 'view', 'embed' ],
					],
					'description' => [
						'type'    => 'string',
						'context' => [ 'view', 'embed' ],
					],
					'status'      => [
						'type'    => 'string',
						'context' => [ 'view', 'embed' ],
						'enum'    => Status::ALL,
					],
					'entries'     => [
						'type'    => 'array',
						'context' => [ 'view' ],
						'items'   => [
							'type'       => 'object',
							'properties' => [
								'slug'   => [
									'type' => 'string',
								],
								'title'  => [
									'type' => 'string',
								],
								'status' => [
									'type' => 'string',
									'enum' => Status::ALL,
								],
							],
						],
					],
					'errors'      => [
						'type'    => 'array',
						'context' => [ 'view' ],
						'items'   => [
							'type'       => 'object',
							'properties' => [
								'code'    => [
									'type' => 'string',
								],
								'message' => [
									'type' => 'string',
								],
							],
						],
					],
				],
			];
		}

		return $this->schema;
	}

	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';
		unset( $params['search'] );

		$params['code'] = [
			'type'  => 'array',
			'items' => [
				'type' => 'string',
			],
		];

		$params['before'] = [
			'type'   => 'string',
			'format' => 'date-time',
		];
		$params['after']  = [
			'type'   => 'string',
			'format' => 'date-time',
		];

		return $params;
	}
}
