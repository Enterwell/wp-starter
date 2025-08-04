<?php

namespace EwStarter\Services\Interfaces;

use Ew\WpHelpers\Classes\Request_Validation_Result;
use EwStarter\Exceptions\Validation_Exception;
use EwStarter\Models\User_Application;
use Exception;

/**
 * User application service interface.
 */
interface User_Applications_Service_Interface {
	/**
	 * Validate user application create request.
	 *
	 * @param array $r
	 *
	 * @return Request_Validation_Result
	 */
	function validate_create_user_application_request( array $r ): Request_Validation_Result;


	/**
	 * Creates user application from the request data.
	 *
	 * @param array $r
	 *
	 * @return User_Application
	 * @throws Validation_Exception
	 * @throws Exception
	 */
	function create_user_application( array $r ): User_Application;
}
