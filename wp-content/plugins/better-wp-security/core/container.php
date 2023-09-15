<?php

namespace iThemesSecurity;

use iThemesSecurity\Lib\REST;
use iThemesSecurity\Lib\Site_Types;
use ITSEC_Lib_Upgrader;
use iThemesSecurity\Strauss\Pimple\Container;
use wpdb;

return static function ( Container $c ) {
	$c[ wpdb::class ] = static function () {
		return $GLOBALS['wpdb'];
	};

	$c[ ITSEC_Lib_Upgrader::class ] = static function () {
		return new ITSEC_Lib_Upgrader();
	};

	$c[ \ITSEC_Modules::class ] = \ITSEC_Modules::get_instance();

	$c[ Actor\Multi_Actor_Factory::class ] = static function ( Container $c ) {
		return new Actor\Multi_Actor_Factory( ...$c['actor.factories'] );
	};

	$c['actor.factories'] = static function () {
		return [
			new Actor\User_Factory(),
			new Actor\Lockout_Module_Factory(),
		];
	};

	$c['ban-hosts.sources'] = static function () {
		return [];
	};

	$c['ban-hosts.repositories'] = static function () {
		return [];
	};

	$c['dashboard.cards'] = static function () {
		return [];
	};

	$c['import-export.sources'] = static function ( Container $c ) {
		return [
			$c[ \ITSEC_Modules::class ],
		];
	};

	$c['rest.controllers'] = static function() {
		return [];
	};

	$c[ Ban_Hosts\Multi_Repository::class ] = static function ( Container $c ) {
		return new Ban_Hosts\Multi_Repository(
			...array_map( [ $c, 'offsetGet' ], $c['ban-hosts.repositories'] )
		);
	};

	$c[ Ban_Hosts\Source::class ] = static function ( Container $c ) {
		return new Ban_Hosts\Chain_Source(
			...array_map( [ $c, 'offsetGet' ], $c['ban-hosts.repositories'] ),
			...array_map( [ $c, 'offsetGet' ], $c['ban-hosts.sources'] )
		);
	};

	$c[ Ban_Hosts\REST::class ] = static function ( Container $c ) {
		return new Ban_Hosts\REST(
			$c[ Ban_Hosts\Multi_Repository::class ],
			$c[ Actor\Multi_Actor_Factory::class ]
		);
	};

	$c[ Site_Types\Registry::class ] = static function () {
		return ( new Site_Types\Registry() )
			->register( new Site_Types\Type\Ecommerce() )
			->register( new Site_Types\Type\Network() )
			->register( new Site_Types\Type\Non_Profit() )
			->register( new Site_Types\Type\Blog() )
			->register( new Site_Types\Type\Portfolio() )
			->register( new Site_Types\Type\Brochure() );
	};

	$c[ Site_Types\Defaults::class ] = static function () {
		return new Site_Types\Defaults();
	};

	$c[ Lib\Tools\Tools_Registry::class ] = static function () {
		return new Lib\Tools\Tools_Registry();
	};

	$c[ Lib\Tools\Tools_Runner::class ] = static function ( Container $c ) {
		return new Lib\Tools\Tools_Runner( $c[ Lib\Tools\Tools_Registry::class ] );
	};

	$c[ REST\Modules_Controller::class ] = static function () {
		return new REST\Modules_Controller();
	};

	$c[ REST\Settings_Controller::class ] = static function () {
		return new REST\Settings_Controller();
	};

	$c[ REST\Site_Types_Controller::class ] = static function ( Container $c ) {
		return new REST\Site_Types_Controller(
			$c[ Site_Types\Registry::class ],
			$c[ Site_Types\Defaults::class ]
		);
	};

	$c[ REST\Tools_Controller::class ] = static function ( Container $c ) {
		return new REST\Tools_Controller(
			$c[ Lib\Tools\Tools_Registry::class ],
			$c[ Lib\Tools\Tools_Runner::class ]
		);
	};
};
