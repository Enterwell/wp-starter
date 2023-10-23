<?php

namespace iThemesSecurity\Modules\Firewall\Rules;

interface Loader {

	/**
	 * Gets the list of Firewall rules to use.
	 *
	 * @return array
	 */
	public function load_rules(): array;
}
