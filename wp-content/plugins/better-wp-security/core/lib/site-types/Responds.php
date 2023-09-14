<?php

namespace iThemesSecurity\Lib\Site_Types;

interface Responds {
	/**
	 * Handle the user's answer.
	 *
	 * It has already been validated and sanitized according to the schema.
	 *
	 * @param Answer_Handler $handler
	 *
	 * @return void
	 */
	public function respond( Answer_Handler $handler );
}
