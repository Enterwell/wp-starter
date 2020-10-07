<?php

namespace iThemesSecurity\Site_Scanner;

use iThemesSecurity\Site_Scanner\Repository\LatestScanRepository;
use iThemesSecurity\Site_Scanner\Repository\LogRepository;
use iThemesSecurity\Site_Scanner\Repository\Repository;
use Pimple\Container;

return static function ( Container $c ) {
	$c['module.site-scanner.files'] = [
		'rest.php' => REST\REST::class,
	];

	$c[ Factory::class ] = static function () {
		return new Factory();
	};

	$c[ Multi_Fixer::class ] = static function ( Container $c ) {
		return new Multi_Fixer( $c[ Vulnerability_Fixer::class ] );
	};

	$c[ Vulnerability_Fixer::class ] = static function ( Container $c ) {
		return new Vulnerability_Fixer( $c[ \ITSEC_Lib_Upgrader::class ] );
	};

	$c[ Repository::class ] = static function ( Container $c ) {
		$log_type = \ITSEC_Modules::get_setting( 'global', 'log_type' );

		if ( 'file' === $log_type ) {
			return $c[ LatestScanRepository::class ];
		}

		return $c[ LogRepository::class ];
	};

	$c[ LogRepository::class ] = static function ( Container $c ) {
		return new LogRepository( $c[ Factory::class ] );
	};

	$c[ LatestScanRepository::class ] = static function ( Container $c ) {
		return new LatestScanRepository();
	};

	$c[ REST\REST::class ] = static function ( Container $c ) {
		return new REST\REST(
			$c[ REST\Scans::class ],
			$c[ REST\Issues::class ],
			$c[ REST\Muted_Issues::class ]
		);
	};

	$c[ REST\Scans::class ] = static function ( Container $c ) {
		return new REST\Scans(
			$c[ Repository::class ]
		);
	};

	$c[ REST\Issues::class ] = static function ( Container $c ) {
		return new REST\Issues(
			$c[ Repository::class ],
			$c[ Multi_Fixer::class ]
		);
	};

	$c[ REST\Muted_Issues::class ] = static function () {
		return new REST\Muted_Issues();
	};
};
