<?php

namespace iThemesSecurity\Modules\Core;

use iThemesSecurity\Strauss\Pimple\Container;
use iThemesSecurity\User_Groups\Repository\Repository;
use ITSEC_Dashboard_Card_Active_Lockouts;
use ITSEC_Dashboard_Card_Banned_Users;
use ITSEC_Lib;


return static function ( Container $c ) {
	$c['module.core.files'] = [
		'active.php' => \ITSEC_Core_Active::class,
	];

	$c[ \ITSEC_Core_Active::class ] = static function ( Container $c ) {
		return new \ITSEC_Core_Active(
			$c[ \ITSEC_Core_Admin::class ],
			$c[ \ITSEC_Admin_Notices::class ],
			$c[ User_Query_Extension::class ],
		);
	};

	$c[ \ITSEC_Core_Admin::class ] = static function () {
		return new \ITSEC_Core_Admin();
	};

	$c[ \ITSEC_Admin_Notices::class ] = static function () {
		return new \ITSEC_Admin_Notices();
	};

	$c[ User_Query_Extension::class ] = static function ( Container $c ) {
		return new User_Query_Extension(
			$c[ Repository::class ]
		);
	};

	ITSEC_Lib::extend_if_able( $c, 'dashboard.cards', function ( $cards ) use ( $c ) {

		$cards[] = new ITSEC_Dashboard_Card_Active_Lockouts();
		if ( $c['ban-hosts.repositories'] ) {
			$cards[] = new ITSEC_Dashboard_Card_Banned_Users();
		}
		return $cards;
	} );
};
