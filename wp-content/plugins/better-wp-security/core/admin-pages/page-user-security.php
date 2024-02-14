<?php

add_action( 'admin_enqueue_scripts', function () {
	$preload_requests = [
		[
			'route' => '/wp/v2/users',
			'query' => [
				'roles'    => [ 'administrator' ],
				'context'  => 'edit',
				'per_page' => 20,
			],
		],
	];

	$preload = ITSEC_Lib::preload_rest_requests( $preload_requests );

	wp_enqueue_script( 'itsec-pages-user-security' );
	wp_enqueue_style( 'itsec-pages-user-security' );
	wp_add_inline_script(
		'itsec-pages-user-security',
		sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload ) )
	);

	foreach ( ITSEC_Modules::get_active_modules_to_run() as $module ) {
		$handle = "itsec-{$module}-user-security";

		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_enqueue_script( $handle );
		}

		if ( wp_style_is( $handle, 'registered' ) ) {
			wp_enqueue_style( $handle );
		}
	}
} );

add_action( 'itsec-page-show', function () {
	echo '<div id="itsec-user-security-root"></div>';
} );
