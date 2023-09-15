<?php

use iThemesSecurity\Lib\Tools\Config_Tool;
use iThemesSecurity\Lib\Tools\Tools_Registry;
use \iThemesSecurity\Lib\Result;
use iThemesSecurity\Lib\Tools\Tools_Runner;

add_action( 'itsec_register_tools', function ( Tools_Registry $registry ) {
	$registry->register( new class( 'change-admin-user', ITSEC_Modules::get_config( 'admin-user' ) ) extends Config_Tool {
		public function is_available(): bool {
			return (bool) username_exists( 'admin' );
		}

		public function run( array $form = [] ): Result {
			global $wpdb;

			$username = $form['new_username'];

			if ( ! validate_username( $username ) ) {
				return Result::error( new WP_Error(
					'itsec.tool.change-admin-user.validate',
					__( 'Invalid username.', 'better-wp-security' )
				) );
			}

			if ( username_exists( $username ) ) {
				return Result::error( new WP_Error(
					'itsec.tool.change-admin-user.duplicate',
					__( 'A user already exists with that username.', 'better-wp-security' )
				) );
			}

			if ( ! ITSEC_Lib::get_lock( 'admin_user', 180 ) ) {
				return Result::error( new WP_Error(
					'itsec.tool.change-admin-user.lock',
					__( 'This tool is already running. Please try again in a few minutes.', 'better-wp-security' )
				) );
			}

			$user_id = username_exists( 'admin' );

			// Query main user table
			$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->users}` SET user_login = %s WHERE user_login = %s", $username, 'admin' ) );

			// Process sitemeta if we're in a multi-site situation
			if ( is_multisite() ) {
				$old_admins = $wpdb->get_var( "SELECT meta_value FROM `" . $wpdb->sitemeta . "` WHERE meta_key = 'site_admins'" );
				// No need to escape the new username. It is already safe via validate_userame() which will check for quotes
				$new_admins = str_replace( '5:"admin"', strlen( $username ) . ':"' . $username . '"', $old_admins );
				$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->sitemeta}` SET meta_value = %s WHERE meta_key = 'site_admins'", $new_admins ) );
			}

			clean_user_cache( $user_id );
			ITSEC_Lib::release_lock( 'admin_user' );

			return Result::success()
			             ->add_success_message( sprintf( __( 'Updated “admin” username to “%s”', 'better-wp-security' ), $username ) );
		}
	} );

	$registry->register( new class( 'change-id-1-user', ITSEC_Modules::get_config( 'admin-user' ) ) extends Config_Tool {
		public function is_available(): bool {
			$user = get_userdata( 1 );

			return $user instanceof WP_User && $user->exists();
		}

		public function run( array $form = [] ): Result {
			global $wpdb;

			if ( ! ITSEC_Lib::get_lock( 'admin_user', 180 ) ) {
				return Result::error( new WP_Error(
					'itsec.tool.change-id-1-user.lock',
					__( 'This tool is already running. Please try again in a few minutes.', 'better-wp-security' )
				) );
			}

			$user = get_userdata( 1 );

			$wpdb->query( "DELETE FROM `{$wpdb->users}` WHERE ID = 1;" );
			$wpdb->insert( $wpdb->users, array(
				'user_login'          => $user->user_login,
				'user_pass'           => $user->user_pass,
				'user_nicename'       => $user->user_nicename,
				'user_email'          => $user->user_email,
				'user_url'            => $user->user_url,
				'user_registered'     => $user->user_registered,
				'user_activation_key' => $user->user_activation_key,
				'user_status'         => $user->user_status,
				'display_name'        => $user->display_name
			) );

			$new_user = $wpdb->insert_id;

			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_author = %d WHERE post_author = 1", $new_user ) );
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET user_id = %d WHERE user_id = 1", $new_user ) );
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->comments} SET user_id = %d WHERE user_id = 1", $new_user ) );
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->links} SET link_owner = %d WHERE link_owner = 1", $new_user ) );

			/**
			 * Fires when the admin user with id of #1 has been changed.
			 *
			 * @since 6.3.0
			 *
			 * @param int     $new_user The new user's ID.
			 * @param WP_User $user     The old user object.
			 */
			do_action( 'itsec_change_admin_user_id', $new_user, $user );

			ITSEC_Lib::release_lock( 'admin_user' );

			return Result::success( $new_user )
			             ->add_success_message( __( 'Updated user ID.', 'better-wp-security' ) );
		}
	} );
} );

/**
 * Changes Admin User.
 *
 * Changes the username and id of the 1st user
 *
 * @param string $username The username to change.
 * @param bool   $id       Whether to change the id.
 *
 * @return bool
 */
function itsec_change_admin_user( $username = null, $id = false ) {
	_deprecated_function( __METHOD__, '7.0.0', Tools_Runner::class );

	$runner   = ITSEC_Modules::get_container()->get( Tools_Runner::class );
	$registry = ITSEC_Modules::get_container()->get( Tools_Registry::class );

	if ( $username && ! $runner->run_tool( $registry->get_tool( 'change-admin-user' ), [ 'new_username' => $username ] )->is_success() ) {
		return false;
	}

	if ( $id && ! $runner->run_tool( $registry->get_tool( 'change-id-1-user' ) )->is_success() ) {
		return false;
	}

	return true;
}
