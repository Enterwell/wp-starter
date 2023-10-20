<?php

namespace iThemesSecurity\Modules\Core;

use iThemesSecurity\Strauss\Pimple\Container;
use iThemesSecurity\User_Groups\Repository\Repository;

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
};
