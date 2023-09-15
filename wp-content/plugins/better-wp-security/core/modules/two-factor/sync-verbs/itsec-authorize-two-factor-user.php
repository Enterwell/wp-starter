<?php

class Ithemes_Sync_Verb_ITSEC_Authorize_Two_Factor_User extends Ithemes_Sync_Verb {

	public static $name        = 'itsec-authorize-two-factor-user';
	public static $description = 'Authorize existing two-factor request for a given user';

	public $default_arguments = array(
	);

	public function run( $arguments ) {

		global $itsec_globals;
		
		if ( ! isset( $arguments['session'] ) ) {
			return false; //Session ID not provided
		}
		
		$session = ITSEC_Login_Interstitial_Session::get( $arguments['session'] );
		
		if ( is_wp_error( $session ) ) {
			return false;
		}
		
		if ( '2fa' === $session->get_current_interstitial() ) {
			if ( ITSEC_Core::get_login_interstitial()->proceed_to_next( $session ) ) {
				return true;
			}
		}

		return false;

	}

}
