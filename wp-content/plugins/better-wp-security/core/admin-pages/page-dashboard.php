<?php

add_action( 'admin_enqueue_scripts', function () {
	$primary_id = ITSEC_Dashboard_Util::get_primary_dashboard_id();

	$preload_requests = [
		'/?_fields=name,description,url,home'            => [
			'route' => '/',
			'query' => [ '_fields' => 'name,description,url,home' ],
		],
		'/ithemes-security/v1?context=help'              => [
			'route' => '/ithemes-security/v1',
			'query' => [ 'context' => 'help' ],
		],
		'/ithemes-security/v1/dashboard-static',
		'/ithemes-security/v1/dashboards?_embed=1'       => [
			'route' => '/ithemes-security/v1/dashboards',
			'embed' => true,
		],
		'/ithemes-security/v1/dashboard-available-cards' => [
			'route' => '/ithemes-security/v1/dashboard-available-cards',
		],
		'/ithemes-security/v1/actors?_embed=1'           => [
			'route' => '/ithemes-security/v1/actors',
			'embed' => true,
		],
		'/wp/v2/users/me?context=edit'                   => [
			'route' => '/wp/v2/users/me',
			'query' => [ 'context' => 'edit' ],
		],
	];

	if ( ! $primary_id ) {
		$create = new WP_REST_Request( 'POST', '/ithemes-security/v1/dashboards' );
		$create->set_body_params( [
			'preset' => 'default',
			'label'  => __( 'Security Dashboard', 'better-wp-security' ),
		] );
		$created = rest_do_request( $create );

		if ( ! $created->is_error() ) {
			$primary_id = $created->get_data()['id'];
			update_user_meta( get_current_user_id(), ITSEC_Dashboard::META_PRIMARY, $primary_id );
			ITSEC_Dashboard_Util::flush_cache();
		}
	}

	if ( $primary_id ) {
		$key   = "/ithemes-security/v1/dashboards/{$primary_id}?_embed=1";
		$query = array();

		if ( current_user_can( 'itsec_edit_dashboard', $primary_id ) ) {
			$key .= '&context=edit';

			$query['context'] = 'edit';
		}

		$preload_requests[ $key ] = array(
			'route' => "/ithemes-security/v1/dashboards/{$primary_id}",
			'embed' => true,
			'query' => $query,
		);

		$preload_requests["/ithemes-security/v1/dashboards/{$primary_id}/cards?_embed=1"] = array(
			'route' => "/ithemes-security/v1/dashboards/{$primary_id}/cards",
			'embed' => true,
		);

		$preload_requests[] = "/ithemes-security/v1/dashboards/{$primary_id}/layout";
	}

	$preload = ITSEC_Lib::preload_rest_requests( $preload_requests );

	wp_enqueue_style( 'itsec-dashboard-dashboard' );
	wp_enqueue_script( 'itsec-dashboard-dashboard' );
	wp_add_inline_script(
		'itsec-dashboard-dashboard',
		sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload ) )
	);

	foreach ( ITSEC_Modules::get_available_modules() as $module ) {
		if ( ! ITSEC_Modules::is_active( $module ) ) {
			continue;
		}

		$handle = "itsec-{$module}-dashboard";

		if ( wp_script_is( $handle, 'registered' ) ) {
			wp_enqueue_script( $handle );
		}

		if ( wp_style_is( $handle, 'registered' ) ) {
			wp_enqueue_style( $handle );
		}
	}

	do_action( 'itsec_dashboard_enqueue_scripts' );
} );

add_action( 'itsec-page-show', function () {
	printf(
		'<div id="itsec-dashboard-root" data-can-manage="%s" data-install-type="%s"></div>',
		ITSEC_Core::current_user_can_manage(),
		ITSEC_Core::get_install_type()
	);
} );

remove_all_actions( 'all_admin_notices' );
remove_all_actions( 'network_admin_notices' );
remove_all_actions( 'admin_notices' );
