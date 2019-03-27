<?php

namespace Ew;

/**
 * Class Events_Service
 * @package Ew
 */
class Events_Service {
	/**
	 * @var Events_Repository
	 */
	private $events_repository;

	/**
	 * Events_Service constructor.
	 */
	public function __construct() {
		$this->events_repository = new Events_Repository();
	}

	/**
	 * Function that is called on event save
	 *
	 * @param $wp_post
	 * @param $r
	 */
	public function on_save_event( $wp_post, $r ) {
		// Check if there is already an event with the given ID in the database
		$event = $this->events_repository->get_event_by_id( $wp_post->ID );

		// If there is no event in db, create new
		if ( empty( $event ) ) {
			$event = new Event( $wp_post );
		}

		// Fill the event variables with the values from the request
		$event->start_date = ! empty( $r['ew_event_start_date'] ) ? new \DateTime( $r['ew_event_start_date'] ) : '';
		$event->end_date   = ! empty( $r['ew_event_end_date'] ) ? new \DateTime( $r['ew_event_end_date'] ) : '';

		// Save the event in the db
		return $this->events_repository->save_event( $event );
	}
}