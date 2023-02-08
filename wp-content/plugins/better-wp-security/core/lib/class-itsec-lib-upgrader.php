<?php

class ITSEC_Lib_Upgrader {

	/**
	 * Gets the update available for a plugin.
	 *
	 * @param string $plugin The plugin file.
	 *
	 * @return array|WP_Error
	 */
	public function get_available_plugin_update( $plugin ) {
		$update_plugins = get_site_transient( 'update_plugins' );

		if ( empty( $update_plugins ) || empty( $update_plugins->response ) || empty( $update_plugins->response[ $plugin ] ) ) {
			return new WP_Error( 'itsec_updates_no_update', esc_html__( 'No update is listed for the plugin.', 'better-wp-security' ) );
		}

		$api = $update_plugins->response[ $plugin ];

		if ( ! empty( $api->requires_php ) && ! is_php_version_compatible( $api->requires_php ) ) {
			$message = sprintf(
				esc_html__( 'An update to version %1$s is available, but it doesn&#8217;t work with your version of PHP. %2$sLearn more about updating PHP%3$s.', 'better-wp-security' ),
				$api->new_version,
				'<a href="' . esc_url( wp_get_update_php_url() ) . '">',
				'</a>'
			);

			return new WP_Error( 'itsec_updates_incompatible_php', $message, [ 'requires_php' => $api->requires_php ] );
		}

		if ( empty( $api->package ) ) {
			return new WP_Error( 'itsec_updates_missing_package', esc_html__( 'An available update is listed, but automatic updates are not configured.', 'better-wp-security' ) );
		}

		return [
			'version' => $api->new_version,
			'package' => $api->package,
		];
	}

	/**
	 * Gets the update available for a theme.
	 *
	 * @param string $theme The theme slug.
	 *
	 * @return array|WP_Error
	 */
	public function get_available_theme_update( $theme ) {
		$update_themes = get_site_transient( 'update_themes' );

		if ( empty( $update_themes ) || empty( $update_themes->response ) || empty( $update_themes->response[ $theme ] ) ) {
			return new WP_Error( 'itsec_updates_no_update', esc_html__( 'No update is listed for the theme.', 'better-wp-security' ) );
		}

		$api = $update_themes->response[ $theme ];

		if ( empty( $api['package'] ) ) {
			return new WP_Error( 'itsec_updates_missing_package', esc_html__( 'An available update is listed, but automatic updates are not configured.', 'better-wp-security' ) );
		}

		return [
			'version' => $api['new_version'],
			'package' => $api['package'],
		];
	}

	/**
	 * Updates the plugin to the latest version.
	 *
	 * @param string $plugin The plugin file.
	 *
	 * @return true|WP_Error
	 */
	public function update_plugin( $plugin ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		require_once( ABSPATH . 'wp-admin/includes/misc.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$skin            = new ITSEC_Upgrader_Skin();
		$plugin_upgrader = new Plugin_Upgrader( $skin );

		$result = $plugin_upgrader->upgrade( $plugin, array( 'clear_update_cache' => false ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( $skin->errors ) {
			$strings = array_merge( ...array_map( 'ITSEC_Response::get_error_strings', $skin->errors ) );

			return new WP_Error( 'itsec_updates_update_error', wp_sprintf(
				esc_html__( 'Unable to update the plugin: %l', 'better-wp-security' ),
				$strings
			) );
		}

		if ( false === $result ) {
			return new WP_Error( 'itsec_updates_unknown_update_error', esc_html__( 'An unknown issue prevented the update from completing.', 'better-wp-security' ) );
		}

		wp_clean_plugins_cache();
		wp_update_plugins();

		return true;
	}

	/**
	 * Updates the theme to the latest version.
	 *
	 * @param string $theme
	 *
	 * @return true|WP_Error
	 */
	public function update_theme( $theme ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		require_once( ABSPATH . 'wp-admin/includes/misc.php' );
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		$skin     = new ITSEC_Upgrader_Skin();
		$upgrader = new Theme_Upgrader( $skin );
		$result   = $upgrader->upgrade( $theme, array( 'clear_update_cache' => false ) );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( $skin->errors ) {
			$strings = array_merge( ...array_map( 'ITSEC_Response::get_error_strings', $skin->errors ) );

			return new WP_Error( 'itsec_updates_update_error', wp_sprintf(
				esc_html__( 'Unable to update the theme: %l', 'better-wp-security' ),
				$strings
			) );
		}

		if ( false === $result ) {
			return new WP_Error( 'itsec_updates_unknown_update_error', esc_html__( 'An unknown issue prevented the update from completing.', 'better-wp-security' ) );
		}

		wp_clean_themes_cache();
		wp_update_themes();

		return true;
	}
}
