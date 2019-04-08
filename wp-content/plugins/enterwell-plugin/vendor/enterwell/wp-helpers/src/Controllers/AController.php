<?php

//  Enterwell namespace
namespace Ew\WpHelpers\Controllers;
use Ew\WpHelpers\Exceptions\AEw_Exception;

/**
 * Abstract class for WP REST Api controllers.
 *
 * Each plugin that makes use of this controller should
 * have its own abstract controller class that will be inherited
 * by other controllers in the plugin.
 *
 * For example if we have Books plugin than we'll have:
 *
 * ABooksController - controller that implements methods:
 * get_base_route
 * get_version
 *
 * Method register_routes is left to implement to other controllers in plugin.
 *
 * By doing so we'll have unified base route and version for all controllers in our plugin
 * as well as couple of helpful methods in each.
 *
 * @since       1.0.0
 *
 * @package     Ew\WpHelpers
 * @subpackage  Ew\WpHelpers\controllers
 * @author      Matej Bosnjak <matej.bosnjak@enterwell.net>
 */
abstract class AController {

	/**
	 * Controllers route.
	 * Each controller must have its own route, route defines
	 * REST resource that is managed with controller.
	 *
	 * @since   1.0.0
	 *
	 * @var string  Controller's route.
	 */
	protected $route;


	/**
	 * Controller constructor.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $route
	 */
	public function __construct( $route ) {
		$this->route = $route;
	}

	/**
	 * Registers all controller routes.
	 * In this function all routes for this controller are registered
	 * using WordPress function register_route
	 *
	 * @since   1.0.0
	 */
	public abstract function register_routes();

	#region Permission callback helpers

	/**
	 * Check if request nonce is valid.
	 * Mostly used in register_routes function as
	 * permissions callback.
	 * @since   1.0.0
	 *
	 * @param   \WP_REST_Request $request
	 *
	 * @return  bool
	 */
	public function is_nonce_valid( $request ) {
		// Logged in users does not have to send nonces
		if ( $this->is_user_logged_in() ) {
			return true;
		}

		// Get nonce from headera
		$nonce = $request->get_header( 'X-WP-Nonce' );

		// If there is no nonce in headers
		// look for it in request body
		if ( empty( $nonce ) ) {
			// Get request params
			$params = $request->get_params();

			if ( ! empty( $params['_wpnonce'] ) ) {
				$nonce = $params['_wpnonce'];
			}
		}

		return wp_verify_nonce( $nonce, 'wp_rest' );
	}

	/**
	 * Checks if user is logged in.
	 * Mostly used in register_routes function as
	 * permissions callback.
	 *
	 * @since   1.0.0
	 *
	 * @return bool
	 */
	public function is_user_logged_in() {
		return is_user_logged_in();
	}

	/**
	 * Check if administrator is logged in.
	 * Mostly used in register_routes function as
	 * permissions callback.
	 *
	 * @since   1.0.0
	 *
	 * @return  bool
	 */
	public function is_admin() {
		return current_user_can( 'administrator' );
	}

	#endregion

	/**
	 * Get current controller's namespace.
	 * All controller routes should be in controller's namespace.
	 *
	 * Used in register_routes function when registering new routes.
	 *
	 * @since   1.0.0
	 *
	 * @return string   This controller's namespace.
	 */
	protected function get_namespace() {
		return implode( "/", [ $this->get_base_route(), $this->get_version() ] );
	}

	/**
	 * Gets controller base route.
	 * Controller base route is initial route
	 * for all controllers in one plugin, for example all plugins in
	 * Books plugin will have routes like:
	 *
	 * /wp-json/books-plugin/v1/{route-name}
	 *
	 * 'books-plugin' is base route for books plugin.
	 *
	 * @since   1.0.0
	 * @return  string
	 */
	public abstract function get_base_route();

	/**
	 * Gets controller api version.
	 * For example all plugins in
	 * Books plugin will have routes like:
	 *
	 * /wp-json/books-plugin/v1/{route-name}
	 *
	 * 'v1' is API version for our Books plugin.
	 *
	 * @since   1.0.0
	 * @return  string
	 */
	public abstract function get_version();

	/**
	 * Returns ok response.
	 *
	 * @since   1.0.0
	 *
	 * @param   mixed $arg
	 * @param   int $ok_code
	 *
	 * @return  \WP_REST_Response
	 */
	protected function ok( $arg, $ok_code = 200 ) {

		// Call to_json on JsonSerializable objects.
		$response_object = $arg instanceof \JsonSerializable ? $arg->jsonSerialize() : $arg;

		// Return response with code.
		return new \WP_REST_Response( $response_object, $ok_code );
	}

	/**
	 * Returns bad request response.
	 *
	 * @since   1.0.0
	 *
	 * @param   mixed $message
	 * @param   int $bad_request_code
	 *
	 * @return  \WP_REST_Response
	 */
	protected function bad_request( $message, $bad_request_code = 400 ) {

		return new \WP_REST_Response( $message, $bad_request_code );
	}

	/**
	 * General error response.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $message
	 *
	 * @return  \WP_REST_Response
	 */
	protected function error_response( $message ) {
		return $this->exception_response( new \Exception( $message ) );
	}

	/**
	 * Gets response for exception.
	 *
	 * @since   1.0.0
	 *
	 * @param   \Exception $exception
	 *
	 * @return  \WP_REST_Response
	 */
	protected function exception_response( \Exception $exception ) {

		if ( $exception instanceof AEw_Exception ) {
			return new \WP_REST_Response( $exception, $exception->get_exception_http_code() );
		} else {
			return new \WP_REST_Response( $exception->getMessage(), 500 );
		}
	}
}