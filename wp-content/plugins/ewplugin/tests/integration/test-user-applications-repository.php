<?php
/**
 * Class User_Applications_Repository_Test
 *
 * @package Ewplugin
 */

/**
 * User application repository test.
 *
 * User application GET and CREATE is tested.
 * User application EDIT is not tested since usually we don't edit once
 * created user applications.
 */
class User_Applications_Repository_Test extends Plugin_Test_Case {
	/**
	 * @var \EwStarter\User_Applications_Repository
	 */
	private $user_applications_repository;

	/**
	 * Activate plugin before
	 * @throws Exception
	 */
	public function set_up() {
		parent::set_up();
		$this->user_applications_repository = new \EwStarter\User_Applications_Repository();
	}

	/**
	 * Returns user application for testing.
	 * @return \EwStarter\User_Application
	 */
	private function get_test_user_application() {
		$user_application                    = new \EwStarter\User_Application();
		$user_application->first_name        = 'John';
		$user_application->last_name         = 'Doe';
		$user_application->email             = 'john@doe.com';
		$user_application->phone             = '0000000';
		$user_application->street_and_number = 'Baker Street 10';
		$user_application->city              = 'Zagreb';
		$user_application->postal_code       = '10000';
		$user_application->invoice_file      = '/path-to/file.jpg';

		return $user_application;
	}

	/**
	 * Test user application create.
	 */
	public function test_user_application_save() {
		// Create test user application
		$user_application = $this->get_test_user_application();

		// Save user application
		$user_application = $this->user_applications_repository->save( $user_application );

		// Assert that id is filled
		$this->assertNotEmpty($user_application->id, '!empty($user_application->id)');

		// Get user application from the db
		$db_user_application = $this->user_applications_repository->get( $user_application->id );

		// Assert all fields are saved and loaded correctly
		$this->assertEquals( $user_application->first_name, $db_user_application->first_name, 'first_name === first_name' );
		$this->assertEquals( $user_application->last_name, $db_user_application->last_name, 'last_name === last_name' );
		$this->assertEquals( $user_application->email, $db_user_application->email, 'email === email' );
		$this->assertEquals( $user_application->phone, $db_user_application->phone, 'phone === phone' );
		$this->assertEquals( $user_application->street_and_number, $db_user_application->street_and_number, 'street_and_number === street_and_number' );
		$this->assertEquals( $user_application->city, $db_user_application->city, 'city === city' );
		$this->assertEquals( $user_application->postal_code, $db_user_application->postal_code, 'postal_code === postal_code' );
		$this->assertEquals( $user_application->invoice_file, $db_user_application->invoice_file, 'invoice_file === invoice_file' );
		$this->assertEquals( $user_application->date_created->format( DATE_ATOM ), $db_user_application->date_created->format( DATE_ATOM ), 'date_created->format(DATE_ATOM) === date_created->format(DATE_ATOM)' );
	}

	/**
	 * Test user application save without invoice file.
	 * @throws Exception
	 */
	public function test_user_application_validation_invoice_file() {
		// Expect exception
		$this->expectException( Exception::class );

		// Create user application without invoice file
		$user_application = $this->get_test_user_application();
		unset( $user_application->invoice_file );

		$this->user_applications_repository->save( $user_application );
	}

	/**
	 * Test user application save without first name and email.
	 * @throws Exception
	 */
	public function test_user_application_validation_user_data() {
		// Expect exception
		$this->expectException( Exception::class );

		// Create user application without invoice file
		$user_application = $this->get_test_user_application();
		unset( $user_application->first_name );
		unset( $user_application->email );

		$this->user_applications_repository->save( $user_application );
	}

	/**
	 * Test user application save with invalid email format.
	 * @throws Exception
	 */
	public function test_user_application_validation_invalid_email() {
		// Expect exception
		$this->expectException( Exception::class );

		// Create user application without invoice file
		$user_application        = $this->get_test_user_application();
		$user_application->email = 'invalid@email';

		$this->user_applications_repository->save( $user_application );
	}
}
