<?php

namespace Ew;

use Ew\WpHelpers\Classes\Db_Data;
use Ew\WpHelpers\Classes\Request_Validation_Result;
use Ew\WpHelpers\Repositories\ARepository;

/**
 * Class Events_Repository
 * @package Ew
 */
class Events_Repository extends ARepository {
	/**
	 * Events_Repository constructor.
	 */
	public function __construct() {
		parent::__construct( 'ew_events' );
	}

	/**
	 * Get the event from database with the given id
	 * @param $event_id
	 *
	 * @return bool|mixed
	 */
	public function get_event_by_id( $event_id ) {
		$wp_post = get_post( $event_id );
		return $this->_get_single_by_field( 'id', intval( $event_id ), '%d', $wp_post );
	}

	/**
	 * Save event to db
	 * @param $event
	 *
	 * @throws \Exception
	 */
	public function save_event( $event ) {
		$this->validate_event( $event );

		$data = $this->get_db_data( $event );

		if ( $this->event_row_exists( $event->id ) ) {
			$result = $this->db->update( $this->table_name, $data['values'], [ 'id' => $event->id ] );
			if ( $result === false ) {
				throw new \Exception( 'Event update failed' );
			}
		} else {
			$result = $this->db->insert( $this->table_name, $data['values'] );
			if ( $result === false ) {
				throw new \Exception( 'Event insert failed' );
			}
		}
	}

	/**
	 * Validate the given event
	 * @param $event
	 *
	 * @throws \Exception
	 */
	public function validate_event( $event ) {
		$result = new Request_Validation_Result();

		if ( empty( $event->id ) ) {
			$result->add_message( 'post_id is not set' );
			$result->set_valid( false );
		}

		if ( ! $result->is_valid() ) {
			throw new \Exception( $result->get_message() );
		}
	}

	/**
	 * Get db data from Event object
	 *
	 * @param $event
	 *
	 * @return array
	 */
	private function get_db_data( $event ) {
		$data = new Db_Data();
		$data->add_data( 'id', $event->id, '%d' );
		$data->add_data( 'start_date', $event->start_date->format(EW_DATE_FORMAT), '%s' );
		$data->add_data( 'end_date', $event->end_date->format(EW_DATE_FORMAT), '%s' );

		return $data->get_data();
	}

	/**
	 * Checks if row with such id already exists in the events table
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function event_row_exists( $id ) {
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
	 * @return Event
	 * @throws \Exception
	 */
	protected function _construct_object( $table_row, $object_data = null ) {
		return new Event( $object_data, $table_row );
	}
}