<?php

namespace EwStarter\Main;

use EwStarter\Main\Interfaces\Plugin_Activator_Interface;

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
class Plugin_Activator implements Plugin_Activator_Interface {
	/**
	 * DB version.
	 *
	 * @var string
	 */
	protected string $db_version;

	/**
	 *  DB version option name.
	 *
	 * @var string
	 */
	protected string $db_version_option_name;


	public function __construct( string $db_version, string $db_version_name ) {
		$this->db_version             = $db_version;
		$this->db_version_option_name = $db_version_name;

	}

	/**
	 * Function that is called on plugin activation.
	 *
	 * @since    1.0.0
	 */
	public function activate(): void {
		// Check if dependent plugins are activated
		$version = get_option( $this->db_version_option_name, false );

		// If version is same do not create any tables.
		if ( $version == $this->db_version ) {
			return;
		}

		// Get queries
		$queries = static::get_queries();

		// Execute queries
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $queries );

		// Update the version option in database
		update_option( $this->db_version_option_name, $this->db_version );
	}

	/**
	 * Gets all queries to create on db.
	 */
	private function get_queries() {
		global $wpdb;
		$wp_prefix       = $wpdb->prefix;
		$charset_collate = $wpdb->get_charset_collate();

		// Get table names.
		$user_applications_table = "{$wp_prefix}user_applications";

		/** @noinspection SqlNoDataSourceInspection */
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
