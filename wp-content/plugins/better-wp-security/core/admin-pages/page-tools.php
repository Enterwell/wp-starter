<?php

add_action( 'admin_enqueue_scripts', function () {
	$preload_requests = [
		'/ithemes-security/v1/tools'                         => [
			'route' => '/ithemes-security/v1/tools',
		],
	];

	$preload = ITSEC_Lib::preload_rest_requests( $preload_requests, 'tools' );

	wp_enqueue_script( 'itsec-pages-tools' );
	wp_enqueue_style( 'itsec-pages-tools' );
	wp_add_inline_script(
		'itsec-pages-tools',
		sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload ) )
	);

	foreach ( ITSEC_Modules::get_active_modules_to_run() as $module ) {
		$handle = "itsec-{$module}-tools";

		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_enqueue_script( $handle );
		}

		if ( wp_style_is( $handle, 'registered' ) ) {
			wp_enqueue_style( $handle );
		}
	}
} );

add_action( 'itsec-page-show', function () {
	echo '<div id="itsec-tools-root"></div>';
} );
