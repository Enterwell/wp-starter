<?php

namespace iThemesSecurity\Ban_Hosts;

interface Deletable extends Repository {

	/**
	 * Deletes a Ban.
	 *
	 * @param Repository_Ban $ban
	 */
	public function delete( Repository_Ban $ban );
}
