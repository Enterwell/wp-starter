<?php
/**
 * Created by PhpStorm.
 * User: Matej
 * Date: 3.9.2018.
 * Time: 13:49
 */

namespace Ew\WpHelpers\Controllers;

use Ew\WpHelpers\Services\ICRUD_Service;
use \WP_REST_Request;

/**
 * Class ACRUD_Controller
 *
 * Controller that has templates for
 * CREATE and UPDATE actions for the entity (REST resource).
 *
 * @package Ew\WpHelpers
 */
abstract class ACRUD_Controller extends AController {

	/**
	 * Creates the entity from the request.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function create( WP_REST_Request $request ) {
		// Get all request params
		$r = $request->get_params();

		// Get CRUD service
		$service = $this->get_crud_service();

		// Validate create request
		$result = $service->validate_create_request( $r );

		// If request is not valid
		if ( ! $result->is_valid() ) {
			return $this->bad_request( $result->get_message() );
		}

		try {
			// Create entity
			$entity = $service->create_from_request( $r );

			// Return OK response
			return $this->ok( $entity );

		} catch ( \Exception $e ) {
			return $this->exception_response( $e );
		}
	}

	/**
	 * Gets CRUD service for request validation, update and create.
	 * @return ICRUD_Service
	 */
	abstract function get_crud_service();

	/**
	 * Updates the entity from the request.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function update( WP_REST_Request $request ) {
		// Get all request params
		$r         = $request->get_params();
		$entity_id = $request->get_param( 'id' );

		// Entity id have to be set
		if ( empty( $entity_id ) ) {
			return $this->bad_request( "Entity to update id is not set!" );
		}

		// Get CRUD service
		$service = $this->get_crud_service();

		// Validate create request
		$result = $service->validate_create_request( $r );

		// If request is not valid
		if ( ! $result->is_valid() ) {
			return $this->bad_request( $result->get_message() );
		}

		try {
			// Update entity
			$entity = $service->update_from_request( $entity_id, $r );

			// Return OK response
			return $this->ok( $entity );

		} catch ( \Exception $e ) {
			return $this->exception_response( $e );
		}
	}
}