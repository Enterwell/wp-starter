<?php

return static function ( \Pimple\Container $c ) {
	$c[ ITSEC_Lib_Upgrader::class ] = static function () {
		return new ITSEC_Lib_Upgrader();
	};
};
