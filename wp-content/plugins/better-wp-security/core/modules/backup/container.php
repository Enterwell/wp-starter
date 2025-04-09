<?php

use iThemesSecurity\Strauss\Pimple\Container;

return static function ( Container $c ) {
	ITSEC_Lib::extend_if_able( $c, 'dashboard.cards', function ( $cards ) {
		require_once __DIR__ . '/cards/class-itsec-dashboard-card-database-backup.php';

		$cards[] = new ITSEC_Dashboard_Card_Database_Backup();

		return $cards;
	} );
};
