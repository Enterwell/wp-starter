<?php
namespace EwStarter\Repositories\Interfaces;


use EwStarter\Models\User_Application;
use Exception;

interface User_Applications_Repository_Interface {
	/**
	 * Saves user application.
	 *
	 * @param User_Application $user_application
	 *
	 * @return User_Application
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function save( User_Application $user_application ): User_Application;

	/**
	 * Get user application by id.
	 *
	 * @param int $id
	 *
	 * @return User_Application
	 */
	public function get( int $id ): User_Application;
}
