<?php

namespace iThemesSecurity\User_Groups;

interface Matcher {

	/**
	 * Checks if their is a match for one of the group identifiers.
	 *
	 * @param Match_Target $target
	 * @param string[]     $groups
	 *
	 * @return bool
	 */
	public function matches( Match_Target $target, $groups );
}
