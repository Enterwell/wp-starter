<?php

use iThemesSecurity\Modules\Strong_Passwords\Strength_Requirement;
use iThemesSecurity\User_Groups\Matcher;

return static function ( \iThemesSecurity\Strauss\Pimple\Container $c ) {
	$c[ Strength_Requirement::class ] = static function ( \iThemesSecurity\Strauss\Pimple\Container $c ) {
		return new Strength_Requirement( $c[ Matcher::class ], ITSEC_Modules::get_config( 'strong-passwords' ), 'strength' );
	};

	$c['ITSEC_Strong_Passwords'] = static function ( \iThemesSecurity\Strauss\Pimple\Container $c ) {
		return new ITSEC_Strong_Passwords( $c[ Matcher::class ], $c[ Strength_Requirement::class ] );
	};
};
