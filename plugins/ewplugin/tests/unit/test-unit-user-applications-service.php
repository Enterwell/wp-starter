<?php

use DI\DependencyException;
use DI\NotFoundException;
use EwStarter\Exceptions\Test_Exception;
use EwStarter\Exceptions\Validation_Exception;
use EwStarter\Repositories\User_Applications_Repository;
use EwStarter\Services\Files_Service;
use EwStarter\Services\Interfaces\User_Applications_Service_Interface;
use EwStarter\Tests\Helpers\Unit_Test_Case;
use Mockery\MockInterface;

class Test_Unit_User_Applications_Service extends Unit_Test_Case {
	/**
	 * Gets service with mocked dependencies, calls
	 * setup methods on them if provided.
	 *
	 * @param callable|null $setup_user_mock
	 * @param callable|null $setup_files_mock
	 *
	 * @return User_Applications_Service_Interface
	 * @throws DependencyException
	 * @throws NotFoundException
	 */
	protected function get_mocked_service( ?callable $setup_user_mock = null, ?callable $setup_files_mock = null ): User_Applications_Service_Interface {
		$user_application_repository_mock = Mockery::mock( User_Applications_Repository::class );
		$files_service_mock               = Mockery::mock( Files_Service::class );

		if ( is_callable( $setup_user_mock ) ) {
			$setup_user_mock( $user_application_repository_mock );
		}
		if ( is_callable( $setup_files_mock ) ) {
			$setup_files_mock( $files_service_mock );
		}

		return $this->container->make( User_Applications_Service_Interface::class, [
			'user_applications_repository' => $user_application_repository_mock,
			'files_service'                => $files_service_mock
		] );
	}

	/**
	 * Tests create when the request data is not valid.
	 * Should throw validation exception before it reaches mocks.
	 *
	 * @return void
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws Validation_Exception
	 */
	public function test_create_user_application_invalid_request() {
		$this->expectException( Validation_Exception::class );

		$request_data = $this->entity_helper->get_test_user_application_request_data( function ( array &$request_data ) {
			unset( $request_data['firstName'] );
			unset( $request_data['invoiceFile'] );
		} );

		$service = $this->get_mocked_service();
		$service->create_user_application( $request_data );
	}

	/**
	 * We mock file service to throw exception, should be caught by the test.
	 * @return void
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws Validation_Exception
	 */
	public function test_create_user_application_failed_save_to_uploads() {
		$this->expectException( Test_Exception::class );

		$request_data = $this->entity_helper->get_test_user_application_request_data();

		$service = $this->get_mocked_service(
			null,
			function ( MockInterface $mock ) {
				$mock
					->shouldReceive( 'check_filetype_and_ext' )
					->withAnyArgs()
					->andThrow( Test_Exception::class );
			}
		);
		$service->create_user_application( $request_data );
	}

	/**
	 * Mock user applications repository to throw
	 * exception on save, check unlink call.
	 *
	 * @return void
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws Validation_Exception
	 */
	public function test_create_user_application_failed_db_save() {
		$this->expectException( Test_Exception::class );

		$request_data = $this->entity_helper->get_test_user_application_request_data();

		$service = $this->get_mocked_service(
			function ( MockInterface $mock ) {
				$mock
					->shouldReceive( 'save' )
					->withAnyArgs()
					->andThrow( Test_Exception::class);
			},
			function ( MockInterface $mock ) {
				$mock
					->shouldReceive( 'unlink' )
					->once();

				$mock
					->shouldReceive( 'check_filetype_and_ext' )
					->withAnyArgs()
					->andReturn( 'tmp-file.png' );

				$mock
					->shouldReceive( 'save_file_to_uploads' )
					->withAnyArgs()
					->andReturn( 'tmp-file.png' )
					->once();
			}
		);

		$service->create_user_application( $request_data );
	}

	/**
	 * Test the case when everything goes fine.
	 *
	 * @return void
	 * @throws DependencyException
	 * @throws NotFoundException
	 * @throws Validation_Exception
	 */
	public function test_create_user_application_ok() {
		$request_data     = $this->entity_helper->get_test_user_application_request_data();
		$user_application = $this->entity_helper->get_test_user_application();

		$service = $this->get_mocked_service(
			function ( MockInterface $mock ) use ( $user_application ) {
				$mock
					->shouldReceive( 'save' )
					->withAnyArgs()
					->andReturn( $user_application );
			},
			function ( MockInterface $mock ) {
				$mock
					->shouldReceive( 'check_filetype_and_ext' )
					->withAnyArgs()
					->andReturn( 'tmp-file.png' );

				$mock
					->shouldReceive( 'save_file_to_uploads' )
					->withAnyArgs()
					->andReturn( 'tmp-file.png' )
					->once();
			}
		);

		$result = $service->create_user_application( $request_data );

		$this->assertEquals( $result, $user_application );
	}
}
