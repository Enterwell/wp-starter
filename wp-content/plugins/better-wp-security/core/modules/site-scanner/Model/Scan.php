<?php

namespace iThemesSecurity\Site_Scanner;

final class Scan implements \Countable {
	/** @var int */
	private $id;

	/** @var string */
	private $code;

	/** @var string */
	private $url;

	/** @var \DateTimeImmutable */
	private $time;

	/** @var Entry[] */
	private $entries;

	/** @var \WP_Error */
	private $error;

	/** @var array[] */
	private $errors;

	/**
	 * Scan constructor.
	 *
	 * @param int                $id
	 * @param string             $code
	 * @param string             $url
	 * @param \DateTimeImmutable $time
	 * @param Entry[]            $entries
	 * @param array[]            $errors
	 */
	public function __construct( $id, $code, $url, \DateTimeImmutable $time, array $entries, array $errors ) {
		$this->id      = $id;
		$this->code    = $code;
		$this->url     = $url;
		$this->time    = $time;
		$this->entries = $entries;
		$this->errors  = $errors;
	}

	/**
	 * Creates an error scan.
	 *
	 * @param int                $id
	 * @param string             $code
	 * @param string             $url
	 * @param \DateTimeImmutable $time
	 * @param \WP_Error          $error
	 *
	 * @return static
	 */
	public static function as_error( $id, $code, $url, \DateTimeImmutable $time, \WP_Error $error ) {
		$scan        = new static( $id, $code, $url, $time, [], [] );
		$scan->error = $error;

		return $scan;
	}

	/**
	 * The id identifying this scan.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the scan result code.
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Get the URL that was scanned.
	 *
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Get the time a scan occurred.
	 *
	 * @return \DateTimeImmutable
	 */
	public function get_time() {
		return $this->time;
	}

	/**
	 * Get the overall status of the scan.
	 *
	 * @return string
	 */
	public function get_status() {
		if ( $this->is_error() ) {
			return Status::ERROR;
		}

		if ( $this->count( Status::WARN ) ) {
			return Status::WARN;
		}

		if ( $this->get_errors() ) {
			return Status::ERROR;
		}

		return Status::CLEAN;
	}

	/**
	 * Gets the entries accompanying this scan.
	 *
	 * @return Entry[]
	 */
	public function get_entries() {
		return $this->entries;
	}

	/**
	 * Finds an entry with the given slug.
	 *
	 * @param string $slug
	 *
	 * @return Entry|null
	 */
	public function find_entry( $slug ) {
		foreach ( $this->get_entries() as $entry ) {
			if ( $entry->get_slug() === $slug ) {
				return $entry;
			}
		}

		return null;
	}

	/**
	 * Gets the list of errors encountered during the scan.
	 *
	 * @return array[]
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Was this scan an error.
	 *
	 * @return bool
	 */
	public function is_error() {
		return (bool) $this->error;
	}

	/**
	 * Get the error that occurred.
	 *
	 * @return \WP_Error|null
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * Finds an issue by its id.
	 *
	 * @param string $issue_id
	 *
	 * @return Issue|null
	 */
	public function find_issue( $issue_id ) {
		foreach ( $this->get_entries() as $entry ) {
			foreach ( $entry->get_issues() as $issue ) {
				if ( $issue->get_id() === $issue_id ) {
					return $issue;
				}
			}
		}

		return null;
	}

	/**
	 * Counts the total number of issues found in the scan.
	 *
	 * @param string $status Only count issues with the given status.
	 *
	 * @return int
	 */
	public function count( string $status = '' ): int {
		$i = 0;

		foreach ( $this->get_entries() as $entry ) {
			$i += $entry->count( $status );
		}

		return $i;
	}
}
