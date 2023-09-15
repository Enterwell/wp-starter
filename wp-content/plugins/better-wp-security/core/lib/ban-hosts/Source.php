<?php

namespace iThemesSecurity\Ban_Hosts;

/**
 * Interface Source
 *
 * @package iThemesSecurity\Ban_Hosts
 */
interface Source {
	/**
	 * Finds a ban for the given host.
	 *
	 * @param string $host The host to find a ban for.
	 *
	 * @return Ban|null
	 */
	public function find_ban_for_host( $host );
}
