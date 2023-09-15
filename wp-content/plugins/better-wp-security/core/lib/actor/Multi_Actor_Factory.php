<?php

namespace iThemesSecurity\Actor;

final class Multi_Actor_Factory {

	/** @var Actor_Factory[] */
	private $factories;

	/**
	 * Multi_Actor_Factory constructor.
	 *
	 * @param Actor_Factory[] $factories
	 */
	public function __construct( Actor_Factory ...$factories ) { $this->factories = $factories; }

	/**
	 * Makes an Actor instance.
	 *
	 * @param string     $type
	 * @param string|int $identifier
	 *
	 * @return Actor|null
	 */
	public function make( $type, $identifier ) {
		foreach ( $this->factories as $factory ) {
			if ( $factory->get_slug() === $type ) {
				return $factory->make( $identifier );
			}
		}

		return null;
	}

	/**
	 * Gets the list of supported actor types.
	 *
	 * @return Actor_Factory[]
	 */
	public function get_supported_factories() {
		return $this->factories;
	}
}
