<?php

class ITSEC_Email_Confirmation {

	public function run() {
		add_action( 'after_password_reset', array( $this, 'after_password_reset' ) );
		add_action( 'profile_update', array( $this, 'on_user_update' ), 10, 2 );
	}

	/**
	 * When a user's password has been reset, mark the user's email address as confirmed.
	 *
	 * @param WP_User $user
	 */
	public function after_password_reset( $user ) {
		ITSEC_Lib::load( 'email-confirmation' );
		ITSEC_Lib_Email_Confirmation::mark_email_as_confirmed( $user );
	}

	/**
	 * When a user's email is updated, mark their email confirmation as not confirmed.
	 *
	 * @param int    $user_id  User id.
	 * @param object $old_data Old data.
	 */
	public function on_user_update( $user_id, $old_data ) {
		$user = get_userdata( $user_id );

		if ( $user->user_email === $old_data->user_email ) {
			return;
		}

		ITSEC_Lib::load( 'email-confirmation' );
		$change = get_user_meta( $user->ID, '_new_email', true );

		if (
			defined( 'IS_PROFILE_PAGE' ) &&
			IS_PROFILE_PAGE &&
			! empty( $_GET['newuseremail'] ) &&
			is_array( $change ) &&
			isset( $change['hash'], $change['newemail'] ) &&
			$change['newemail'] === $user->user_email &&
			hash_equals( $change['hash'], $_GET['newuseremail'] )
		) {
			ITSEC_Lib_Email_Confirmation::mark_email_as_confirmed( $user );

			return;
		}

		ITSEC_Lib_Email_Confirmation::mark_email_as_confirmed( $user, false );
	}
}
