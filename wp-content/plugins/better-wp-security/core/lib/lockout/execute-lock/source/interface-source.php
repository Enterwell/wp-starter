<?php

namespace iThemesSecurity\Lib\Lockout\Execute_Lock\Source;

require_once( __DIR__ . '/class-lockout-module.php' );
require_once( __DIR__ . '/class-configurable.php' );

interface Source {

	/**
	 * Get a unique slug for the lock source.
	 *
	 * @return string
	 */
	public function get_source_slug();
}
