<?php

use iThemesSecurity\User_Groups\Matcher;
use iThemesSecurity\Strauss\Pimple\Container;

return static function ( Container $c ) {
	$c[ ITSEC_Dashboard::class ] = static function ( Container $c ) {
		return new ITSEC_Dashboard( $c[ Matcher::class ] );
	};

	$c->extend( 'dashboard.cards', function ( $cards ) use ( $c ) {
		$cards[] = new ITSEC_Dashboard_Card_Active_Lockouts();
		$cards[] = new ITSEC_Dashboard_Card_Line_Graph( 'brute-force', __( 'Brute Force Attacks', 'better-wp-security' ), [
			[
				'events' => [ 'local-brute-force', 'network-brute-force' ],
				'label'  => __( 'Attacks', 'better-wp-security' ),
			],
		] );
		$cards[] = new ITSEC_Dashboard_Card_Pie_Chart( 'lockout', __( 'Lockouts', 'better-wp-security' ), [
			[
				'events' => 'lockout-host',
				'label'  => __( 'Hosts', 'better-wp-security' ),
			],
			[
				'events' => 'lockout-user',
				'label'  => __( 'Users', 'better-wp-security' ),
			],
			[
				'events' => 'lockout-username',
				'label'  => __( 'Usernames', 'better-wp-security' ),
			],
		], [
			'circle_label'    => _x( 'Total', 'Total Lockouts', 'better-wp-security' ),
			'circle_callback' => function () {
				/** @var ITSEC_Lockout $itsec_lockout */
				global $itsec_lockout;

				return $itsec_lockout->get_lockouts( 'all', array( 'return' => 'count', 'current' => false ) );
			},
		] );

		if ( $c['ban-hosts.repositories'] ) {
			$cards[] = new ITSEC_Dashboard_Card_Banned_Users();
		}

		return $cards;
	} );

	ITSEC_Lib::extend_if_able( $c, 'import-export.sources', function ( $sources, $c ) {
		$sources[] = $c[ ITSEC_Dashboard::class ];

		return $sources;
	} );
};
