<?php

namespace iThemesSecurity\Site_Scanner;

final class Vulnerability_Fixer implements Fixer {

	/** @var \ITSEC_Lib_Upgrader */
	private $upgrader;

	/**
	 * Vulnerability_Fixer constructor.
	 *
	 * @param \ITSEC_Lib_Upgrader $upgrader
	 */
	public function __construct( \ITSEC_Lib_Upgrader $upgrader ) { $this->upgrader = $upgrader; }

	public function can_user_fix( \WP_User $user, Issue $issue ) {
		if ( ! $issue instanceof Vulnerability_Issue ) {
			return new \WP_Error( 'itsec_site_scanner_fixer_not_fixable', __( 'This issue cannot be automatically fixed.', 'better-wp-security' ) );
		}

		switch ( $issue->get_meta()['type'] ) {
			case 'plugin':
				if ( user_can( $user, 'update_plugins' ) ) {
					return true;
				}

				return new \WP_Error( 'itsec_site_scanner_fixer_missing_update_permissions', __( 'You must have permission to update plugins to fix this issue.', 'better-wp-security' ) );
			case 'theme':
				if ( user_can( $user, 'update_themes' ) ) {
					return true;
				}

				return new \WP_Error( 'itsec_site_scanner_fixer_missing_update_permissions', __( 'You must have permission to update themes to fix this issue.', 'better-wp-security' ) );
			default:
				return new \WP_Error( 'itsec_site_scanner_fixer_not_fixable', __( 'This issue cannot be automatically fixed.', 'better-wp-security' ) );
		}
	}

	public function is_fixable( Issue $issue ) {
		if ( ! $issue instanceof Vulnerability_Issue ) {
			return false;
		}

		if ( ! $fixed_in = $issue->get_fixed_in() ) {
			return false;
		}

		$meta = $issue->get_meta();

		switch ( $meta['type'] ) {
			case 'plugin':
				$file = $this->get_plugin_file_for_slug( $meta['software']['slug'] );

				if ( ! $file ) {
					return false;
				}

				$update = $this->upgrader->get_available_plugin_update( $file );

				if ( is_wp_error( $update ) ) {
					return false;
				}
				break;
			case 'theme':
				$update = $this->upgrader->get_available_theme_update( $meta['software']['slug'] );

				if ( is_wp_error( $update ) ) {
					return false;
				}

				break;
			case 'wordpress':
			default:
				return false;
		}

		if ( version_compare( $update['version'], $fixed_in, '<' ) ) {
			return false;
		}

		return true;
	}

	public function fix( Issue $issue ) {
		if ( ! $this->is_fixable( $issue ) ) {
			return new \WP_Error( 'itsec_site_scanner_fixer_not_fixable', __( 'This issue cannot be automatically fixed.', 'better-wp-security' ) );
		}

		$meta = $issue->get_meta();

		switch ( $meta['type'] ) {
			case 'plugin':
				$file = $this->get_plugin_file_for_slug( $meta['software']['slug'] );

				return $this->upgrader->update_plugin( $file );
			case 'theme':
				return $this->upgrader->update_theme( $meta['software']['slug'] );
			default:
				return new \WP_Error( 'itsec_site_scanner_fixer_not_fixable', __( 'This issue cannot be automatically fixed.', 'better-wp-security' ) );
		}
	}

	public function get_fix_label( Issue $issue ) {
		if ( ! $issue instanceof Vulnerability_Issue ) {
			return '';
		}

		$meta = $issue->get_meta();

		switch ( $meta['type'] ) {
			case 'plugin':
				return __( 'Update Plugin', 'better-wp-security' );
			case 'theme':
				return __( 'Update Theme', 'better-wp-security' );
			default:
				return '';
		}
	}

	/**
	 * Get the plugin file for a directory slug.
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	private function get_plugin_file_for_slug( $slug ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		foreach ( $plugins as $file => $_ ) {
			if ( 0 === strpos( $file, $slug . '/' ) ) {
				return $file;
			}
		}

		return '';
	}
}
