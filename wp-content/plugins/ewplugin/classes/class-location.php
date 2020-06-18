<?php

namespace EWStarter;

/**
 * Class Location
 */
class Location {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * Location constructor.
	 *
	 * @param array $row
	 */
	public function __construct( array $row = [] ) {
		$this->id   = intval( $row['id'] );
		$this->name = $row['location_name'];
	}
}