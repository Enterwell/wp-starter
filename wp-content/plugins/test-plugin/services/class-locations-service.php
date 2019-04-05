<?php

namespace Ew;

use Ew\WpHelpers\Classes\Request_Validation_Result;
use Ew\WpHelpers\Services\Validation_Service;

/**
 * Class Locations_Service
 * @package Ew
 */
class Locations_Service extends Validation_Service {
	/**
	 * @var Locations_Repository
	 */
	private $location_repository;

	/**
	 * Locations_Service constructor.
	 */
	public function __construct() {
		$this->location_repository = new Locations_Repository();
	}

	/**
	 * Validate request for creating a new location
	 *
	 * @param $r
	 *
	 * @return Request_Validation_Result
	 */
	public function validate_create_request( $r ) {
		$result = new Request_Validation_Result();

		$required_fields = [
			'id',
			'location_name',
		];

		foreach ( $required_fields as $required_field ) {
			$this->not_empty( $r, $required_field, 'Location_Request' );
		}

		return $result;
	}

	/**
	 * Create location from request
	 *
	 * @param $r
	 *
	 * @return mixed
	 */
	public function create_from_request( $r ) {
		$location = new Location( $r );

		return $this->location_repository->save_location( $location );
	}
}