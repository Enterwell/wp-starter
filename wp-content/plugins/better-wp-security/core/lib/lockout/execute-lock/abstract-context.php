<?php

namespace iThemesSecurity\Lib\Lockout\Execute_Lock;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use iThemesSecurity\Lib\Lockout\Execute_Lock\Source\Source;

abstract class Context implements ArrayAccess, IteratorAggregate {

	/** @var Source */
	private $source;

	/**
	 * ITSEC_Execute_Lock_Context constructor.
	 *
	 * @param Source $source
	 */
	public function __construct( Source $source ) { $this->source = $source; }

	/**
	 * Get the source that caused the execute lock.
	 *
	 * @return Source
	 */
	public function get_source() {
		return $this->source;
	}

	/**
	 * Create a copy of this context with a different source.
	 *
	 * @param Source $source
	 *
	 * @return $this
	 */
	public function with_source( Source $source ) {
		$self         = clone $this;
		$self->source = $source;

		return $self;
	}

	/**
	 * Return the context as an array.
	 *
	 * @return array
	 */
	public function to_legacy() {
		return array(
			'type' => $this->get_source()->get_source_slug(),
		);
	}

	public function getIterator() {
		return new ArrayIterator( $this->to_legacy() );
	}

	public function offsetExists( $offset ) {
		$legacy = $this->to_legacy();

		return isset( $legacy[ $offset ] );
	}

	public function offsetGet( $offset ) {
		$legacy = $this->to_legacy();

		return $legacy[ $offset ];
	}

	public function offsetSet( $offset, $value ) {
		// no-op
	}

	public function offsetUnset( $offset ) {
		// no-op
	}
}

require_once( __DIR__ . '/class-host-context.php' );
require_once( __DIR__ . '/class-user-context.php' );
require_once( __DIR__ . '/class-username-context.php' );
