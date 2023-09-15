<?php

namespace iThemesSecurity\Ban_Hosts;

use iThemesSecurity\Actor\Actor;

final class Filters {

	const ACTOR_TYPE = 'actor_type';
	const ACTOR_IDENTIFIER = 'actor_id';
	const SEARCH = 'search';
	const CREATED_AFTER = 'created_after';
	const CREATED_BEFORE = 'created_before';

	const ALL = [
		self::ACTOR_TYPE,
		self::ACTOR_IDENTIFIER,
		self::SEARCH,
		self::CREATED_AFTER,
		self::CREATED_BEFORE,
	];

	/** @var int */
	private $limit;

	/** @var Cursor */
	private $after_cursor;

	/** @var Cursor */
	private $before_cursor;

	/** @var string */
	private $actor_type;

	/** @var string */
	private $actor_identifier;

	/** @var string */
	private $search;

	/** @var \DateTimeImmutable */
	private $created_after;

	/** @var \DateTimeImmutable */
	private $created_before;

	/**
	 * Limits to results occurring after the given cursor.
	 *
	 * @param Cursor $cursor
	 *
	 * @return Filters
	 */
	public function with_after_cursor( Cursor $cursor ) {
		$clone               = clone $this;
		$clone->after_cursor = $cursor;

		return $clone;
	}

	/**
	 * Get the after cursor.
	 *
	 * @return Cursor|null
	 */
	public function get_after_cursor() {
		return $this->after_cursor;
	}

	/**
	 * Limits to results occurring before the given cursor.
	 *
	 * @param Cursor $cursor
	 *
	 * @return Filters
	 */
	public function with_before_cursor( Cursor $cursor ) {
		$clone                = clone $this;
		$clone->before_cursor = $cursor;

		return $clone;
	}

	/**
	 * Get the before cursor.
	 *
	 * @return Cursor|null
	 */
	public function get_before_cursor() {
		return $this->before_cursor;
	}

	/**
	 * Gets the number of bans to return.
	 *
	 * @return int
	 */
	public function get_limit() {
		return $this->limit;
	}

	/**
	 * Returns a limited number of results.
	 *
	 * @param int $limit
	 *
	 * @return Filters
	 */
	public function with_limit( $limit ) {
		$clone        = clone $this;
		$clone->limit = $limit;

		return $clone;
	}

	/**
	 * Gets the actor type.
	 *
	 * @return string
	 */
	public function get_actor_type() {
		return $this->actor_type;
	}

	/**
	 * Limits to bans with the given actor type.
	 *
	 * @see Actor::get_type()
	 *
	 * @param string $actor_type
	 *
	 * @return Filters
	 */
	public function with_actor_type( $actor_type ) {
		$clone             = clone $this;
		$clone->actor_type = $actor_type;

		return $clone;
	}

	/**
	 * Gets the actor identifier.
	 *
	 * @return string
	 */
	public function get_actor_identifier() {
		return $this->actor_identifier;
	}

	/**
	 * Limits to bans with the given actor identifier.
	 *
	 * @see Actor::get_identifier()
	 *
	 * @param string $actor_identifier
	 *
	 * @return Filters
	 */
	public function with_actor_identifier( $actor_identifier ) {
		$clone                   = clone $this;
		$clone->actor_identifier = $actor_identifier;

		return $clone;
	}

	/**
	 * Gets the comment.
	 *
	 * @return string
	 */
	public function get_search() {
		return $this->search;
	}

	/**
	 * Limits to bans that have comments containing the given substring.
	 *
	 * @param string $search
	 *
	 * @return Filters
	 */
	public function with_search( $search ) {
		$clone         = clone $this;
		$clone->search = $search;

		return $clone;
	}

	/**
	 * Gets the created after date.
	 *
	 * @return \DateTimeImmutable
	 */
	public function get_created_after() {
		return $this->created_after;
	}

	/**
	 * Limits to bans that occur after the given date.
	 *
	 * @param \DateTimeImmutable $created_after
	 *
	 * @return Filters
	 */
	public function with_created_after( \DateTimeImmutable $created_after = null ) {
		$clone                = clone $this;
		$clone->created_after = $created_after;

		return $clone;
	}

	/**
	 * Gets the created before date.
	 *
	 * @return \DateTimeImmutable
	 */
	public function get_created_before() {
		return $this->created_before;
	}

	/**
	 * Limits to bans that occur before the given date.
	 *
	 * @param \DateTimeImmutable $created_before
	 *
	 * @return Filters
	 */
	public function with_created_before( \DateTimeImmutable $created_before = null ) {
		$clone                 = clone $this;
		$clone->created_before = $created_before;

		return $clone;
	}
}
