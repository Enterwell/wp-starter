<?php

/**
 * iThemes file handler.
 *
 * Writes to core files including wp-config.php, htaccess and nginx.conf.
 *
 * @package iThemes_Security
 *
 * @since   4.0.0
 */
final class ITSEC_Files {
	static $instance = false;

	private function __construct() {}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Check the setting that allows writing files.
	 *
	 * @since 1.15.0
	 *
	 * @return bool True if files can be written to, false otherwise.
	 */
	public static function can_write_to_files() {
		$can_write = (bool) ITSEC_Modules::get_setting( 'global', 'write_files' );
		$can_write = apply_filters( 'itsec_filter_can_write_to_files', $can_write );

		return $can_write;
	}

	public static function regenerate_wp_config( $add_responses = true ) {
		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-config-file.php' );

		$result = ITSEC_Lib_Config_File::update_wp_config();
		$success = ! is_wp_error( $result );

		if ( $add_responses && is_wp_error( $result ) ) {
			ITSEC_Response::add_error( $result );
		}

		return $success;
	}

	public static function regenerate_server_config( $add_responses = true ) {
		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-config-file.php' );

		$result = ITSEC_Lib_Config_File::update_server_config();
		$success = ! is_wp_error( $result );
		$server = ITSEC_Lib_Utility::get_web_server();

		if ( $add_responses ) {
			if ( is_wp_error( $result ) ) {
				ITSEC_Response::add_error( $result );

				$file = ITSEC_Lib_Config_File::get_server_config_file_path();
			} else if ( 'nginx' === $server ) {
				ITSEC_Response::add_message( __( 'You must restart your NGINX server for the changes to take effect.', 'better-wp-security' ) );
			}
		}

		return $success;
	}

	/**
	 * Flush files to the filesystem on a schedule.
	 *
	 * @param ITSEC_Job $job
	 */
	public static function flush_files( ITSEC_Job $job ) {
		self::regenerate_server_config( false );
	}

	/**
	 * Execute activation functions.
	 *
	 * Writes necessary information to wp-config and .htaccess upon plugin activation.
	 *
	 * @since  4.0.0
	 *
	 * @return void
	 */
	public function do_activate() {
		self::regenerate_wp_config( false );
		self::regenerate_server_config( false );
	}

	/**
	 * Execute deactivation functions.
	 *
	 * Writes necessary information to wp-config and .htaccess upon plugin deactivation.
	 *
	 * @since  4.0.0
	 *
	 * @return void
	 */
	public function do_deactivate() {
		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-config-file.php' );

		ITSEC_Lib_Config_File::reset_wp_config();
		ITSEC_Lib_Config_File::reset_server_config();
	}
}
