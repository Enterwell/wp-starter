<?php

namespace EwStarter\Tests\Helpers;

use EwStarter\Helpers\Random_Values_Helper;
use EwStarter\Models\User_Application;

/**
 * Class used to create test entities.
 */
class Test_Entity_Helper {
	public function get_test_user_application_request_data( ?callable $modify_request_data = null ): array {
		$random_string = Random_Values_Helper::get_random_string( 5 );
		$invoice_file  = file_get_contents(PLUGIN_DIR . '/tests/helpers/assets/invoice-base64.txt');

		$request_data = [
			'firstName'       => "John$random_string",
			'lastName'        => "Doe$random_string",
			'email'           => "jdoe$random_string@doe.com",
			'phone'           => "+000000$random_string",
			'streetAndNumber' => "Baker St.$random_string 150",
			'city'            => "Split$random_string",
			'postalCode'      => '10000',
			'invoiceFile'     => $invoice_file,
			'invoiceFileName' => 'jd-invoice.jpg',
		];

		if ( is_callable( $modify_request_data ) ) {
			$modify_request_data( $request_data );
		}

		return $request_data;
	}

	public function get_test_user_application( ?callable $modify_application = null ): User_Application {
		$random_string                       = Random_Values_Helper::get_random_string( 5 );
		$user_application                    = new User_Application();
		$user_application->first_name        = "John{$random_string}";
		$user_application->last_name         = "Doe{$random_string}";
		$user_application->email             = "john{$random_string}@doe.com";
		$user_application->phone             = $random_string;
		$user_application->street_and_number = "Baker Street $random_string 10";
		$user_application->city              = $random_string;
		$user_application->postal_code       = '10000';
		$user_application->invoice_file      = "/path-to/file$random_string.jpg";

		if ( $modify_application !== null ) {
			$modify_application( $user_application );
		}

		return $user_application;
	}
}
