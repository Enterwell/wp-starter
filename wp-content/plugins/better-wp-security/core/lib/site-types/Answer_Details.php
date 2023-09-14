<?php

namespace iThemesSecurity\Lib\Site_Types;

use iThemesSecurity\User_Groups\User_Group;

interface Answer_Details {
	/**
	 * Gets the question asked.
	 *
	 * @return Question
	 */
	public function get_question(): Question;

	/**
	 * Gets the answer provided by the user.
	 *
	 * @return mixed
	 */
	public function get_answer();

	/**
	 * Gets the list of modules to enable.
	 *
	 * @return array
	 */
	public function get_enabled_modules(): array;

	/**
	 * Gets the user groups to create.
	 *
	 * @return User_Group[]
	 */
	public function get_user_groups(): array;

	/**
	 * Gets the user group's enabled settings.
	 *
	 * @return array
	 */
	public function get_user_group_settings(): array;

	/**
	 * Gets the list of user groups that should be substituted for default canonical groups.
	 *
	 * @return string[] Map of canonical roles to user group ids.
	 */
	public function get_canonical_user_group_substitutions(): array;

	/**
	 * Gets the settings to set.
	 *
	 * @return array
	 */
	public function get_settings(): array;
}
