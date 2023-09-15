<?php

/**
 * Overrides the RCP IP address to use iThemes Security.
 *
 * @return string
 */
function itsec_rcp_get_ip() {
	return ITSEC_Lib::get_ip();
}

add_filter( 'rcp_get_ip', 'itsec_rcp_get_ip' );

/**
 * Adds the RCP login redirect to the session.
 *
 * @param ITSEC_Login_Interstitial_Session $session
 */
function itsec_rcp_initialize_interstitial( ITSEC_Login_Interstitial_Session $session ) {
	if ( isset( $_REQUEST['rcp_redirect'] ) ) {
		$session->set_redirect_to( $_REQUEST['rcp_redirect'] );
	}
}

add_action( 'itsec_initialize_login_interstitial_session_from_global_state', 'itsec_rcp_initialize_interstitial' );
