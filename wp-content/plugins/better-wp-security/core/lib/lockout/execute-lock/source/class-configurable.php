<?php

namespace iThemesSecurity\Lib\Lockout\Execute_Lock\Source;

class Configurable implements Source {

	/** @var string */
	private $slug;

	/**
	 * ITSEC_Execute_Lock_Source_Configurable constructor.
	 *
	 * @param string $slug
	 */
	public function __construct( $slug ) { $this->slug = $slug; }

	public function get_source_slug() {
		return $this->slug;
	}
}
