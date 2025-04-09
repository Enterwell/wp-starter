<?php

namespace iThemesSecurity\User_Groups;

use iThemesSecurity\User_Groups\Repository\Repository;

final class Everybody_Else implements Matchable {

	const ID = 'everybody-else';

	/** @var Repository */
	private $repository;

	/**
	 * Everybody_Else constructor.
	 *
	 * @param Repository $repository
	 */
	public function __construct( Repository $repository ) { $this->repository = $repository; }

	public function matches( Match_Target $target ) {
		foreach ( $this->repository->all() as $user_group ) {
			if ( $user_group->matches( $target ) ) {
				return false;
			}
		}

		return true;
	}

	public function get_id() {
		return self::ID;
	}

	public function get_label() {
		return __( 'Everybody Else', 'better-wp-security' );
	}
}
