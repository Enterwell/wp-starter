<?php

namespace iThemesSecurity\Lib\REST;

class Geolocation_Controller extends \WP_REST_Controller {

	protected $namespace = 'ithemes-security/v1';
	protected $rest_base = 'geolocate';

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<ip>.+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => [
					'ip' => [
						'type'   => 'string',
						'format' => 'ip',
					],
				],
			],
		] );
	}

	public function get_item( $request ) {
		return \ITSEC_Lib_Geolocation::geolocate( $request['ip'] );
	}
}
