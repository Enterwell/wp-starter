<?php

namespace iThemesSecurity\Lib\Site_Types;

interface Has_Prerequisites extends Question {

	/**
	 * Returns a map of question IDs to schemas.
	 *
	 * In order for this question to be asked
	 *
	 * @return array
	 */
	public function get_prerequisites(): array;
}
