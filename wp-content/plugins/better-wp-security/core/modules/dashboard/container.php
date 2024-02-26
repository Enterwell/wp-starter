<?php

use iThemesSecurity\Dashboard\Cards\Security_Summary_Card;
use iThemesSecurity\Site_Scanner\Repository\Scans_Repository;
use iThemesSecurity\User_Groups\Matcher;
use iThemesSecurity\Strauss\Pimple\Container;

return static function ( Container $c ) {
	$c[ ITSEC_Dashboard::class ] = static function ( Container $c ) {
		return new ITSEC_Dashboard( $c[ Matcher::class ] );
	};

	ITSEC_Lib::extend_if_able( $c,'dashboard.cards', function ( $cards ) use ( $c ) {
		$cards[] = new Security_Summary_Card(
			$c[ Scans_Repository::class ],
		);
		$cards[] = new ITSEC_Dashboard_Card_Line_Graph( 'brute-force', __( 'Threats Blocked', 'better-wp-security' ), [
			[
				'events' => [ 'local-brute-force', 'network-brute-force', 'firewall-block' ],
				'label'  => __( 'Attacks', 'better-wp-security' ),
			],
		] );
		$cards[] = new ITSEC_Dashboard_Card_Pie_Chart( 'lockout', __( 'Lockouts', 'better-wp-security' ), [
			[
				'events' => 'lockout-host',
				'label'  => __( 'IP Addresses', 'better-wp-security' ),
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

		return $cards;
	} );

	ITSEC_Lib::extend_if_able( $c, 'import-export.sources', function ( $sources, $c ) {
		$sources[] = $c[ ITSEC_Dashboard::class ];

		return $sources;
	} );
};
