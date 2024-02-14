<?php

namespace iThemesSecurity\TwoFactor;

use iThemesSecurity\Strauss\Pimple\Container;
use iThemesSecurity\TwoFactor\REST\REST;
use iThemesSecurity\TwoFactor\REST\Scan;
use iThemesSecurity\User_Groups;

return static function ( Container $c ) {
	$c['module.two-factor.files'] = [
		'rest.php' => REST::class,
	];

	$c[ \ITSEC_Two_Factor::class ] = static function () {
		return \ITSEC_Two_Factor::get_instance();
	};

	$c[ Scan::class ] = static function ( Container $c ) {
		return new Scan(
			$c[ User_Groups\Matcher::class ],
			$c[ \ITSEC_Two_Factor::class ]
		);
	};

	$c[ REST::class ] = static function ( Container $c ) {
		return new REST(
			$c[ Scan::class ]
		);
	};
};
