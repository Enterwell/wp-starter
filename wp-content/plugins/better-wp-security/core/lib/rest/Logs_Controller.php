<?php

namespace iThemesSecurity\Lib\REST;

use WP_REST_Request;

final class Logs_Controller extends \WP_REST_Controller {
	protected $namespace = 'ithemes-security/v1';
	protected $rest_base = 'logs';
	protected $types = [];


	public function __construct() {
		$this->types = \ITSEC_Log::get_types_for_display();
	}

	public function register_routes() {
		// basic request
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => $this->get_collection_params(),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
		// request based on id
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => [
					'id'      => [
						'type' => 'integer',
					],
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
				],
			],
			'schema' => [ $this, 'get_public_item_schema' ]
		] );
	}

	public function get_items( $request ) {
		\ITSEC_Modules::load_module_file( 'logs.php', ':active' );

		$filters = [];

		if ( $request['before'] ) {
			$filters['__max_timestamp'] = rest_parse_date( $request['before'] );
		}

		if ( $request['after'] ) {
			$filters['__min_timestamp'] = rest_parse_date( $request['after'] );
		}

		if ( $request['search'] ) {
			$filters['__search'] = $request['search'];
		}

		if ( $request['module'] ) {
			$filters['module'] = $request['module'];
		}

		if ( $request['code'] ) {
			$filters['code'] = $request['code'];
		}

		if ( $request['type'] ) {
			$filters['type'] = $request['type'];
		}

		if ( $request['user'] ) {
			$filters['user_id'] = $request['user'];
		}

		if ( $request['ip'] ) {
			$filters['remote_ip'] = $request['ip'];
		}

		$orderby_map = [
			'created_at'         => 'timestamp',
			'id'                 => 'id',
			'module'             => 'module',
			'code'               => 'code',
			'type'               => 'type',
			'request_started_at' => 'init_timestamp',
			'user'               => 'user_id',
			'site'               => 'blog_id',
		];

		$columns = $this->get_columns_for_request( $request );

		$items = \ITSEC_Log_Util::get_entries(
			$filters,
			$request['per_page'],
			$request['page'],
			$orderby_map[ $request['orderby'] ],
			$request['order'],
			$columns
		);

		if ( is_wp_error( $items ) ) {
			return $items;
		}

		$data = [];

		foreach ( $items as $item ) {
			$data[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $item, $request )
			);
		}

		$response = new \WP_REST_Response( $data );

		$filters['__get_count'] = true;
		$count                  = \ITSEC_Log_Util::get_entries( $filters );

		if ( ! is_wp_error( $count ) ) {
			\ITSEC_Lib_REST::paginate( $request, $response, $count, $this->namespace . '/' . $this->rest_base );
		}

		return $response;
	}

	public function get_item( $request ) {
		\ITSEC_Modules::load_module_file( 'logs.php', ':active' );

		$log = $this->get_log( $request );

		if ( is_wp_error( $log ) ) {
			return $log;
		}

		return $this->prepare_item_for_response( $log, $request );
	}

	/**
	 * Gets the requested log.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|\WP_Error
	 */
	protected function get_log( WP_REST_Request $request ) {
		$filters['id'] = $request['id'];
		$columns       = $this->get_columns_for_request( $request );
		$items         = \ITSEC_Log_Util::get_entries( $filters, 1, 1, 'timestamp', 'DESC', $columns );

		if ( is_wp_error( $items ) ) {
			return $items;
		}

		if ( isset( $items[0] ) ) {
			return $items[0];
		}

		return new \WP_Error(
			'rest_not_found',
			__( 'Log not found.', 'better-wp-security' ),
			[ 'status' => \WP_Http::NOT_FOUND ]
		);
	}

	public function prepare_item_for_response( $item, $request ) {
		if ( false === strpos( $item['code'], '::' ) ) {
			$code = $item['code'];
			$data = [];
		} else {
			[ $code, $data ] = explode( '::', $item['code'], 2 );
			$data = explode( ',', $data );
		}

		$rendered                   = $item;
		$rendered['description']    = $item['code'];
		$rendered['module_display'] = $item['module'];

		$rendered = apply_filters( "itsec_logs_prepare_{$item['module']}_entry_for_list_display", $rendered, $code, $data );

		$data = [
			'id'                 => (int) $item['id'],
			'parent'             => (int) $item['parent_id'],
			'module'             => [
				'raw'      => $item['module'],
				'rendered' => $rendered['module_display']
			],
			'code'               => [
				'raw'      => [
					'code' => $code,
					'data' => $data,
				],
				'rendered' => $rendered['description'],
			],
			'type'               => [
				'raw'      => $item['type'],
				'rendered' => $this->types[ $item['type'] ]
			],
			'created_at'         => mysql_to_rfc3339( $item['timestamp'] ),
			'request_started_at' => mysql_to_rfc3339( $item['init_timestamp'] ),
			'url'                => $item['url'],
			'ip'                 => [
				'raw'   => $item['remote_ip'],
			],
			'user'               => (int) $item['user_id'],
			'memory_current'     => (int) $item['memory_current'],
			'memory_peak'        => (int) $item['memory_peak'],
			'site'               => (int) $item['blog_id'],
		];

		if ( $request['context'] === 'view' ) {
			$data['data'] = $item['data'];
		}

		$data     = $this->filter_response_by_context( $data, $request['context'] );
		$response = new \WP_REST_Response( $data );
		$response->add_link(
			'self',
			rest_url(
				sprintf( "%s/%s/%s", $this->namespace, $this->rest_base, $data['id'] )
			)
		);

		if (
			\ITSEC_Modules::is_active( 'geolocation' ) &&
			filter_var( $item['remote_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE & FILTER_FLAG_IPV4 )
		) {
			$response->add_link(
				\ITSEC_Lib_REST::get_link_relation( 'geolocate' ),
				rest_url( sprintf( 'ithemes-security/v1/geolocate/%s', urlencode( $item['remote_ip'] ) ) ),
				[
					'embeddable' => true,
				]
			);
		}

		/**
		 * Filters the REST API response for Log items.
		 *
		 * @param \WP_REST_Response $response The REST API response object.
		 * @param array             $item     The raw log item from the database.
		 * @param WP_REST_Request   $request  The full API request object.
		 */
		return apply_filters( 'itsec_rest_prepare_log_for_response', $response, $item, $request );
	}

	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';
		$params['before']             = [
			'type'   => 'string',
			'format' => 'date-time',
		];
		$params['after']              = [
			'type'   => 'string',
			'format' => 'date-time',
		];
		$params['module']             = [
			'type'  => 'array',
			'items' => [
				'type' => 'string',
			],
		];
		$params['code']               = [
			'type'  => 'array',
			'items' => [
				'type' => 'string',
			],
		];
		$params['type']               = [
			'type'  => 'array',
			'items' => [
				'type'      => 'string',
				'enum'      => array_keys( $this->types ),
				'enumNames' => array_values( $this->types ),
			],
		];
		$params['user']               = [
			'type'  => 'array',
			'items' => [
				'type' => 'integer',
			],
		];
		$params['ip']                 = [
			'type'  => 'array',
			'items' => [
				'type'   => 'string',
				'format' => 'ip',
			],
		];
		$params['order']              = [
			'type'    => 'string',
			'enum'    => [ 'asc', 'desc' ],
			'default' => 'desc',
		];
		$params['orderby']            = [
			'type'    => 'string',
			'enum'    => [
				'created_at',
				'id',
				'module',
				'code',
				'type',
				'request_started_at',
				'user',
				'site',
			],
			'default' => 'created_at',
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
				'id'                 => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'parent'             => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'module'             => [
					'type'       => 'object',
					'properties' => [
						'raw'      => [
							'type' => 'string',
						],
						'rendered' => [
							'type' => 'string',
						],
					],
					'readonly'   => true,
					'context'    => [ 'view', 'embed' ],
				],
				'code'               => [
					'type'       => 'object',
					'properties' => [
						'raw'      => [
							'type'       => 'object',
							'properties' => [
								'code' => [
									'type' => 'string',
								],
								'data' => [
									'type'  => 'array',
									'items' => [
										'type' => 'string',
									],
								],
							],
						],
						'rendered' => [
							'type' => 'string',
						],
					],
					'readonly'   => true,
					'context'    => [ 'view', 'embed' ],
				],
				'type'               => [
					'type'       => 'object',
					'properties' => [
						'raw'      => [
							'type' => 'string',
							'enum' => array_keys( $this->types ),
						],
						'rendered' => [
							'type' => 'string',
						],
					],
					'readonly'   => true,
					'context'    => [ 'view', 'embed' ],
				],
				'created_at'         => [
					'type'     => 'string',
					'format'   => 'date-time',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'request_started_at' => [
					'type'     => 'string',
					'format'   => 'date-time',
					'readonly' => true,
					'context'  => [ 'view' ],
				],
				'url'                => [
					'type'     => 'string',
					'oneOf'    => [
						[
							'enum' => [
								'wp-cli',
								'wp-cron',
								'unknown',
							],
						],
						[
							'format' => 'uri',
						],
					],
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'ip'                 => [
					'type'       => 'object',
					'properties' => [
						'raw'     => [
							'type'   => 'string',
							'format' => 'ip',
						],
						'details' => [
							'type'   => 'string',
							'format' => 'uri',
						],
					],
					'readonly'   => true,
					'context'    => [ 'view', 'embed' ],
				],
				'user'               => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'memory_current'     => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view' ],
				],
				'memory_peak'        => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view' ],
				],
				'data'               => [
					'type'     => [
						'array',
						'object',
						'string',
					],
					'readonly' => true,
					'context'  => [ 'view' ],
				],
				'site'               => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view' ],
				],
			],
		];

		return $this->schema;
	}

	/**
	 * Gets columns for request.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return string[]
	 */
	protected function get_columns_for_request( WP_REST_Request $request ): array {
		$columns = [
			'id',
			'parent_id',
			'module',
			'type',
			'code',
			'timestamp',
			'init_timestamp',
			'remote_ip',
			'user_id',
			'url',
			'memory_current',
			'memory_peak',
			'blog_id',
		];

		if ( $request['context'] === 'view' ) {
			$columns[] = 'data';
		}

		return $columns;
	}
}
