<?php

add_action( 'admin_enqueue_scripts', function () {
	$preload_requests = [
		[
			'route' => '/ithemes-security/v1/logs',
			'query' => [
				'module'   => [ 'firewall', 'lockout' ],
				'code'     => [ 'BLOCK::%', 'host-lockout::%', 'host-triggered-blacklist' ],
				'per_page' => 20,
			],
			'embed' => true,
		],
		[
			'route' => '/ithemes-security/rpc/lockout-stats/top-blocked-ips',
			'query' => [ 'period' => '30-days' ],
		],
		[
			'route' => '/ithemes-security/v1/dashboard/events',
			'query' => [
				'events' => [
					'local-brute-force',
					'network-brute-force',
					'firewall-block',
				],
				'period' => '30-days',
			],
		],
	];

	$preload = ITSEC_Lib::preload_rest_requests( $preload_requests );

	wp_enqueue_script( 'itsec-pages-firewall' );
	wp_enqueue_style( 'itsec-pages-firewall' );
	wp_add_inline_script(
		'itsec-pages-firewall',
		sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload ) )
	);

	foreach ( ITSEC_Modules::get_active_modules_to_run() as $module ) {
		$handle = "itsec-{$module}-firewall";

		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_enqueue_script( $handle );
		}

		if ( wp_style_is( $handle, 'registered' ) ) {
			wp_enqueue_style( $handle );
		}
	}
} );

add_action( 'itsec-page-show', function () {
	echo '<div id="itsec-firewall-root"></div>';
} );
