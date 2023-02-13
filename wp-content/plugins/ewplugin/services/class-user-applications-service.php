<?php

namespace EwStarter;

use Ew\WpHelpers\Classes\Request_Validation_Result;
use Ew\WpHelpers\Services\Validation_Service;

/**
 * Class User_Applications_Service
 *
 * Service used to validate and save user
 * application requests.
 *
 * @package EwStarter
 */
class User_Applications_Service extends Validation_Service {
	/**
	 * Invoice files folder name.
	 */
	const INVOICE_FILES_FOLDER = 'EwStarter-invoice-files';

	/**
	 * @var User_Applications_Repository
	 */
	private $user_applications_repository;

	/**
	 * @var Files_Service
	 */
	private $files_service;

	/**
	 * User_Applications_Service constructor.
	 * @throws \Exception
	 */
	public function __construct() {
		$this->user_applications_repository = new User_Applications_Repository();
		$this->files_service                = new Files_Service();
	}

	/**
	 * Validate user application create request.
	 *
	 * @param $r
	 *
	 * @return Request_Validation_Result
	 */
	public function validate_create_user_application_request( array $r ): Request_Validation_Result {
		// All required fields as in request
		$required_fields = [
			'firstName',
			'lastName',
			'email',
			'phone',
			'streetAndNumber',
			'city',
			'postalCode',
			'invoiceFile',
			'invoiceFileName',
		];

		// Validate that all fields are set and not empty
		$result = new Request_Validation_Result();
		foreach ( $required_fields as $required_field ) {
			$result->merge( $this->not_empty( $r, $required_field, 'User_Application_Create' ) );
		}

		// Skip additional validation if any field is empty
		if ( ! $result->is_valid() ) {
			return $result;
		}

		// Validate email
		if ( ! filter_var( $r['email'], FILTER_VALIDATE_EMAIL ) ) {
			$result->add_error_message( '[email] is not valid' );
		}

		// Validate file as valid base64
		if ( base64_decode( $r['invoiceFile'], true ) === false ) {
			$result->add_error_message( '[invoiceFile] is not valid base64' );
		}

		return $result;
	}


	/**
	 * Creates user application from the request data.
	 *
	 * @param array $r
	 *
	 * @return User_Application
	 * @throws Validation_Exception
	 * @throws \Exception
	 */
	public function create_user_application( array $r ): User_Application {
		// Validate request
		$validation_result = $this->validate_create_user_application_request( $r );

		// If the result is not valid throw request validation exception
		if ( ! $validation_result->is_valid() ) {
			throw new Validation_Exception( $validation_result->get_message() );
		}

		// Save invoice file to the filesystem
		$invoice_file_content = base64_decode( $r['invoiceFile'] );
		$invoice_file_name    = $r['invoiceFileName'];
		$invoice_file_path    = $this->save_invoice_file( $invoice_file_content, $invoice_file_name );

		try {
			// Save user application to the db
			$user_application                    = new User_Application();
			$user_application->first_name        = sanitize_text_field( $r['firstName'] );
			$user_application->last_name         = sanitize_text_field( $r['lastName'] );
			$user_application->email             = sanitize_email( $r['email'] );
			$user_application->phone             = sanitize_text_field( $r['phone'] );
			$user_application->street_and_number = sanitize_text_field( $r['streetAndNumber'] );
			$user_application->city              = sanitize_text_field( $r['city'] );
			$user_application->postal_code       = sanitize_text_field( $r['postalCode'] );
			$user_application->date_created      = new \DateTime();

			$user_application->invoice_file = $invoice_file_path;

			// Return saved user application
			return $this->user_applications_repository->save( $user_application );
		} catch ( \Exception $e ) {
			if ( ! empty( $invoice_file_path ) ) {
				// Delete saved invoice file
				unlink( $invoice_file_path );
			}
			throw $e;
		}
	}

	/**
	 * Validates and saves invoice file.
	 *
	 * @param string $invoice_file_content
	 * @param string $invoice_file_name
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function save_invoice_file( string $invoice_file_content, string $invoice_file_name ): string {
		// Save file to tmp location
		$tmp_file = $this->files_service->save_file_to_uploads( $invoice_file_content, $invoice_file_name, 'tmp' );

		// WordPress validate file and extension
		$validate = wp_check_filetype_and_ext( $tmp_file, $invoice_file_name );

		// Remove tmp file
		unlink( $tmp_file );

		if ( $validate['ext'] === false ) {
			throw new \Exception( 'File type/ext is not valid!' );
		}

		if ( ! empty( $validate['proper_filename'] ) ) {
			// Set proper file name
			$invoice_file_name = $validate['proper_filename'];
		}

		return $this->files_service->save_file_to_uploads( $invoice_file_content, $invoice_file_name, static::INVOICE_FILES_FOLDER );
	}
}
