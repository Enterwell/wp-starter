<?php

use iThemesSecurity\Dashboard\REST\Events_Controller;

/**
 * Class ITSEC_Dashboard_REST
 */
class ITSEC_Dashboard_REST {

	public function run() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_filter( 'rest_route_data', array( $this, 'filter_route_data' ) );
		add_filter( 'rest_pre_dispatch', array( $this, 'handle_options_request' ), 100, 3 );
		add_filter( 'rest_request_before_callbacks', array( $this, 'return_permission_errors_before_validation' ), 10, 3 );
	}

	/**
	 * Register dashboard REST routes.
	 */
	public function register_routes() {
		$available = new ITSEC_REST_Dashboard_Available_Cards_Controller();
		$available->register_routes();

		$dashboards = new ITSEC_REST_Dashboard_Dashboards_Controller();
		$dashboards->register_routes();

		$layout = new ITSEC_REST_Dashboard_Layout_Controller();
		$layout->register_routes();

		$cards = new ITSEC_REST_Dashboard_Cards_Controller();
		$cards->register_routes();

		$static = new ITSEC_REST_Dashboard_Static_Controller();
		$static->register_routes();

		$events = new Events_Controller();
		$events->register_routes();
	}

	/**
	 * Remove any dashboard routes from the index.
	 *
	 * @param array $available
	 *
	 * @return array
	 */
	public function filter_route_data( $available ) {

		foreach ( $available as $route => $data ) {
			if ( strpos( $route, '/ithemes-security/v1/dashboards' ) === 0 ) {
				unset( $available[ $route ] );
			}
		}

		return $available;
	}

	/**
	 * Return an empty response for any options requests to the dashboard.
	 *
	 * @param WP_REST_Response $response
	 * @param WP_REST_Server   $server
	 * @param WP_REST_Request  $request
	 *
	 * @return WP_REST_Response
	 */
	public function handle_options_request( $response, $server, $request ) {

		if ( 'OPTIONS' !== $request->get_method() ) {
			return $response;
		}

		if ( strpos( $request->get_route(), '/ithemes-security/v1/dashboards' ) === false ) {
			return $response;
		}

		$response->set_data( array() );

		return $response;
	}

	/**
	 * Return permission errors before a validation error.
	 *
	 * @param WP_REST_Response|WP_Error $response
	 * @param array                     $handler
	 * @param WP_REST_Request           $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function return_permission_errors_before_validation( $response, $handler, $request ) {

		if ( ! is_wp_error( $response ) || strpos( $request->get_route(), '/ithemes-security/v1/dashboards' ) === false ) {
			return $response;
		}

		if ( ! empty( $handler['permission_callback'] ) ) {
			$permission = call_user_func( $handler['permission_callback'], $request );

			if ( is_wp_error( $permission ) ) {
				$response = $permission;
			} elseif ( false === $permission || null === $permission ) {
				$response = new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to do that.', 'better-wp-security' ), array( 'status' => rest_authorization_required_code() ) );
			}
		}

		return $response;
	}

	/**
	 * Return either the masked not found error, or the given error if the user can manage ITSEC or debug mode is enabled.
	 *
	 * @param WP_Error|null $error
	 *
	 * @return WP_Error
	 */
	public static function not_found_error( $error = null ) {
		if ( $error && ( ( defined( 'ITSEC_DEBUG' ) && ITSEC_DEBUG ) || ITSEC_Core::current_user_can_manage() ) ) {
			return $error;
		}

		return new WP_Error( 'rest_no_route', __( 'No route was found matching the URL and request method', 'better-wp-security' ), array( 'status' => 404 ) );
	}

	/**
	 * Get the definition for the period collection param.
	 *
	 * @return array
	 */
	public static function get_period_arg() {
		return array(
			'default' => ITSEC_Dashboard_Util::P_30_DAYS,
			'oneOf'   => array(
				array(
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => array(
						'start' => array(
							'type'     => 'string',
							'format'   => 'date-time',
							'required' => true,
						),
						'end'   => array(
							'type'     => 'string',
							'format'   => 'date-time',
							'required' => true,
						),
					),
				),
				array(
					'type' => 'string',
					'enum' => array( ITSEC_Dashboard_Util::P_24_HOURS, ITSEC_Dashboard_Util::P_WEEK, ITSEC_Dashboard_Util::P_30_DAYS )
				),
			),
		);
	}
}
