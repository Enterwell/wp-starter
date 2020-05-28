<?php

namespace iThemesSecurity\User_Groups;

interface Matchable {

	/**
	 * Checks if the given user matches the rules for this user group
	 *
	 * @param Match_Target $target
	 *
	 * @return bool
	 */
	public function matches( Match_Target $target );

	/**
	 * Get a globally unique identifier for this matchable object.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Gets the label for this group.
	 *
	 * @return string
	 */
	public function get_label();
}
