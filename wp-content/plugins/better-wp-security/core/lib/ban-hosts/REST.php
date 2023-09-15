<?php

namespace iThemesSecurity\Ban_Hosts;

use iThemesSecurity\Actor\Multi_Actor_Factory;
use iThemesSecurity\Exception\WP_Error;

class REST extends \WP_REST_Controller {

	/** @var Multi_Repository */
	private $repository;

	/** @var Multi_Actor_Factory */
	private $actor_factory;

	/**
	 * REST constructor.
	 *
	 * @param Multi_Repository    $repository
	 * @param Multi_Actor_Factory $actor_factory
	 */
	public function __construct( Multi_Repository $repository, Multi_Actor_Factory $actor_factory ) {
		$this->repository    = $repository;
		$this->actor_factory = $actor_factory;
		$this->rest_base     = 'bans';
		$this->namespace     = 'ithemes-security/v1';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		foreach ( $this->repository->get_sources() as $source ) {
			$collection = [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => $this->get_collection_params( $source ),
				],
			];

			$item = [
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
			];

			if ( $this->repository->supports_create( $source ) ) {
				$collection[] = [
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
					'args'                => \ITSEC_Lib_REST::get_endpoint_args_for_schema( $this->repository->get_creation_schema( $source ) ),
				];
			}

			if ( $this->repository->supports_update( $source ) ) {
				$item[] = [
					'methods'             => 'PUT',
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
					'args'                => \ITSEC_Lib_REST::get_endpoint_args_for_schema( $this->repository->get_update_schema( $source ), 'PUT' ),
				];
			}

			if ( $this->repository->supports_delete( $source ) ) {
				$item[] = [
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permissions_check' ],
				];
			}

			$collection['schema'] = [ $this, 'get_public_item_schema' ];
			$item['schema']       = [ $this, 'get_public_item_schema' ];
			$item['args']         = [
				'id' => [
					'type' => 'integer',
				],
			];

			register_rest_route( $this->namespace, sprintf( "%s/(?P<source>%s)", $this->rest_base, $source ), $collection );
			register_rest_route( $this->namespace, sprintf( "%s/(?P<source>%s)/(?P<id>\d+)", $this->rest_base, $source ), $item );
		}
	}

	public function get_items_permissions_check( $request ) {
		if ( \ITSEC_Core::current_user_can_manage() ) {
			return true;
		}

		if ( class_exists( 'ITSEC_Dashboard_Util' ) && \ITSEC_Dashboard_Util::can_access_card( 'banned-users-list' ) ) {
			return true;
		}

		return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you do not have permission to view bans.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
	}

	public function get_items( $request ) {
		$source  = $request['source'] ?: '';
		$cursor  = null;
		$filters = new Filters();
		$allowed = $this->get_collection_params( $source );

		if ( ! empty( $request['after'] ) ) {
			try {
				$cursor = $this->repository->cursor( \ITSEC_Lib::url_safe_b64_decode( $request['after'] ) );
			} catch ( Malformed_Cursor $e ) {
				return new \WP_Error( 'itsec_malformed_cursor', $e->getMessage(), [ 'status' => \WP_Http::BAD_REQUEST ] );
			}
		}

		if ( $request->has_param( 'search' ) ) {
			$search = trim( $request['search'] );

			if ( \ITSEC_Lib_IP_Tools::validate( $search ) ) {
				$ban = $this->repository->find_ban_for_host( $search );

				if ( ! $ban instanceof Repository_Ban || ( $source && $source !== $ban->get_source() ) ) {
					return new \WP_REST_Response( [] );
				}

				return new \WP_REST_Response( [
					$this->prepare_response_for_collection( $this->prepare_item_for_response( $ban, $request ) )
				] );
			}

			if ( in_array( Filters::SEARCH, $this->repository->get_supported_filters( $source ), true ) ) {
				$filters = $filters->with_search( $search );
			}
		}

		if ( isset( $allowed[ Filters::ACTOR_TYPE ] ) && $request->has_param( Filters::ACTOR_TYPE ) ) {
			$filters = $filters->with_actor_type( $request[ Filters::ACTOR_TYPE ] );
		}

		if ( isset( $allowed[ Filters::ACTOR_IDENTIFIER ] ) && $request->has_param( Filters::ACTOR_IDENTIFIER ) ) {
			$filters = $filters->with_actor_identifier( $request[ Filters::ACTOR_IDENTIFIER ] );
		}

		if ( isset( $allowed[ Filters::CREATED_BEFORE ] ) && $request->has_param( Filters::CREATED_BEFORE ) ) {
			$filters = $filters->with_created_before( new \DateTimeImmutable( $request[ Filters::CREATED_BEFORE ] ) );
		}

		if ( isset( $allowed[ Filters::CREATED_AFTER ] ) && $request->has_param( Filters::CREATED_AFTER ) ) {
			$filters = $filters->with_created_after( new \DateTimeImmutable( $request[ Filters::CREATED_AFTER ] ) );
		}

		if ( $request->has_param( 'per_page' ) ) {
			$filters = $filters->with_limit( (int) $request['per_page'] );
		}

		$results = $this->repository->get_bans( $filters, $cursor, $source );
		$bans    = [];

		foreach ( $results->get_bans() as $ban ) {
			$bans[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $ban, $request )
			);
		}

		$response = new \WP_REST_Response( $bans );
		$this->add_collection_links( $response, $request );

		if ( ( $cursor = $results->get_cursor() ) && $cursor->has_more( (int) $request['per_page'] ) ) {
			$path = sprintf( '%s/%s', $this->namespace, $this->rest_base );

			if ( $source ) {
				$path .= '/' . $source;
			}

			$base = add_query_arg( urlencode_deep( $request->get_query_params() ), rest_url( $path ) );
			$response->link_header( 'next', add_query_arg( 'after', \ITSEC_Lib::url_safe_b64_encode( (string) $cursor ), $base ) );
		}

		return $response;
	}

	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	public function get_item( $request ) {
		$ban = $this->repository->get( $request['source'], (int) $request['id'] );

		if ( ! $ban ) {
			return new \WP_Error( 'itsec_ban_not_found', __( 'No ban found with that id.', 'better-wp-security' ), [ 'status' => \WP_Http::NOT_FOUND ] );
		}

		return $this->prepare_item_for_response( $ban, $request );
	}

	public function create_item_permissions_check( $request ) {
		if ( \ITSEC_Core::current_user_can_manage() ) {
			return true;
		}

		return new \WP_Error( 'rest_cannot_create', __( 'Sorry, you do not have permission to create bans.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
	}

	public function create_item( $request ) {
		$params = $request->get_params();
		unset( $params['source'], $params['context'] );

		try {
			$ban = $this->repository->fill( $request['source'], $params );

			if ( $ban->matches( \ITSEC_Lib::get_ip() ) ) {
				return new \WP_Error( 'itsec_cannot_ban_self', esc_html__( 'The host matches your current IP and cannot be banned.', 'better-wp-security' ), [ 'status' => \WP_Http::BAD_REQUEST ] );
			}

			$ban = $this->repository->persist( $ban );
		} catch ( WP_Error $e ) {
			return $e->get_error();
		}

		$request['context'] = 'edit';

		$response = $this->prepare_item_for_response( $ban, $request );
		$response->set_status( \WP_Http::CREATED );
		$response->header( 'Location', $response->get_links()['self'][0]['href'] );

		return $response;
	}

	public function update_item_permissions_check( $request ) {
		if ( \ITSEC_Core::current_user_can_manage() ) {
			return true;
		}

		return new \WP_Error( 'rest_cannot_edit', __( 'Sorry, you do not have permission to update bans.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
	}

	public function update_item( $request ) {
		$ban = $this->repository->get( $request['source'], (int) $request['id'] );

		if ( ! $ban ) {
			return new \WP_Error( 'itsec_ban_not_found', __( 'No ban found with that id.', 'better-wp-security' ), [ 'status' => \WP_Http::NOT_FOUND ] );
		}

		try {
			$updated = $this->repository->fill( $request['source'], $request->get_params(), $ban );
			$updated = $this->repository->persist( $updated );
		} catch ( WP_Error $e ) {
			return $e->get_error();
		}

		$request['context'] = 'edit';

		return $this->prepare_item_for_response( $updated, $request );
	}

	public function delete_item_permissions_check( $request ) {
		if ( \ITSEC_Core::current_user_can_manage() ) {
			return true;
		}

		return new \WP_Error( 'rest_cannot_delete', __( 'Sorry, you do not have permission to delete bans.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
	}

	public function delete_item( $request ) {
		$ban = $this->repository->get( $request['source'], (int) $request['id'] );

		if ( ! $ban ) {
			return new \WP_Error( 'itsec_ban_not_found', __( 'No ban found with that id.', 'better-wp-security' ), [ 'status' => \WP_Http::NOT_FOUND ] );
		}

		$this->repository->delete( $ban );

		return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * Formats a Ban for the REST response.
	 *
	 * @param Repository_Ban   $item
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if ( $actor = $item->get_created_by() ) {
			$actor = [
				'id'    => $actor->get_identifier(),
				'type'  => $actor->get_type(),
				'label' => (string) $actor,
			];
		}

		$response = new \WP_REST_Response( [
			'id'         => $item->get_id(),
			'source'     => $item->get_source(),
			'label'      => (string) $item,
			'created_by' => $actor,
			'created_at' => \ITSEC_Lib::to_rest_date( $item->get_created_at() ),
			'comment'    => $item->get_comment(),
		] );

		$response->add_links( $this->prepare_links( $item, $request ) );

		/**
		 * Filters the prepared response for a Ban object in the REST API.
		 *
		 * @param \WP_REST_Response $response
		 * @param Repository_Ban    $item
		 * @param \WP_REST_Request  $request
		 */
		$response = apply_filters( 'itsec_rest_prepare_ban_for_response', $response, $item, $request );

		return $response;
	}

	/**
	 * Prepares the list of links for the item.
	 *
	 * @param Repository_Ban   $item
	 * @param \WP_REST_Request $request
	 *
	 * @return array[]
	 */
	protected function prepare_links( Repository_Ban $item, \WP_REST_Request $request ) {
		$href = rest_url( sprintf( '%s/%s/%s/%s', $this->namespace, $this->rest_base, $item->get_source(), $item->get_id() ) );

		return [
			'self' => [
				'href'        => $href,
				'targetHints' => [
					'allow' => $this->get_allowed_headers( $item, $request ),
				],
			],
		];
	}

	/**
	 * Add links to the collection response.
	 *
	 * @param \WP_REST_Response $response
	 * @param \WP_REST_Request  $request
	 */
	protected function add_collection_links( \WP_REST_Response $response, \WP_REST_Request $request ) {
		if ( ! $request['source'] ) {
			foreach ( $this->repository->get_sources() as $source ) {
				if ( $this->repository->supports_create( $source ) && \ITSEC_Core::current_user_can_manage() ) {
					$href = rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $source ) );
					$response->link_header( 'create-form', $href );
				}
			}
		}
	}

	/**
	 * Gets the list of allowed headers.
	 *
	 * @param Repository_Ban   $ban
	 * @param \WP_REST_Request $request
	 *
	 * @return string[]
	 */
	protected function get_allowed_headers( Repository_Ban $ban, \WP_REST_Request $request ) {
		$allow = [
			'GET',
		];

		if ( $this->repository->supports_update( $ban->get_source() ) && true === $this->update_item_permissions_check( $request ) ) {
			$allow[] = 'PUT';
		}

		if ( $this->repository->supports_delete( $ban->get_source() ) && true === $this->delete_item_permissions_check( $request ) ) {
			$allow[] = 'DELETE';
		}

		return $allow;
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$links = [];

		foreach ( $this->repository->get_sources() as $source ) {
			if ( $this->repository->supports_create( $source ) ) {
				$schema = $this->repository->get_creation_schema( $source );

				$links[] = [
					'rel'              => 'create-form',
					'href'             => rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $source ) ),
					'submissionSchema' => \ITSEC_Lib_REST::sanitize_schema_for_output( $schema ),
					'targetHints'      => [
						'allow' => \ITSEC_Core::current_user_can_manage() ? [ 'GET', 'POST' ] : [ 'GET' ],
					],
					'title'            => $schema['title'] ?? __( 'Add Ban', 'better-wp-security' ),
				];
			}

			if ( $this->repository->supports_update( $source ) ) {
				$links[] = [
					'rel'          => 'edit-form',
					'href'         => rest_url( sprintf( '%s/%s/%s/{id}', $this->namespace, $this->rest_base, $source ) ),
					'targetSchema' => \ITSEC_Lib_REST::sanitize_schema_for_output( $this->repository->get_update_schema( $source ), 'PUT' ),
				];
			}
		}

		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'ithemes-security-ban',
			'type'       => 'object',
			'properties' => [
				'id'         => [
					'type'    => 'integer',
					'context' => [ 'view', 'edit', 'embed' ],
				],
				'source'     => [
					'type'    => 'string',
					'enum'    => $this->repository->get_sources(),
					'context' => [ 'view', 'edit', 'embed' ],
				],
				'label'      => [
					'type'    => 'string',
					'context' => [ 'view', 'edit', 'embed' ],
				],
				'created_by' => [
					'type'       => [ 'object', 'null' ],
					'context'    => [ 'view', 'edit', 'embed' ],
					'properties' => [
						'id'    => [
							'type' => [ 'integer', 'string' ],
						],
						'type'  => [
							'type' => 'string',
							'enum' => \ITSEC_Lib::pluck( $this->actor_factory->get_supported_factories(), 'get_slug' ),
						],
						'label' => [
							'type' => 'string',
						],
					],
				],
				'created_at' => [
					'type'    => 'string',
					'format'  => 'date-time',
					'context' => [ 'view', 'edit', 'embed' ],
				],
				'comment'    => [
					'type'    => 'string',
					'context' => [ 'view', 'edit', 'embed' ],
				],
			],
			'links'      => $links,
		];

		/**
		 * Filters the schema for the Ban Hosts REST API endpoint.
		 *
		 * @since 7.0.0
		 *
		 * @param array $schema The schema to filter.
		 */
		$this->schema = apply_filters( 'itsec_ban_hosts_rest_schema', $schema );

		return $this->schema;
	}

	public function get_collection_params( $source = '' ) {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';
		unset( $params['page'] );

		$filters = $this->repository->get_supported_filters( $source );

		foreach ( $filters as $filter ) {
			switch ( $filter ) {
				case Filters::ACTOR_TYPE:
					$params[ $filter ] = [
						'title'       => __( 'Actor Type', 'better-wp-security' ),
						'description' => __( 'The type of actor that created the ban.', 'better-wp-security' ),
						'type'        => 'string',
						'enum'        => \ITSEC_Lib::pluck( $this->actor_factory->get_supported_factories(), 'get_slug' ),
					];
					break;
				case Filters::ACTOR_IDENTIFIER:
					$params[ $filter ] = [
						'title'       => __( 'Actor Identifier', 'better-wp-security' ),
						'description' => __( 'The particular actor that caused the ban.', 'better-wp-security' ),
						'type'        => 'string',
					];
					break;
				case Filters::CREATED_AFTER:
					$params[ $filter ] = [
						'title'       => __( 'Banned After', 'better-wp-security' ),
						'description' => __( 'Limit to bans added after the given date.', 'better-wp-security' ),
						'type'        => 'string',
						'format'      => 'date-time',
					];
					break;
				case Filters::CREATED_BEFORE:
					$params[ $filter ] = [
						'title'       => __( 'Banned Before', 'better-wp-security' ),
						'description' => __( 'Limit to bans added before the given date.', 'better-wp-security' ),
						'type'        => 'string',
						'format'      => 'date-time',
					];
					break;
			}
		}

		return $params;
	}
}
