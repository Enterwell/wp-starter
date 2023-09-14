<?php

register_rest_route( 'ithemes-security/rpc', 'global/detect-ip', [
	'methods'             => WP_REST_Server::CREATABLE,
	'args'                => [
		'proxy' => [
			'type'     => 'string',
			'enum'     => array_keys( ITSEC_Lib_IP_Detector::get_proxy_types() ),
			'required' => true,
		],
		'args'  => [
			'type'    => 'object',
			'default' => [],
		],
	],
	'callback'            => function ( $request ) {
		if ( 'detect-ip' === $request['proxy'] ) {
			if ( ! ITSEC_Modules::is_active( 'security-check-pro' ) ) {
				ITSEC_Modules::activate( 'security-check-pro' );
				ITSEC_Modules::load_module_file( 'active.php', 'security-check-pro' );
			}

			ITSEC_Modules::load_module_file( 'feedback.php', 'security-check' );
			ITSEC_Modules::load_module_file( 'utility.php', 'security-check-pro' );
			$scan = ITSEC_Security_Check_Pro_Utility::get_server_response();

			if ( is_wp_error( $scan ) ) {
				return $scan;
			}

			if ( empty( $scan['remote_ip'] ) ) {
				return new WP_Error(
					'itsec_missing_remote_ip',
					__( 'Could not detect IP header.', 'better-wp-security' )
				);
			}
		}

		$detector = ITSEC_Lib_IP_Detector::build_for_type( $request['proxy'], $request['args'] );
		$ip       = $detector->get();

		if ( ! $ip ) {
			return new WP_Error(
				'itsec_invalid_remote_ip',
				__( 'Identified IP was invalid.', 'better-wp-security' )
			);
		}

		return [ 'ip' => $ip ];
	},
	'permission_callback' => 'ITSEC_Core::current_user_can_manage',
] );
