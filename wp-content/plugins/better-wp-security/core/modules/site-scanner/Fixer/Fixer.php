<?php

namespace iThemesSecurity\Site_Scanner;

interface Fixer {

	/**
	 * Can this issue be fixed.
	 *
	 * @param Issue $issue
	 *
	 * @return bool
	 */
	public function is_fixable( Issue $issue );

	/**
	 * Does the user have permission to fix the issue.
	 *
	 * @param \WP_User $user
	 * @param Issue    $issue
	 *
	 * @return true|\WP_Error
	 */
	public function can_user_fix( \WP_User $user, Issue $issue );

	/**
	 * Fix the issue.
	 *
	 * @param Issue $issue
	 *
	 * @return true|\WP_Error
	 */
	public function fix( Issue $issue );

	/**
	 * Get a descriptive action label for fixing the issue.
	 *
	 * For instance "Update Now" instead of just a generic "Fix".
	 *
	 * @param Issue $issue
	 *
	 * @return string
	 */
	public function get_fix_label( Issue $issue );
}
