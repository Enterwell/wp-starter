<?php

use iThemesSecurity\User_Groups\Matcher;

return static function ( \Pimple\Container $c ) {
	$c['ITSEC_Strong_Passwords'] = static function ( \Pimple\Container $c ) {
		return new ITSEC_Strong_Passwords( $c[ Matcher::class ] );
	};
};
