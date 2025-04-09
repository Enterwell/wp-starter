<?php

use iThemesSecurity\User_Groups;
use iThemesSecurity\User_Groups\REST;
use iThemesSecurity\Strauss\Pimple\Container;

return static function ( Container $c ) {
	$c['module.user-groups.files'] = [
		'active.php' => User_Groups\Module\Module::class,
		'rest.php'   => REST\REST::class,
	];

	$c[ User_Groups\Module\Module::class ] = static function ( Container $c ) {
		return new User_Groups\Module\Module(
			$c[ User_Groups\Repository\Repository::class ],
			$c[ User_Groups\Settings_Registry::class ],
			$c[ User_Groups\Settings_Proxy::class ]
		);
	};

	$c[ User_Groups\Repository\Repository::class ] = static function ( Container $c ) {
		return new User_Groups\Repository\Eager_Loading_Decorator( $c[ User_Groups\Repository\DB_Repository::class ] );
	};

	$c[ User_Groups\Repository\DB_Repository::class ] = static function ( Container $c ) {
		return new User_Groups\Repository\DB_Repository( $GLOBALS['wpdb'] );
	};

	$c[ User_Groups\Upgrader::class ] = static function ( Container $c ) {
		return new User_Groups\Upgrader(
			$c[ User_Groups\Repository\Repository::class ]
		);
	};

	$c[ User_Groups\Matcher::class ] = static function ( Container $c ) {
		return $c[ User_Groups\Default_Matcher::class ];
	};

	$c[ User_Groups\Default_Matcher::class ] = static function ( Container $c ) {
		return new User_Groups\Default_Matcher( $c[ User_Groups\Matchables_Source::class ] );
	};

	$c[ User_Groups\Matchables_Source::class ] = static function ( Container $c ) {
		return ( new User_Groups\Matchables_Source( $c[ User_Groups\Repository\Repository::class ] ) )
			->add( new User_Groups\Everybody_Else( $c[ User_Groups\Repository\Repository::class ] ) );
	};

	$c[ User_Groups\Settings_Registry::class ] = static function ( Container $c ) {
		return new User_Groups\Settings_Registry();
	};

	$c[ User_Groups\Settings_Proxy::class ] = static function () {
		return new User_Groups\Settings_Proxy();
	};

	$c[ User_Groups\REST\REST::class ] = static function ( Container $c ) {
		return new User_Groups\REST\REST( array(
			$c[ REST\User_Groups::class ],
			$c[ REST\Matchables::class ],
			$c[ REST\Settings::class ],
		) );
	};

	$c[ REST\User_Groups::class ] = static function ( Container $c ) {
		return new REST\User_Groups( $c[ User_Groups\Repository\Repository::class ] );
	};

	$c[ REST\Matchables::class ] = static function ( Container $c ) {
		return new REST\Matchables( $c[ User_Groups\Matchables_Source::class ] );
	};

	$c[ REST\Settings::class ] = static function ( Container $c ) {
		return new REST\Settings(
			$c[ User_Groups\Matchables_Source::class ],
			$c[ User_Groups\Settings_Registry::class ],
			$c[ User_Groups\Settings_Proxy::class ]
		);
	};

	\ITSEC_Lib::extend_if_able( $c, 'import-export.sources', function ( $sources, $c ) {
		$sources[] = $c[ User_Groups\Module\Module::class ];

		return $sources;
	} );
};
