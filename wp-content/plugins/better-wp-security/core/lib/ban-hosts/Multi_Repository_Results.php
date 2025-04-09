<?php

namespace iThemesSecurity\Ban_Hosts;

final class Multi_Repository_Results {

	/** @var Repository_Ban[] */
	private $bans;

	/** @var Multi_Cursor|null */
	private $cursor;

	/**
	 * Multi_Repository_Results constructor.
	 *
	 * @param Repository_Ban[]  $bans
	 * @param Multi_Cursor|null $cursor
	 */
	public function __construct( array $bans, Multi_Cursor $cursor = null ) {
		$this->bans   = $bans;
		$this->cursor = $cursor;
	}

	/**
	 * Gets the list of bans from the multi repository.
	 *
	 * @return Repository_Ban[]
	 */
	public function get_bans() {
		return $this->bans;
	}

	/**
	 * Gets the cursor if this was a paginated request.
	 *
	 * @return Multi_Cursor|null
	 */
	public function get_cursor() {
		return $this->cursor;
	}
}
