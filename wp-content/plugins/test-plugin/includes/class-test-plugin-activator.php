<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    TestPlugin
 * @subpackage TestPlugin/includes
 * @author     Enterwell <info@enterwell.net>
 */
class TestPluginActivator {
	/**
	 * Database version
	 * Should be updated every time we modify the database
	 */
	const DB_VERSION = '0.0.3.';

	/**
	 * Database version option name
	 */
	const DB_VERSION_OPTION_NAME = '_ew_db_version';


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
		$events_table    = "{$wp_prefix}ew_events";
		$locations_table = "{$wp_prefix}ew_locations";

		return [
			"CREATE TABLE {$events_table} (
				id BIGINT UNSIGNED NOT NULL,				
				start_date VARCHAR(64),
				end_date VARCHAR(64),
			    PRIMARY KEY  id (id)
			) {$charset_collate};",

			"CREATE TABLE {$locations_table} (
				id BIGINT UNSIGNED NOT NULL auto_increment,
				location_name VARCHAR(128),
			    PRIMARY KEY  id (id)
			) {$charset_collate};",
		];
	}
}
