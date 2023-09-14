<?php

namespace iThemesSecurity\Actor;

final class User_Factory implements Actor_Factory {
	public function make( $identifier ) {
		$user = get_userdata( $identifier );

		if ( ! $user ) {
			return null;
		}

		return new User( $user );
	}

	public function get_slug() {
		return 'user';
	}

	public function get_label() {
		return __( 'User', 'better-wp-security' );
	}

	public function is_determinate() {
		return false;
	}

	public function get_actors( $query = '' ) {
		$user_query = new \WP_User_Query( [
			'search' => $query,
			'number' => 500,
		] );

		return array_map( function ( $user ) {
			return new User( $user );
		}, $user_query->get_results() );
	}
}
