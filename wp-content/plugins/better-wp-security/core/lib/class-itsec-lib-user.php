<?php

class ITSEC_Lib_User {

	/**
	 * Is the given user using Two-Factor.
	 *
	 * Either because they have opted-in to it, or it is required for their account.
	 *
	 * @param WP_User $user
	 *
	 * @return bool
	 */
	public static function is_user_using_two_factor( WP_User $user ): bool {
		return apply_filters( 'itsec_is_user_using_two_factor', false, $user );
	}
}
