<?php

namespace iThemesSecurity\Ban_Hosts;

interface Repository extends Source {

	/**
	 * Gets the slug uniquely identifying this repository.
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Gets a list of all bans in the repository.
	 *
	 * Results must be ordered in reverse chronological order.
	 *
	 * @param Filters $filters
	 *
	 * @return Repository_Ban[]
	 */
	public function get_bans( Filters $filters );

	/**
	 * Counts the number of bans that match the given filters.
	 *
	 * @param Filters $filters
	 *
	 * @return int
	 */
	public function count_bans( Filters $filters );

	/**
	 * Gets the list of supported query filters.
	 *
	 * A list of {@see Filters} constants declaring the available options.
	 *
	 * @return string[]
	 */
	public function get_supported_filters();

	/**
	 * Retrieves a Ban object identified by the given uuid.
	 *
	 * @param int $id The ban's id.
	 *
	 * @return Repository_Ban|null
	 */
	public function get( $id );

	/**
	 * Finds a ban for the given host.
	 *
	 * @param string $host The host to find a ban for.
	 *
	 * @return Repository_Ban|null
	 */
	public function find_ban_for_host( $host );
}
