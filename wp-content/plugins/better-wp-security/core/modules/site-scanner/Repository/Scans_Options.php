<?php

namespace iThemesSecurity\Site_Scanner\Repository;

final class Scans_Options {

	/** @var string */
	private $code;

	/** @var int */
	private $page = 1;

	/** @var int */
	private $per_page = 100;

	/** @var \DateTimeImmutable */
	private $before;

	/** @var \DateTimeImmutable */
	private $after;

	/**
	 * Get the code to search for.
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Set the code to search for.
	 *
	 * @param string $code
	 *
	 * @return Scans_Options
	 */
	public function set_code( $code ) {
		$this->code = $code;

		return $this;
	}

	/**
	 * Get the date scans must have run before.
	 *
	 * @return \DateTimeImmutable
	 */
	public function get_before() {
		return $this->before;
	}

	/**
	 * Set the date scans must have run before.
	 *
	 * @param \DateTimeImmutable $before
	 *
	 * @return Scans_Options
	 */
	public function set_before( \DateTimeImmutable $before ) {
		$this->before = $before;

		return $this;
	}

	/**
	 * Get the date scans must have run after.
	 *
	 * @return \DateTimeImmutable
	 */
	public function get_after() {
		return $this->after;
	}

	/**
	 * Set the date scans must have run after.
	 *
	 * @param \DateTimeImmutable $after
	 *
	 * @return Scans_Options
	 */
	public function set_after( \DateTimeImmutable $after ) {
		$this->after = $after;

		return $this;
	}

	/**
	 * Get the page of scans to return.
	 *
	 * @return int
	 */
	public function get_page() {
		return $this->page;
	}

	/**
	 * Set the page of scans to return.
	 *
	 * @param int $page
	 *
	 * @return Scans_Options
	 */
	public function set_page( $page ) {
		$this->page = absint( $page );

		return $this;
	}

	/**
	 * Get the number of items to return per page.
	 *
	 * @return int
	 */
	public function get_per_page() {
		return $this->per_page;
	}

	/**
	 * Set the number of items to return per page.
	 *
	 * @param int $per_page
	 *
	 * @return Scans_Options
	 */
	public function set_per_page( $per_page ) {
		$this->per_page = absint( $per_page );

		return $this;
	}
}
