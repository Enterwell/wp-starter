<?php

namespace iThemesSecurity\Lib\Site_Types;

interface Has_End_Users extends Site_Type, Templating_Site_Type {

	/**
	 * Returns the user group label used for the end users of the site.
	 *
	 * @return string
	 */
	public function get_end_users_group_label(): string;
}
