<?php

namespace iThemesSecurity\Lib\REST;

class Lockout_Stats_Controller extends \WP_REST_Controller {
	protected $namespace = 'ithemes-security/rpc';
	protected $rest_base = 'lockout-stats';

	/** @var \ITSEC_Lockout */
	protected $lockout;

	public function __construct( \ITSEC_Lockout $lockout ) { $this->lockout = $lockout; }

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base . '/top-blocked-ips', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_top_blocked_ips' ],
			'permission_callback' => 'ITSEC_Core::current_user_can_manage',
			'args'                => [
				'period' => \ITSEC_Lib_REST::get_period_arg(),
				'number' => [
					'type'    => 'integer',
					'minimum' => 0,
					'maximum' => 10,
					'default' => 5,
				],
			],
		] );
	}

	public function get_top_blocked_ips( \WP_REST_Request $request ) {
		$parsed_period = \ITSEC_Lib_REST::parse_period_arg( $request['period'] );

		if ( is_wp_error( $parsed_period ) ) {
			return $parsed_period;
		}

		$ips = $this->lockout->get_top_blocked_ips(
			$request['number'],
			$parsed_period[0],
			$parsed_period[1]
		);

		if ( ! $ips->is_success() ) {
			return $ips->as_rest_response();
		}

		$formatted = [];

		foreach ( $ips->get_data() as $ip ) {
			$data = [
				'ip'    => $ip['ip'],
				'count' => $ip['count'],
			];

			if ( \ITSEC_Modules::is_active( 'geolocation' ) ) {
				$location = \ITSEC_Lib_Geolocation::geolocate( $ip['ip'] );

				$data['location'] = is_wp_error( $location ) ? null : $location;
			}

			$formatted[] = $data;
		}

		return $formatted;
	}
}
