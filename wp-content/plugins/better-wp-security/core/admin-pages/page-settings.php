<?php
add_action( 'admin_enqueue_scripts', function () {
	$preload = \ITSEC_Lib::preload_rest_requests( [
		'/ithemes-security/v1/site-types'                    => [
			'route' => '/ithemes-security/v1/site-types',
		],
		'/ithemes-security/v1/user-matchables?_embed=1'      => [
			'route' => '/ithemes-security/v1/user-matchables',
			'embed' => true,
		],
		'/ithemes-security/v1/tools'                         => [
			'route' => '/ithemes-security/v1/tools',
		],
	] );
	wp_enqueue_script( 'itsec-pages-settings' );
	wp_enqueue_style( 'itsec-pages-settings' );
	wp_add_inline_script(
		'itsec-pages-settings',
		sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload ) )
	);

	foreach ( ITSEC_Modules::get_available_modules() as $module ) {
		$handle = "itsec-{$module}-settings";

		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_enqueue_script( $handle );
		}

		if ( wp_style_is( $handle, 'registered' ) ) {
			wp_enqueue_style( $handle );
		}
	}

	remove_action( 'admin_head', 'wp_admin_canonical_url' );
} );

add_filter( 'admin_viewport_meta', function ( $meta ) {
	$meta .= ',maximum-scale=1';

	return $meta;
} );

add_action( 'itsec-page-show', function () {
	require_once ITSEC_Core::get_core_dir() . 'admin-pages/sidebar-widget.php';
	require_once ITSEC_Core::get_core_dir() . 'deprecated/module-settings.php';
	do_action( 'itsec-settings-page-init' );

	if ( ITSEC_Core::is_onboarded() ) {
		do_action( 'stellarwp/telemetry/optin', 'solid-security' );
	}

	$server_type = ITSEC_Lib::get_server();
	$onboard     = ITSEC_Modules::get_setting( 'global', 'onboard_complete' );

	printf(
		'<div id="itsec-settings-root" data-server-type="%s" data-install-type="%s" data-onboard="%s"></div>',
		esc_attr( $server_type ),
		esc_attr( ITSEC_Core::get_install_type() ),
		esc_attr( $onboard )
	);
} );
