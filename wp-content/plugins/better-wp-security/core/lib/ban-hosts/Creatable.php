<?php

namespace iThemesSecurity\Ban_Hosts;

interface Creatable extends Persistable {

	/**
	 * Gets the JSON Schema used to describe a new ban record.
	 *
	 * @return array
	 */
	public function get_creation_schema();
}
