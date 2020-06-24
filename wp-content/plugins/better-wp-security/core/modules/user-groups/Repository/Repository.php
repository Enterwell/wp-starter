<?php

namespace iThemesSecurity\User_Groups\Repository;

use iThemesSecurity\Exception\WP_Error;
use iThemesSecurity\User_Groups\User_Group;

interface Repository {

	/**
	 * Get the next id for a user group.
	 *
	 * @return string
	 */
	public function next_id();

	/**
	 * Get a user group by it's id.
	 *
	 * @param string $id
	 *
	 * @return User_Group
	 * @throws User_Group_Not_Found
	 */
	public function get( $id );

	/**
	 * Does the repository have a user group identified by the uuid.
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function has( $id );

	/**
	 * Persist a user group.
	 *
	 * @param User_Group $user_group
	 *
	 * @return void
	 *
	 * @throws WP_Error
	 */
	public function persist( User_Group $user_group );

	/**
	 * Delete a user group.
	 *
	 * @param User_Group $user_group
	 *
	 * @return void
	 */
	public function delete( User_Group $user_group );

	/**
	 * Get a list of all the user groups.
	 *
	 * @return User_Group[]
	 */
	public function all();
}
