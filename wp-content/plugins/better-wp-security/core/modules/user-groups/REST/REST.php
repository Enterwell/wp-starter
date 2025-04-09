<?php

namespace iThemesSecurity\User_Groups\REST;

use iThemesSecurity\Contracts\Runnable;

class REST implements Runnable {

	/** @var \WP_REST_Controller[] */
	private $controllers;

	/**
	 * REST constructor.
	 *
	 * @param \WP_REST_Controller[] $controllers
	 */
	public function __construct( array $controllers ) { $this->controllers = $controllers; }

	public function run() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		foreach ( $this->controllers as $controller ) {
			$controller->register_routes();
		}
	}
}
