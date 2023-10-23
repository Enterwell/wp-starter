<?php

add_action( 'admin_enqueue_scripts', function () {
	$preload_requests = [
		[
			'route' => '/ithemes-security/v1/site-scanner/scans',
			'query' => [ 'per_page' => 100 ],
		],
		[
			'route' => '/ithemes-security/v1/site-scanner/vulnerabilities',
			'query' => [ 'per_page' => 100, 'resolution' => [ 'unresolved', 'patched', 'deactivated' ] ],
		],
	];

	if ( isset( $_GET['path'] ) && str_starts_with( $_GET['path'], '/vulnerability' ) ) {
		$id = str_replace( '/vulnerability/', '', $_GET['path'] );

		$preload_requests[]	= "/ithemes-security/v1/site-scanner/vulnerabilities/{$id}";
	}

	$preload = ITSEC_Lib::preload_rest_requests( $preload_requests );

	wp_enqueue_script( 'itsec-pages-vulnerabilities' );
	wp_enqueue_style( 'itsec-pages-vulnerabilities' );
	wp_add_inline_script(
		'itsec-pages-vulnerabilities',
		sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload ) )
	);

	foreach ( ITSEC_Modules::get_active_modules_to_run() as $module ) {
		$handle = "itsec-{$module}-vulnerabilities";

		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_enqueue_script( $handle );
		}

		if ( wp_style_is( $handle, 'registered' ) ) {
			wp_enqueue_style( $handle );
		}
	}
} );

add_action( 'itsec-page-show', function () {
	echo '<div id="itsec-vulnerabilities-root"></div>';
} );
