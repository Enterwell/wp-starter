<?php

namespace iThemesSecurity\Modules\Firewall;

use iThemesSecurity\Modules\Firewall\REST;
use iThemesSecurity\Site_Scanner\Repository\Vulnerabilities_Repository;
use iThemesSecurity\Strauss\Patchstack\Extensions\ExtensionInterface;
use iThemesSecurity\Strauss\Pimple\Container;

return static function ( Container $c ) {
	$c['module.firewall.files'] = [
		'active.php' => Firewall::class,
		'logs.php'   => Logs::class,
		'rest.php'   => REST\REST::class,
	];

	$c[ Firewall::class ] = static function ( Container $c ) {
		return new Firewall(
			$c[ Processor_Factory::class ],
			$c[ Ingestor::class ]
		);
	};

	$c[ Logs::class ] = static function ( Container $c ) {
		return new Logs(
			$c[ Vulnerabilities_Repository::class ]
		);
	};

	$c[ Ingestor::class ] = static function ( Container $c ) {
		return new Ingestor(
			$c[ Rules\Repository::class ]
		);
	};

	$c[ Rules\Repository::class ] = static function () {
		return new Rules\Repository( $GLOBALS['wpdb'] );
	};

	$c[ Rules\Loader::class ] = static function ( Container $c ) {
		return $c[ Rules\Repository::class ];
	};

	$c[ Processor_Factory::class ] = static function ( Container $c ) {
		return new Processor_Factory(
			$c[ ExtensionInterface::class ],
			$c[ Rules\Loader::class ]
		);
	};

	$c[ ExtensionInterface::class ] = static function ( Container $c ) {
		return new Extension( $c[ \ITSEC_Lockout::class ] );
	};

	$c[ REST\REST::class ] = static function ( Container $c ) {
		return new REST\REST(
			$c[ REST\Rules::class ]
		);
	};

	$c[ REST\Rules::class ] = static function ( Container $c ) {
		return new REST\Rules(
			$c[ Rules\Repository::class ]
		);
	};
};
