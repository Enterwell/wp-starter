<?php

namespace iThemesSecurity\Ban_Hosts;

interface Repository_Ban extends Ban {

	/**
	 * Gets the id for the ban.
	 *
	 * @return int
	 */
	public function get_id();

	/**
	 * Gets the source
	 *
	 * @return string
	 */
	public function get_source();

	/**
	 * Gets the time this entry was created.
	 *
	 * @return \DateTimeImmutable
	 */
	public function get_created_at();
}
