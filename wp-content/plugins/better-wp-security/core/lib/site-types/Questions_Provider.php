<?php

namespace iThemesSecurity\Lib\Site_Types;

interface Questions_Provider {

	/**
	 * Gets a list of questions.
	 *
	 * @return Question[]
	 */
	public function get_questions(): array;
}
