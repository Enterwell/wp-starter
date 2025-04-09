<?php

namespace iThemesSecurity\User_Groups\Repository;

use iThemesSecurity\User_Groups\User_Group;

trait Decorator {

	/** @var Repository */
	private $decorates;

	public function next_id() {
		return $this->decorates->next_id();
	}

	public function get( $id ) {
		$this->decorates->get( $id );
	}

	public function has( $id ) {
		return $this->decorates->has( $id );
	}

	public function persist( User_Group $user_group, array $args = [] ) {
		$this->decorates->persist( $user_group, $args );
	}

	public function delete( User_Group $user_group ) {
		$this->decorates->delete( $user_group );
	}

	public function all() {
		return $this->decorates->all();
	}
}
