<?php

namespace iThemesSecurity\Site_Scanner\REST;

use iThemesSecurity\Contracts\Runnable;

class REST implements Runnable {
	/** @var \WP_REST_Controller[] */
	private $controllers;

	public function __construct( \WP_REST_Controller ...$controllers ) {
		$this->controllers = $controllers;
	}

	public function run() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		foreach ( $this->controllers as $controller ) {
			$controller->register_routes();
		}

		register_rest_route( 'ithemes-security/v1', 'site-scanner/verify-scan', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_verification_request' ],
			'permission_callback' => '__return_true',
			'args'                => [
				'secret' => [
					'type'      => 'string',
					'required'  => true,
					'minLength' => 1,
				],
			],
		] );
	}

	public function handle_verification_request( \WP_REST_Request $request ) {
		$public = \ITSEC_Site_Scanner_API::get_public_key( $request['secret'] );

		if ( is_wp_error( $public ) ) {
			\ITSEC_Log::add_debug( 'site-scanner', 'verify-scan::invalid_secret', [
				'params' => $request->get_params(),
				'error'  => $public->get_error_code(),
			] );

			$public->add_data( [ 'status' => \WP_Http::OK ] );

			return $public;
		}

		\ITSEC_Site_Scanner_API::clear_key_pair( $request['secret'] );
		\ITSEC_Log::add_debug( 'site-scanner', 'verify-scan::valid_secret', [
			'params' => $request->get_params(),
			'public' => $public,
		] );

		return new \WP_REST_Response( [ 'public' => $public ] );
	}

}
