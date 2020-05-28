<?php

class ITSEC_Lib_Email_Confirmation {

	const META_KEY = '_itsec_email_confirmed';

	/**
	 * Is the email address confirmed for the given user.
	 *
	 * @param WP_User|int $user
	 *
	 * @return bool
	 */
	public static function is_email_confirmed( $user ) {
		$user = ITSEC_Lib::get_user( $user );

		return (bool) get_user_meta( $user->ID, self::META_KEY, true );
	}

	/**
	 * Mark the email address for the given user as confirmed.
	 *
	 * @param WP_User|int $user
	 * @param bool        $confirmed
	 */
	public static function mark_email_as_confirmed( $user, $confirmed = true ) {
		$user = ITSEC_Lib::get_user( $user );

		update_user_meta( $user->ID, self::META_KEY, $confirmed );
	}
}
