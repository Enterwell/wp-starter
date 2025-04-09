<?php

namespace iThemesSecurity\Ban_Hosts;

interface Updatable extends Persistable {

	/**
	 * Gets the schema specifying what fields can be updated.
	 *
	 * @return array
	 */
	public function get_update_schema();
}
