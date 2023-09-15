<?php

use iThemesSecurity\Actor\Actor;
use iThemesSecurity\Actor\Actor_Factory;
use iThemesSecurity\Actor\Multi_Actor_Factory;

class ITSEC_REST_Actors_Controller extends WP_REST_Controller {

	/** @var Multi_Actor_Factory */
	private $factory;

	/**
	 * ITSEC_REST_Actors_Controller constructor.
	 *
	 * @param Multi_Actor_Factory $factory
	 */
	public function __construct( Multi_Actor_Factory $factory ) {
		$this->factory   = $factory;
		$this->namespace = 'ithemes-security/v1';
		$this->rest_base = 'actors';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<type>[\w\-\_]+)/search', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_items' ],
			'permission_callback' => [ $this, 'get_items_permissions_check' ],
			'schema'              => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return ITSEC_Core::current_user_can_manage() || current_user_can( 'itsec_dashboard_access' );
	}

	public function get_items( $request ) {
		if ( ! $factory = $this->get_requested_factory( $request ) ) {
			return new \WP_Error(
				'itsec_invalid_actor_type',
				__( 'That actor type is not supported.', 'better-wp-security' ),
				[ 'status' => WP_Http::NOT_FOUND ]
			);
		}

		$items = [];

		foreach ( $factory->get_actors( $request['search'] ) as $actor ) {
			$items[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $actor, $request )
			);
		}

		return new WP_REST_Response( $items );
	}

	/**
	 * Gets the requested actor factory.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return Actor_Factory|null
	 */
	protected function get_requested_factory( $request ) {
		foreach ( $this->factory->get_supported_factories() as $factory ) {
			if ( $factory->get_slug() === $request['type'] ) {
				return $factory;
			}
		}

		return null;
	}

	/**
	 * Prepares an Actor Factory for a response.
	 *
	 * @param Actor           $item
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		return new WP_REST_Response( [
			'id'    => $item->get_identifier(),
			'label' => (string) $item,
		] );
	}

	public function get_collection_params() {
		$params = parent::get_collection_params();
		unset( $params['page'], $params['per_page'] );
		$params['context']['default']          = 'view';
		$params['search']['default']           = '';
		$params['search']['validate_callback'] = function ( $value, $request ) {
			if ( ! $factory = $this->get_requested_factory( $request ) ) {
				return true;
			}

			if ( $factory->is_determinate() || trim( $value ) ) {
				return true;
			}

			return new WP_Error( 'rest_invalid_param', __( 'A search term is required when querying this actor type.', 'better-wp-security' ) );
		};

		return $params;
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'properties' => [
				'id'    => [
					'type'    => [ 'integer', 'string' ],
					'context' => [ 'view', 'edit', 'embed' ],
				],
				'label' => [
					'type'    => 'string',
					'context' => [ 'view', 'edit', 'embed' ],
				],
			],
		];

		return $this->schema;
	}
}
