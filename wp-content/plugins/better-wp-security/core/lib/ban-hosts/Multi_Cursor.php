<?php

namespace iThemesSecurity\Ban_Hosts;

final class Multi_Cursor {

	/** @var Repository[] */
	private $repositories;

	/** @var Cursor[] */
	private $cursors = [];

	/**
	 * Cursor constructor.
	 *
	 * @param string     $formatted
	 * @param Repository ...$repositories
	 */
	public function __construct( $formatted = '', Repository ...$repositories ) {
		$this->repositories = $repositories;

		if ( $formatted ) {
			$parts = explode( ',', $formatted );

			foreach ( $this->repositories as $i => $repository ) {
				if ( ! isset( $parts[ $i ] ) ) {
					throw new Malformed_Cursor( esc_html__( 'Cursor missing definition for repository.', 'better-wp-security' ) );
				}

				$part = $parts[ $i ];

				if ( '' === $part ) {
					continue;
				}

				$this->cursors[ $repository->get_slug() ] = Cursor::parse( $part );
			}
		}
	}

	/**
	 * Adds a cursor for a repository's results.
	 *
	 * @param Repository $repository
	 * @param Cursor     $cursor
	 *
	 * @return Multi_Cursor
	 */
	public function with_cursor( Repository $repository, Cursor $cursor ) {
		$clone = clone $this;

		$clone->cursors[ $repository->get_slug() ] = $cursor;

		return $clone;
	}

	/**
	 * Gets the cursor for a single repository.
	 *
	 * @param Repository $repository
	 *
	 * @return Cursor|null
	 */
	public function get_cursor( Repository $repository ) {
		return isset( $this->cursors[ $repository->get_slug() ] ) ? $this->cursors[ $repository->get_slug() ] : null;
	}

	/**
	 * Gets the last count for a repository.
	 *
	 * @param Repository $repository
	 *
	 * @return int|null
	 */
	public function get_last_count( Repository $repository ) {
		return $this->get_cursor( $repository ) ? $this->get_cursor( $repository )->get_last_count() : null;
	}

	/**
	 * Checks if more results are available.
	 *
	 * @param int $page_size
	 *
	 * @return bool
	 */
	public function has_more( $page_size ) {
		foreach ( $this->cursors as $cursor ) {
			if ( $cursor->get_last_count() >= $page_size ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Converts the cursor to a string representation.
	 *
	 * @return string
	 */
	public function __toString() {
		$parts = [];

		foreach ( $this->repositories as $repository ) {
			$cursor = isset( $this->cursors[ $repository->get_slug() ] ) ? $this->cursors[ $repository->get_slug() ] : null;

			if ( ! $cursor ) {
				$parts[] = '';
				continue;
			}

			$parts[] = (string) $cursor;
		}

		return implode( ',', $parts );
	}
}
