<?php

namespace iThemesSecurity\Ban_Hosts;

use iThemesSecurity\Actor\Actor;

interface Ban {

	/**
	 * Does this ban entry match the given IP address.
	 *
	 * @param string $ip The IP address.
	 *
	 * @return bool
	 */
	public function matches( $ip );

	/**
	 * Gets the time this entry was created.
	 *
	 * @return \DateTimeImmutable|null
	 */
	public function get_created_at();

	/**
	 * Gets the actor who added this ban entry.
	 *
	 * @return Actor|null
	 */
	public function get_created_by();

	/**
	 * Gets the comment describing the ban entry.
	 *
	 * @return string
	 */
	public function get_comment();

	/**
	 * Human facing label of what is banned.
	 *
	 * This should be as short as possible. For example:
	 *
	 * - 192.168.1.1
	 * - 192.168.1/8
	 *
	 * @return string
	 */
	public function __toString();
}
