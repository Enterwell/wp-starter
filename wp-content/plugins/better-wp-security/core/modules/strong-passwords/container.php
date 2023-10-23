<?php

namespace iThemesSecurity\Modules\Strong_Passwords;

use iThemesSecurity\Modules\Strong_Passwords\REST\REST;
use iThemesSecurity\Modules\Strong_Passwords\REST\Scan;
use iThemesSecurity\Strauss\Pimple\Container;
use iThemesSecurity\User_Groups;

return static function ( Container $c ) {
	$c['module.strong-passwords.files'] = [
		'rest.php' => REST::class,
	];

	$c[ Strength_Requirement::class ] = static function ( Container $c ) {
		return new Strength_Requirement( $c[ User_Groups\Matcher::class ], \ITSEC_Modules::get_config( 'strong-passwords' ), 'strength' );
	};

	$c[ \ITSEC_Strong_Passwords::class ] = static function ( Container $c ) {
		return new \ITSEC_Strong_Passwords( $c[ User_Groups\Matcher::class ], $c[ Strength_Requirement::class ] );
	};

	$c[ Scan::class ] = static function ( Container $c ) {
		return new Scan( $c[ User_Groups\Matcher::class ] );
	};

	$c[ REST::class ] = static function ( Container $c ) {
		return new REST( $c[ Scan::class ] );
	};
};
