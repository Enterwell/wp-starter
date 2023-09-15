<?php

use iThemesSecurity\User_Groups\Upgrader;
use iThemesSecurity\User_Groups\User_Group;

class ITSEC_Dashboard_Setup implements \iThemesSecurity\Contracts\Runnable {
	public function run() {
		add_action( 'itsec_modules_do_plugin_upgrade', [ $this, 'on_upgrade' ] );
	}

	/**
	 * Runs the upgrade routine.
	 *
	 * @param int $itsec_old_version
	 */
	public function on_upgrade( $itsec_old_version ) {
		if ( $itsec_old_version < 4117 ) {
			$upgrader       = ITSEC_Modules::get_container()->get( Upgrader::class );
			$disabled_users = ITSEC_Modules::get_setting( 'dashboard', 'disabled_users' );
			$user_group_ids = [ $upgrader->get_default_group_id( 'administrator' ) ];

			if ( $disabled_users ) {
				$user_group = $upgrader->find_or_create( __( 'Dashboard Owners', 'better-wp-security' ), static function ( User_Group $user_group ) use ( $disabled_users ) {
					$users = ITSEC_Lib_Canonical_Roles::get_users_with_canonical_role( 'administrator' );

					foreach ( $users as $user ) {
						if ( ! in_array( $user->ID, $disabled_users, false ) && user_can( $user, apply_filters( 'itsec_cap_required', is_multisite() ? 'manage_network_options' : 'manage_options' ) ) ) {
							$user_group->add_user( $user );
						}
					}
				} );

				if ( $user_group->get_users() ) {
					$user_group_ids = [ $user_group->get_id() ];
				} else {
					$user_group_ids = [];
				}
			}

			ITSEC_Modules::set_setting( 'dashboard', 'group', $user_group_ids );
		}

		if ( $itsec_old_version < 4123 ) {
			if (
				! ITSEC_Modules::get_setting( 'dashboard', 'migrated' ) &&
				! get_posts( [ 'post_type' => ITSEC_Dashboard::CPT_DASHBOARD, 'numberposts' => 1 ] )
			) {
				require_once( dirname( __FILE__ ) . '/class-itsec-dashboard-util.php' );
				ITSEC_Dashboard_Util::migrate();
				ITSEC_Modules::set_setting( 'dashboard', 'migrated', true );
			}

			$removed_cards = new WP_Query( [
				'post_type'    => ITSEC_Dashboard::CPT_CARD,
				'meta_key'     => ITSEC_Dashboard::META_CARD,
				'meta_value'   => [ 'four-oh-four' ],
				'meta_compare' => 'IN',
			] );

			foreach ( $removed_cards->posts as $post ) {
				wp_delete_post( $post->ID, true );
			}
		}
	}
}

( new ITSEC_Dashboard_Setup() )->run();
