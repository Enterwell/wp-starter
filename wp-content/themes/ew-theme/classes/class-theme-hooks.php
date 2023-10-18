<?php

namespace EwStarter;

/**
 * Class Theme_Hooks
 * @package EwStarter
 */
class Theme_Hooks {

	/**
	 * Loads Theme hooks
	 */
	public static function load() {
		// Disable user endpoints for non admin users
		if (!current_user_can('manage_options')) {
			add_filter( 'rest_endpoints', [ static::class, 'disable_user_endpoints' ]);
		}
	}

	/**
	 * Disable user endpoints
	 */
	public static function disable_user_endpoints($endpoints) {
		if ( isset( $endpoints['/wp/v2/users'] ) ) {
			unset( $endpoints['/wp/v2/users'] );
		}
		if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
			unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}
}
