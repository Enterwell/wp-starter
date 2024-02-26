<?php

namespace EwStarter\Main;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    EWPlugin
 * @subpackage EWPlugin/includes
 * @author     Enterwell <info@enterwell.net>
 */
class Plugin_Activator {
	/**
	 * Database version
	 * Should be updated every time we modify the database
	 */
	const DB_VERSION = '0.0.1';

	/**
	 * Database version option name
	 */
	const DB_VERSION_OPTION_NAME = '_ew_plugin_db_version';


	/**
	 * Function that is called on plugin activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$version = get_option( static::DB_VERSION_OPTION_NAME, false );

		// If version is same do not create any tables.
		if ( $version == static::DB_VERSION ) {
			return;
		}

		// Get queries
		$queries = static::get_queries();

		// Execute queries
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $queries );

		// Update the version option in database
		update_option( static::DB_VERSION_OPTION_NAME, static::DB_VERSION );
	}

	/**
	 * Gets all queries to create on db.
	 */
	private static function get_queries() {
		global $wpdb;
		$wp_prefix       = $wpdb->prefix;
		$charset_collate = $wpdb->get_charset_collate();

		// Get table names.
		$user_applications_table = "{$wp_prefix}user_applications";

		return [
			"CREATE TABLE {$user_applications_table} (
				id BIGINT UNSIGNED NOT NULL auto_increment,
				date_created VARCHAR(128) NOT NULL,
				first_name VARCHAR(256),
				last_name VARCHAR(256),
				email VARCHAR(256),
				phone VARCHAR(256),
				street_and_number VARCHAR(256),
				city VARCHAR(256),
				postal_code VARCHAR(64),
				invoice_file TEXT,
			    PRIMARY KEY  id (id)
			) {$charset_collate};"
		];
	}
}
