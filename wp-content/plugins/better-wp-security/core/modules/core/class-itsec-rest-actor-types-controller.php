<?php

use iThemesSecurity\Actor\Actor_Factory;
use iThemesSecurity\Actor\Multi_Actor_Factory;

class ITSEC_REST_Actor_Types_Controller extends WP_REST_Controller {

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
		register_rest_route( $this->namespace, $this->rest_base, [
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
		$items = [];

		foreach ( $this->factory->get_supported_factories() as $factory ) {
			$items[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $factory, $request )
			);
		}

		return new WP_REST_Response( $items );
	}

	/**
	 * Prepares an Actor Factory for a response.
	 *
	 * @param Actor_Factory   $item
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$response = new WP_REST_Response( [
			'slug'  => $item->get_slug(),
			'label' => $item->get_label(),
		] );

		$response->add_link(
			'https://api.w.org/items',
			rest_url( sprintf( '%s/%s/%s/search', $this->namespace, $this->rest_base, $item->get_slug() ) ),
			[
				'embeddable' => $item->is_determinate(),
			]
		);

		return $response;
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'properties' => [
				'slug'  => [
					'type'    => 'string',
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
