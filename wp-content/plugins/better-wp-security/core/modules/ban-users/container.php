<?php

namespace iThemesSecurity\Ban_Users;

use iThemesSecurity\Actor\Multi_Actor_Factory;
use iThemesSecurity\Ban_Hosts\Filters;
use iThemesSecurity\Strauss\Pimple\Container;

return static function ( Container $c ) {
	$c['module.ban-users.files'] = [
		'rest.php' => REST::class,
	];

	\ITSEC_Lib::extend_if_able( $c, 'ban-hosts.repositories', static function ( $repositories ) {
		if ( \ITSEC_Modules::get_setting( 'ban-users', 'enable_ban_lists' ) ) {
			$repositories[] = Database_Repository::class;
		}

		return $repositories;
	} );

	$c[ Database_Repository::class ] = static function ( Container $c ) {
		return new Database_Repository(
			$c[ Multi_Actor_Factory::class ],
			$c[ \wpdb::class ]
		);
	};

	$c[ REST::class ] = static function ( Container $c ) {
		return new REST( $c[ Database_Repository::class ] );
	};

	\ITSEC_Lib::extend_if_able( $c, 'dashboard.cards', function ( $cards ) use ( $c ) {
		$cards[] = new \ITSEC_Dashboard_Card_Pie_Chart( 'banned-users', __( 'Bans Overview', 'better-wp-security' ), [
			[
				'events' => 'blacklist-brute_force',
				'label'  => __( 'Login Attempts', 'better-wp-security' ),
			],
			[
				'events' => 'blacklist-brute_force_admin_user',
				'label'  => __( 'Login Using "admin"', 'better-wp-security' ),
			],
			[
				'events' => 'blacklist-recaptcha',
				'label'  => __( 'CAPTCHA', 'better-wp-security' ),
			],
		], [
			'circle_label'    => _x( 'Banned', 'Total Banned IPs', 'better-wp-security' ),
			'circle_callback' => function () use ( $c ) {
				return $c[ Database_Repository::class ]->count_bans( new Filters() );
			},
		] );

		return $cards;
	} );

	\ITSEC_Lib::extend_if_able( $c, 'import-export.sources', function ( $sources, $c ) {
		$sources[] = $c[ Database_Repository::class ];

		return $sources;
	} );
};
