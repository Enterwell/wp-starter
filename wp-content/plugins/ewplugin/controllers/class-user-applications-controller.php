<?php
/**
 * Created by PhpStorm.
 * User: mateikki
 * Date: 31.8.2020.
 * Time: 14:48
 */

namespace EwStarter\Controllers;

use EwStarter\Services\Interfaces\User_Applications_Service_Interface;
use Exception;
use WP_REST_Request;
use WP_REST_Response;
use EwStarter\Exceptions\Validation_Exception;

/**
 * Class User_Applications_Controller
 * @package EwStarter
 */
class User_Applications_Controller extends APlugin_Controller {
	/**
	 * @var User_Applications_Service_Interface
	 */
	private User_Applications_Service_Interface $user_applications_service;

	/**
	 * User_Applications_Controller constructor.
	 * @throws Exception
	 */
	public function __construct( User_Applications_Service_Interface $user_applications_service ) {
		parent::__construct( 'user-applications' );

		$this->user_applications_service = $user_applications_service;
	}

	/**
	 * Registers all controller routes.
	 * In this function all routes for this controller are registered
	 * using WordPress function register_route
	 *
	 * @since   1.0.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_namespace(),
			"/" . $this->route,
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'create_user_application' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Creates user application
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function create_user_application( WP_REST_Request $request ): WP_REST_Response {
		try {
			// Create user application from params
			$user_application = $this->user_applications_service->create_user_application( $request->get_params() );

			return $this->ok( $user_application );
		} catch ( Validation_Exception $e ) {
			return $this->bad_request( $e->getMessage() );
		} catch ( Exception $e ) {
			return $this->exception_response( $e );
		}
	}
}
