<?php
/**
 * Class User_Applications_Repository_Test
 *
 * @package Ewplugin
 */

use DI\DependencyException;
use DI\NotFoundException;
use EwStarter\Models\User_Application;
use EwStarter\Repositories\Interfaces\User_Applications_Repository_Interface;
use EwStarter\Tests\Helpers\Test_Case;

/**
 * User application repository test.
 *
 * User application GET and CREATE is tested.
 * User application EDIT is not tested since usually we don't edit once
 * created user applications.
 */
class User_Applications_Repository_Test extends Test_Case {
	/**
	 * @var User_Applications_Repository_Interface
	 */
	private User_Applications_Repository_Interface $user_applications_repository;

	/**
	 * @param string|null $name
	 * @param array $data
	 * @param string $dataName
	 *
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	public function __construct( ?string $name = null, array $data = [], string $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );

		$this->user_applications_repository = $this->container->get( User_Applications_Repository_Interface::class );
	}

	/**
	 * Test user application create.
	 */
	public function test_user_application_save() {
		// Create test user application
		$user_application = $this->entity_helper->get_test_user_application();

		// Save user application
		$user_application = $this->user_applications_repository->save( $user_application );

		// Assert that id is filled
		$this->assertNotEmpty( $user_application->id, '!empty($user_application->id)' );

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
		$user_application = $this->entity_helper->get_test_user_application( function ( User_Application $application ) {
			unset( $application->invoice_file );
		} );

		$this->user_applications_repository->save( $user_application );
	}

	/**
	 * Test user application save without first name and email.
	 * @throws Exception
	 */
	public function test_user_application_validation_user_data() {
		// Expect exception
		$this->expectException( Exception::class );

		// Create user application without user data
		$user_application = $this->entity_helper->get_test_user_application( function ( User_Application $application ) {
			unset( $application->first_name );
			unset( $application->email );
		} );


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
		$user_application = $this->entity_helper->get_test_user_application( function ( User_Application $application ) {
			$application->email = 'invalid@email';
		} );

		$this->user_applications_repository->save( $user_application );
	}
}
