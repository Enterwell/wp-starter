<?php

namespace iThemesSecurity\Site_Scanner;

final class Entry implements \Countable {
	private $slug;
	private $title;
	private $status;
	private $issues;

	/**
	 * Entry constructor.
	 *
	 * @param string  $slug   The slug uniquely identifying the entry.
	 * @param string  $title  The entry title.
	 * @param string  $status The overall status, {@see Status}, of the entry.
	 * @param Issue[] $issues The list of issues part of this entry.
	 */
	public function __construct( $slug, $title, $status, array $issues = [] ) {
		$this->slug   = $slug;
		$this->title  = $title;
		$this->status = $status;
		$this->issues = $issues;
	}

	/**
	 * Gets the entry slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Gets the entry title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Gets the entry status.
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Gets the entry issues.
	 *
	 * @return Issue[]
	 */
	public function get_issues() {
		return $this->issues;
	}

	/**
	 * Counts the total number of issues found in the entry.
	 *
	 * @param string $status Only count issues with the given status.
	 *
	 * @return int
	 */
	public function count( $status = '' ) {
		$i = 0;

		foreach ( $this->get_issues() as $issue ) {
			if ( ! $status || $status === $issue->get_status() ) {
				$i ++;
			}
		}

		return $i;
	}
}
