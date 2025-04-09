<?php

namespace iThemesSecurity\Ban_Hosts;

use iThemesSecurity\Exception\WP_Error;

interface Persistable extends Repository {

	/**
	 * Persists a ban to the repository.
	 *
	 * @param Repository_Ban $ban
	 *
	 * @return Repository_Ban
	 * @throws WP_Error
	 */
	public function persist( Repository_Ban $ban );

	/**
	 * Fills a ban from request data.
	 *
	 * @param array               $data
	 * @param Repository_Ban|null $ban
	 *
	 * @return Repository_Ban
	 */
	public function fill( array $data, Repository_Ban $ban = null );
}
