<?php

namespace EwStarter;

/**
 * Class Locations_Controller
 * @package EwStarter
 */
class Locations_Controller extends AEwStarter_Controller {
	/**
	 * @var Locations_Service
	 */
	private $locations_service;

	/**
	 * Locations_Controller constructor.
	 */
	public function __construct() {
		parent::__construct( 'locations' );

		$this->locations_service = new Locations_Service();
	}

	/**
	 * Registers all controller routes.
	 * In this function all routes for this controller are registered
	 * using WordPress function register_route
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_namespace(),
			"/" . $this->route,
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'create' ],
			]
		);
	}

	/**
	 * Create new location
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function create( \WP_REST_Request $request ) {
		$r = $request->get_params();

		$result = $this->locations_service->validate_create_request( $r );

		if ( ! $result->is_valid() ) {
			return $this->bad_request( $result->get_message() );
		}

		try {
			$entity = $this->locations_service->create_from_request( $r );

			return $this->ok( $entity );
		} catch ( \Exception $e ) {
			return $this->exception_response( $e );
		}
	}
}