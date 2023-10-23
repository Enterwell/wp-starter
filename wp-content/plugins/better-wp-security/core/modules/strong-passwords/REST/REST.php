<?php

namespace iThemesSecurity\Modules\Strong_Passwords\REST;

use iThemesSecurity\Contracts\Runnable;

class REST implements Runnable {

	private $controllers;

	public function __construct( \WP_REST_Controller ...$controllers ) { $this->controllers = $controllers; }

	public function run() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		foreach ( $this->controllers as $controller ) {
			$controller->register_routes();
		}
	}
}
