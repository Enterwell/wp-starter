<?php

use iThemesSecurity\Actor\Lockout_Module;
use iThemesSecurity\Ban_Users\Ban;
use iThemesSecurity\Ban_Users\Database_Repository;
use iThemesSecurity\Lib\Lockout\Context;

/**
 * Listens to the new banned IP hook and persists the IP to the module's settings.
 *
 * @param string  $ip      The IP address.
 * @param Context $context The lockout context.
 */
function itsec_ban_users_handle_new_banned_ip( $ip, $context = null ) {
	ITSEC_Modules::load_module_file( 'labels.php' );
	$repository = ITSEC_Modules::get_container()->get( Database_Repository::class );

	if ( $context instanceof Context && $lockout = $context->get_lockout_module() ) {
		$actor = new Lockout_Module( $lockout );
	} else {
		$actor = null;
	}

	$ban = new Ban( $ip, $actor );

	try {
		$repository->persist( $ban );
	} catch ( \iThemesSecurity\Exception\WP_Error $e ) {
	}
}

add_action( 'itsec_new_banned_ip', 'itsec_ban_users_handle_new_banned_ip', 10, 2 );
