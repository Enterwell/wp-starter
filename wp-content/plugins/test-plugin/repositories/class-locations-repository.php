<?php

namespace Ew;

use Ew\WpHelpers\Classes\Db_Data;
use Ew\WpHelpers\Repositories\ARepository;

/**
 * Class Locations_Repository
 * @package Ew
 */
class Locations_Repository extends ARepository {
	/**
	 * Locations_Repository constructor.
	 */
	public function __construct() {
		parent::__construct( 'ew_locations' );
	}

	/**
	 * Save location in db
	 *
	 * @param $location
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function save_location( $location ) {
		$data = $this->get_db_data( $location );

		if ( $this->location_row_exists( $location->id ) ) {
			$result = $this->db->update( $this->table_name, $data['values'], [ 'id' => $location->id ] );
			if ( $result === false ) {
				throw new \Exception( 'Event update failed' );
			}
		} else {
			$result = $this->db->insert( $this->table_name, $data['values'] );
			if ( $result === false ) {
				throw new \Exception( 'Event insert failed' );
			}
		}

		return $location;
	}

	/**
	 * Get db data from Location object
	 * @param $location
	 *
	 * @return array
	 */
	private function get_db_data( $location ) {
		$db_data = new Db_Data();
		$db_data->add_data( 'id', $location->id, '%d' );
		$db_data->add_data( 'location_name', $location->name, '%s' );

		return $db_data->get_data();
	}

	/**
	 * Check if row with the given id already exists in database
	 * @param $id
	 *
	 * @return bool
	 */
	private function location_row_exists( $id ) {
		$query  = $this->db->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", intval( $id ) );
		$result = $this->db->get_row( $query );

		return ! empty( $result );
	}

	/**
	 * Constructs object instance from table row and additional object data.
	 * Additional data could be WP_Post object or any other data related
	 * to object that is not stored in object table.
	 *
	 * @param array $table_row
	 * @param null $object_data
	 *
	 * @return Location
	 * @throws \Exception
	 */
	protected function _construct_object( $table_row, $object_data = null ) {
		return new Location( $table_row );
	}
}