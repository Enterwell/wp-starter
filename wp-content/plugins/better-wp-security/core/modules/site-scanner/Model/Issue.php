<?php

namespace iThemesSecurity\Site_Scanner;

interface Issue {

	/**
	 * Get the unique issue id.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get's the status for this issue.
	 *
	 * @return string One of the {@see Status} enums.
	 */
	public function get_status();

	/**
	 * Gets the short, 1 sentence, description of the issue.
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Gets a link to see more about the issue.
	 *
	 * @return string
	 */
	public function get_link();

	/**
	 * Gets the entry this issue belongs to.
	 *
	 * @return string
	 */
	public function get_entry();

	/**
	 * Get meta information about the issue.
	 *
	 * This is untyped beyond returning an array.
	 *
	 * @return array
	 */
	public function get_meta();
}
