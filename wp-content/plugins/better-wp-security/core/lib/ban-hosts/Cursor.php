<?php

namespace iThemesSecurity\Ban_Hosts;

final class Cursor {

	/** @var \DateTimeImmutable */
	private $last_date;

	/** @var int */
	private $last_id;

	/** @var int */
	private $last_count;

	/**
	 * Cursor constructor.
	 *
	 * @param \DateTimeImmutable $last_date
	 * @param int                $last_id
	 * @param int                $last_count
	 */
	public function __construct( \DateTimeImmutable $last_date = null, $last_id = 0, $last_count = 0 ) {
		$this->last_date  = $last_date;
		$this->last_id    = $last_id;
		$this->last_count = $last_count;
	}

	/**
	 * Parses a formatted value into a cursor.
	 *
	 * @param string $formatted
	 *
	 * @return static
	 */
	public static function parse( $formatted ) {
		$parts = explode( '-', $formatted );

		if ( count( $parts ) !== 3 ) {
			throw new Malformed_Cursor( esc_html__( 'Cursor definition for repository is malformed.', 'better-wp-security' ) );
		}

		list( $last_date, $last_id, $last_count ) = $parts;

		return new static(
			$last_date ? new \DateTimeImmutable( "@{$last_date}" ) : null,
			(int) $last_id,
			(int) $last_count
		);
	}

	/**
	 * Creates a new cursor from a results array.
	 *
	 * @param array $results
	 *
	 * @return static
	 */
	public static function from_results( array $results ) {
		$last = \ITSEC_Lib::array_key_last( $results );

		if ( $last !== null ) {
			$last_id = $results[ $last ]->get_id();
			$date    = $results[ $last ]->get_created_at();
		} else {
			$last_id = 0;
			$date    = null;
		}

		return new static( $date, $last_id, count( $results ) );
	}

	/**
	 * Gets the date results must be before.
	 *
	 * @return \DateTimeImmutable|null
	 */
	public function get_last_date() {
		return $this->last_date;
	}

	/**
	 * Gets the id results must be before.
	 *
	 * @return int
	 */
	public function get_last_id() {
		return $this->last_id;
	}

	/**
	 * Gets the count of results from the previous page.
	 *
	 * @return int
	 */
	public function get_last_count() {
		return $this->last_count;
	}

	/**
	 * Converts the cursor to a string.
	 *
	 * @return string
	 */
	public function __toString() {
		return implode( '-', [ $this->get_last_date()->getTimestamp(), $this->get_last_id(), $this->get_last_count() ] );
	}
}
