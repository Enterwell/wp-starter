<?php

use EwStarter\Exceptions\Validation_Exception;
use EwStarter\Models\User_Application;
use EwStarter\Repositories\User_Applications_Repository;
use EwStarter\Services\Interfaces\User_Applications_Service_Interface;
use EwStarter\Services\User_Applications_Service;
use EwStarter\Tests\Helpers\Test_Case;
use Mockery\MockInterface;

/**
 * User applications service tests.
 *
 * Class Test_User_Applications_Service
 */
class Test_User_Applications_Service extends Test_Case {
	/**
	 * @var User_Applications_Service
	 */
	private User_Applications_Service $user_applications_service;

	public function __construct( ?string $name = null, array $data = [], string $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );

		$this->user_applications_service = $this->container->get( User_Applications_Service_Interface::class );
	}

	/**
	 * Tests user application create.
	 * @throws Validation_Exception
	 */
	public function test_user_application_create() {
		$request_data     = $this->entity_helper->get_test_user_application_request_data();
		$user_application = $this->user_applications_service->create_user_application( $request_data );

		// Id was set up - application is created
		$this->assertNotEmpty( $user_application->id, '!empty($user_application->id)' );

		// User application file was created
		$this->assertTrue( file_exists( $user_application->invoice_file ), 'file_exists($user_application->invoice_file)' );

		// Clean up test files
		unlink( $user_application->invoice_file );
	}

	/**
	 * Tests user application that has invalid image file extension.
	 * @throws Validation_Exception
	 */
	public function test_user_application_create_invalid_image_extension() {
		$request_data = $this->entity_helper->get_test_user_application_request_data( function ( array &$request_data ) {
			// Change filename extension to pdf - should be renamed
			$request_data['invoiceFileName'] = 'jd-invoice.png';
		} );

		$user_application = $this->user_applications_service->create_user_application( $request_data );

		// User application file was created
		$this->assertTrue( file_exists( $user_application->invoice_file ), 'file_exists($user_application->invoice_file)' );

		// Check that filename is changed
		$invoice_file_name = basename( $user_application->invoice_file );

		// Invoice file name should be changed to correct extension
		$this->assertTrue( $invoice_file_name !== $request_data['invoiceFileName'], '$invoice_file_name !== $request_data[\'invoiceFileName\']' );
		$this->assertTrue( $invoice_file_name === 'jd-invoice.jpg', '$invoice_file_name === \'jd-invoice.jpg\'' );

		// Clean up test files
		unlink( $user_application->invoice_file );
	}

	/**
	 * Tests user application create with validation fields.
	 * @throws Validation_Exception
	 */
	public function test_user_application_create_validation_fields() {
		// We expect validation exception
		$this->expectException( Validation_Exception::class );

		$request_data = $this->entity_helper->get_test_user_application_request_data( function ( array &$request_data ) {
			unset( $request_data['firstName'] );
			unset( $request_data['invoiceFileName'] );
		} );

		$this->user_applications_service->create_user_application( $request_data );
	}

	/**
	 * Tests user application create with validation - image base64 is not valid.
	 * @throws Validation_Exception
	 */
	public function test_user_application_create_validation_image_invalid_base64() {
		// We expect validation exception
		$this->expectException( Validation_Exception::class );

		$request_data = $this->entity_helper->get_test_user_application_request_data( function ( array &$request_data ) {
			$request_data['invoiceFile'] = 'invalid-base-64';
		} );


		$this->user_applications_service->create_user_application( $request_data );
	}

	/**
	 * Tests user application create with validation - base64 file is not an image.
	 * @throws Validation_Exception
	 */
	public function test_user_application_create_validation_not_image_base64() {
		// We expect validation exception
		$this->expectException( Exception::class );

		$request_data = $this->entity_helper->get_test_user_application_request_data( function ( array &$request_data ) {
			// File is .pptx file encoded, name is image name
			$request_data['invoiceFile'] = file_get_contents( PLUGIN_DIR . '/tests/helpers/assets/pptx-base64.txt' );
		} );

		$this->user_applications_service->create_user_application( $request_data );
	}
}
