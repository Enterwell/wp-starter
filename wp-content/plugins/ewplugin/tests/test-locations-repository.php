<?php
/**
 * Class EventsRepositoryTest
 *
 * @package Ewplugin
 */

use \EWStarter\Location;
use \EWStarter\Locations_Repository;

/**
 * Locations repository test.
 * Simple tests example, tests repository methods.
 */
class Locations_Repository_Test extends EWPlugin_Test_Case {
	/**
	 * @var \EWStarter\Locations_Repository
	 */
	private $locations_repository;

	/**
	 * Activate plugin before
	 */
	public function setUp() {
		parent::setUp();
		$this->locations_repository = new Locations_Repository();
	}


	/**
	 * Tests location insert.
	 * @throws Exception
	 */
	public function test_location_insert() {
		$location       = new Location();
		$location->name = 'test';
		$saved          = $this->locations_repository->save_location( $location );

		// Test if the location was saved
		$this->assertTrue( ! empty( $saved->id ), '!empty($saved->id)' );

		global $wpdb;
		$locations_table = $wpdb->prefix . 'ew_locations';
		$count           = $wpdb->get_var( "SELECT COUNT(*) FROM $locations_table" );
		$count           = intval( $count );

		// Test if only one location is saved in the db
		$this->assertTrue( $count === 1, '$count === 1' );
	}

	/**
	 * Tests location update.
	 * @throws Exception
	 */
	public function test_location_update() {
		$location       = new Location();
		$location->name = 'test';
		$location       = $this->locations_repository->save_location( $location );

		$location->name = 'test2';
		$location       = $this->locations_repository->save_location( $location );

		// Test if the location was updated
		$this->assertNotEquals( 'test', $location->name, '$location->name != "test"' );
		$this->assertEquals( 'test2', $location->name, '$location->name == "test2"' );
	}
}
