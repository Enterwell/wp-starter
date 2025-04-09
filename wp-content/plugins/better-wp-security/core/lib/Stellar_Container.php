<?php

namespace iThemesSecurity\Lib;

use iThemesSecurity\Strauss\Pimple\Container;
use iThemesSecurity\Strauss\StellarWP\ContainerContract\ContainerInterface;

class Stellar_Container implements ContainerInterface {

	/** @var Container */
	private $container;

	public function __construct( Container $container ) { $this->container = $container; }

	public function bind( string $id, $implementation = null ) {
		$this->container[ $id ] = $this->container->factory( $this->make_builder( $implementation ) );
	}

	public function get( string $id ) {
		return $this->container[ $id ];
	}

	public function has( string $id ) {
		return isset( $this->container[ $id ] );
	}

	public function singleton( string $id, $implementation = null ) {
		$this->container[ $id ] = $this->make_builder( $implementation );
	}

	private function make_builder( $implementation ): \Closure {
		return static function ( Container $c ) use ( $implementation ) {
			if ( is_string( $implementation ) && class_exists( $implementation ) ) {
				return new $implementation();
			}

			if ( $implementation instanceof \Closure ) {
				return $implementation();
			}

			return $implementation;
		};
	}
}
