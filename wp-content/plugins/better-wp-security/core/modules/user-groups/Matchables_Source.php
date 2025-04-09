<?php

namespace iThemesSecurity\User_Groups;

use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\User_Groups\Repository\Repository;
use iThemesSecurity\User_Groups\Repository\User_Group_Not_Found;

final class Matchables_Source {

	/** @var Repository */
	private $repository;

	/** @var Matchable[] */
	private $additional = [];

	/**
	 * Matchables_Source constructor.
	 *
	 * @param Repository $repository
	 */
	public function __construct( Repository $repository ) { $this->repository = $repository; }

	/**
	 * Add a matchable.
	 *
	 * @param Matchable $matchable
	 *
	 * @return $this
	 */
	public function add( Matchable $matchable ) {
		$this->additional[ $matchable->get_id() ] = $matchable;

		return $this;
	}

	/**
	 * Find a matchable by its string identifier.
	 *
	 * @param string $id
	 *
	 * @return Matchable
	 *
	 * @throws Matchable_Not_Found
	 */
	public function find( $id ) {
		if ( ! is_string( $id ) ) {
			throw new Invalid_Argument_Exception( 'Id must be a string.' );
		}

		try {
			return isset( $this->additional[ $id ] ) ? $this->additional[ $id ] : $this->repository->get( $id );
		} catch ( User_Group_Not_Found $e ) {
			throw new Matchable_Not_Found( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * Check if the matchables source has a matchable identified by the given id.
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function has( $id ) {
		return isset( $this->additional[ $id ] ) || $this->repository->has( $id );
	}

	/**
	 * Get all matchables.
	 *
	 * @return Matchable[]
	 */
	public function all() {
		return array_merge( $this->repository->all(), array_values( $this->additional ) );
	}
}
