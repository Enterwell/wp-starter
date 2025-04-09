<?php

namespace iThemesSecurity\Site_Scanner;

use iThemesSecurity\Site_Scanner\Cards\Vulnerable_Software_Card;
use iThemesSecurity\Site_Scanner\Repository\Latest_Scans_Repository;
use iThemesSecurity\Site_Scanner\Repository\Log_Scans_Repository;
use iThemesSecurity\Site_Scanner\Repository\Scans_Repository;
use iThemesSecurity\Site_Scanner\Repository\Vulnerabilities_Repository;

use iThemesSecurity\Strauss\Pimple\Container;

return static function ( Container $c ) {
	$c['module.site-scanner.files'] = [
		'rest.php'   => REST\REST::class,
		'active.php' => \ITSEC_Site_Scanner::class,
	];

	$c->extend( 'dashboard.cards', function ( $cards, Container $c ) {
		$cards[] = new Vulnerable_Software_Card( $c[ Scans_Repository::class ] );

		return $cards;
	} );

	$c[ \ITSEC_Site_Scanner::class ] = static function ( Container $c ) {
		return new \ITSEC_Site_Scanner(
			$c[ Scans_Repository::class ],
			$c[ Vulnerabilities_Repository::class ],
		);
	};

	$c[ Factory::class ] = static function () {
		return new Factory();
	};

	$c[ Multi_Fixer::class ] = static function ( Container $c ) {
		return new Multi_Fixer( $c[ Vulnerability_Fixer::class ] );
	};

	$c[ Vulnerability_Fixer::class ] = static function ( Container $c ) {
		return new Vulnerability_Fixer( $c[ \ITSEC_Lib_Upgrader::class ] );
	};

	$c[ Scans_Repository::class ] = static function ( Container $c ) {
		$log_type = \ITSEC_Modules::get_setting( 'global', 'log_type' );

		if ( 'file' === $log_type ) {
			return $c[ Latest_Scans_Repository::class ];
		}

		return $c[ Log_Scans_Repository::class ];
	};

	$c[ Log_Scans_Repository::class ] = static function ( Container $c ) {
		return new Log_Scans_Repository( $c[ Factory::class ] );
	};

	$c[ Latest_Scans_Repository::class ] = static function ( Container $c ) {
		return new Latest_Scans_Repository();
	};

	$c[ Vulnerabilities_Repository::class ] = static function ( Container $c ) {
		return new Vulnerabilities_Repository( $c[ \wpdb::class ] );
	};

	$c[ REST\REST::class ] = static function ( Container $c ) {
		return new REST\REST(
			$c[ REST\Scans::class ],
			$c[ REST\Issues::class ],
			$c[ REST\Muted_Issues::class ],
			$c[ REST\Vulnerabilities::class ],
		);
	};

	$c[ REST\Scans::class ] = static function ( Container $c ) {
		return new REST\Scans(
			$c[ Scans_Repository::class ]
		);
	};

	$c[ REST\Issues::class ] = static function ( Container $c ) {
		return new REST\Issues(
			$c[ Scans_Repository::class ],
			$c[ Multi_Fixer::class ]
		);
	};

	$c[ REST\Muted_Issues::class ] = static function () {
		return new REST\Muted_Issues();
	};

	$c[ REST\Vulnerabilities::class ] = static function ( Container $c ) {
		return new REST\Vulnerabilities(
			$c[ Vulnerabilities_Repository::class ],
			$c[ Vulnerability_Fixer::class ],
		);
	};
};
