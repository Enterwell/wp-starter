<?php

namespace iThemesSecurity\Lib\Lockout\Execute_Lock\Source;

class Lockout_Module implements Source {

	/** @var string */
	private $module_slug;

	/**
	 * Lockout_Module constructor.
	 *
	 * @param string $module_slug
	 */
	public function __construct( $module_slug ) { $this->module_slug = $module_slug; }

	public function get_source_slug() {
		return $this->module_slug;
	}
}
