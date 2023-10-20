<?php

final class ITSEC_Schema {
	const TABLES = [
		'itsec_logs',
		'itsec_lockouts',
		'itsec_temp',
		'itsec_distributed_storage',
		'itsec_geolocation_cache',
		'itsec_fingerprints',
		'itsec_opaque_tokens',
		'itsec_user_groups',
		'itsec_mutexes',
		'itsec_bans',
		'itsec_dashboard_events',
		'itsec_dashboard_lockouts',
		'itsec_vulnerabilities',
		'itsec_firewall_rules',
	];

	const PRO_TABLES = [
		'itsec_webauthn_users',
		'itsec_webauthn_credentials',
	];

	public static function get_table_names(): array {
		if ( ITSEC_Core::is_pro() ) {
			return array_merge( self::TABLES, self::PRO_TABLES );
		} else {
			return self::TABLES;
		}
	}

	/**
	 * Creates appropriate database tables.
	 *
	 * Uses dbdelta to create database tables either on activation or in the event that one is missing.
	 *
	 * @since 3.9.0
	 *
	 * @return true|WP_Error
	 */
	public static function create_database_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
CREATE TABLE {$wpdb->base_prefix}itsec_logs (
	id bigint(20) unsigned NOT NULL auto_increment,
	parent_id bigint(20) unsigned NOT NULL default '0',
	module varchar(50) NOT NULL default '',
	code varchar(100) NOT NULL default '',
	data longtext NOT NULL,
	type varchar(20) NOT NULL default 'notice',
	timestamp datetime NOT NULL default '0000-00-00 00:00:00',
	init_timestamp datetime NOT NULL default '0000-00-00 00:00:00',
	memory_current bigint(20) unsigned NOT NULL default '0',
	memory_peak bigint(20) unsigned NOT NULL default '0',
	url varchar(500) NOT NULL default '',
	blog_id bigint(20) NOT NULL default '0',
	user_id bigint(20) unsigned NOT NULL default '0',
	remote_ip varchar(50) NOT NULL default '',
	PRIMARY KEY  (id),
	KEY module (module),
	KEY code (code),
	KEY type (type),
	KEY timestamp (timestamp),
	KEY init_timestamp (init_timestamp),
	KEY user_id (user_id),
	KEY blog_id (blog_id)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_lockouts (
	lockout_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	lockout_type varchar(25) NOT NULL,
	lockout_start datetime NOT NULL,
	lockout_start_gmt datetime NOT NULL,
	lockout_expire datetime NOT NULL,
	lockout_expire_gmt datetime NOT NULL,
	lockout_host varchar(40),
	lockout_user bigint(20) UNSIGNED,
	lockout_username varchar(60),
	lockout_active int(1) NOT NULL DEFAULT 1,
	lockout_context TEXT,
	PRIMARY KEY  (lockout_id),
	KEY lockout_expire_gmt (lockout_expire_gmt),
	KEY lockout_host (lockout_host),
	KEY lockout_user (lockout_user),
	KEY lockout_username (lockout_username),
	KEY lockout_active (lockout_active)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_temp (
	temp_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	temp_type varchar(25) NOT NULL,
	temp_date datetime NOT NULL,
	temp_date_gmt datetime NOT NULL,
	temp_host varchar(40),
	temp_user bigint(20) UNSIGNED,
	temp_username varchar(60),
	PRIMARY KEY  (temp_id),
	KEY temp_date_gmt (temp_date_gmt),
	KEY temp_host (temp_host),
	KEY temp_user (temp_user),
	KEY temp_username (temp_username)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_distributed_storage (
	storage_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	storage_group varchar(40) NOT NULL,
	storage_key varchar(40) NOT NULL default '',
	storage_chunk int NOT NULL default 0,
	storage_data longtext NOT NULL,
	storage_updated datetime NOT NULL,
	PRIMARY KEY  (storage_id),
	UNIQUE KEY storage_group__key__chunk (storage_group,storage_key,storage_chunk)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_geolocation_cache (
	location_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	location_host varchar(40) NOT NULL,
	location_lat decimal(10,8) NOT NULL,
	location_long decimal(11,8) NOT NULL,
	location_label varchar(255) NOT NULL,
	location_credit varchar (255) NOT NULL,
	location_meta text NOT NULL,
	location_time datetime NOT NULL,
	PRIMARY KEY  (location_id),
	UNIQUE KEY location_host (location_host),
	KEY location_time (location_time)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_fingerprints (
	fingerprint_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	fingerprint_user bigint(20) UNSIGNED NOT NULL,
	fingerprint_hash char(32) NOT NULL,
	fingerprint_created_at datetime NOT NULL,
	fingerprint_approved_at datetime NOT NULL,
	fingerprint_data longtext NOT NULL,
	fingerprint_snapshot longtext NOT NULL,
	fingerprint_last_seen datetime NOT NULL,
	fingerprint_uses int NOT NULL default 0,
	fingerprint_status varchar(20) NOT NULL,
	fingerprint_uuid char(36) NOT NULL,
	PRIMARY KEY  (fingerprint_id),
	UNIQUE KEY fingerprint_user__hash (fingerprint_user,fingerprint_hash),
	UNIQUE KEY fingerprint_uuid (fingerprint_uuid)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_opaque_tokens (
	token_id char(64) NOT NULL,
	token_hashed char(64) NOT NULL,
	token_type VARCHAR(32) NOT NULL,
	token_data TEXT NOT NULL,
	token_created_at DATETIME NOT NULL,
	PRIMARY KEY  (token_id),
	UNIQUE KEY token_hashed (token_hashed)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_user_groups (
	group_id char(36) NOT NULL,
	group_label varchar(255) NOT NULL default '',
	group_roles TEXT,
	group_canonical TEXT,
	group_users TEXT,
	group_min_role varchar(255),
	group_created_at DATETIME,
	PRIMARY KEY  (group_id)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_mutexes (
	mutex_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	mutex_name varchar(100) NOT NULL,
	mutex_expires int(11) UNSIGNED NOT NULL,
	PRIMARY KEY  (mutex_id),
	UNIQUE KEY mutex_name (mutex_name)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_bans (
	id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	host varchar(64) NOT NULL,
	type varchar(20) NOT NULL default 'ip',
	created_at datetime NOT NULL,
	actor_type varchar(20),
	actor_id varchar(128),
	comment varchar(255) NOT NULL default '',
	PRIMARY KEY  (id),
	UNIQUE KEY host (host),
	KEY actor (actor_type,actor_id)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_dashboard_events (
	event_id int(11) unsigned NOT NULL AUTO_INCREMENT,
	event_slug varchar(128) NOT NULL DEFAULT '',
	event_time datetime NOT NULL,
	event_count int(11) unsigned NOT NULL DEFAULT '1',
	event_consolidated tinyint(1) NOT NULL DEFAULT '0',
	PRIMARY KEY  (`event_id`),
	UNIQUE KEY `event_slug__time__consolidated` (event_slug,event_time,event_consolidated)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_dashboard_lockouts (
	id int(11) unsigned NOT NULL AUTO_INCREMENT,
	ip varchar(40),
	time datetime NOT NULL,
	count int(11) unsigned NOT NULL,
	PRIMARY KEY  (`id`),
	UNIQUE KEY `ip__time` (`ip`, `time`)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_vulnerabilities (
	id varchar(128) NOT NULL,
	software_type varchar(20) NOT NULL,
	software_slug varchar(255) NOT NULL,
	first_seen datetime NOT NULL,
	last_seen datetime NOT NULL,
	resolved_at datetime default NULL,
	resolved_by bigint(20) unsigned NOT NULL default 0,
	resolution varchar(20) NOT NULL default '',
	details text NOT NULL,
	PRIMARY KEY  (`id`),
	KEY `resolution` (`resolution`),
	KEY `software_type` (`software_type`),
	KEY `last_seen` (`last_seen`)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_firewall_rules (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	provider varchar(20) NOT NULL,
	provider_ref varchar(128) NOT NULL,
	name varchar(255) NOT NULL,
	vulnerability varchar(128) NOT NULL,
	config text NOT NULL,
	created_at datetime NOT NULL,
	paused_at datetime default NULL,
	PRIMARY KEY  (`id`),
	KEY `provider__ref` (`provider`, `provider_ref`),
	KEY `vulnerability` (`vulnerability`),
	KEY `paused_at` (`paused_at`)
) $charset_collate;
";

		if ( ITSEC_Core::is_pro() ) {
			$sql .= "
CREATE TABLE {$wpdb->base_prefix}itsec_webauthn_users (
	user_id bigint(20) unsigned NOT NULL,
	webauthn_id CHAR(44) NOT NULL,
	PRIMARY KEY  (`user_id`)
) $charset_collate;

CREATE TABLE {$wpdb->base_prefix}itsec_webauthn_credentials (
	ref char(64) CHARACTER SET latin1 COLLATE latin1_bin,
	id varchar(1024) NOT NULL,
	type varchar(40) NOT NULL,
	transports text NOT NULL,
	public_key text NOT NULL,
	sign_count bigint unsigned NOT NULL,
	backup_eligible tinyint(1) unsigned NOT NULL,
	backed_up tinyint(1) unsigned NOT NULL,
	webauthn_user char(44) NOT NULL,
	created_at datetime NOT NULL,
	last_used datetime default NULL,
	trashed_at datetime default NULL,
	label varchar(255) NOT NULL,
	status varchar(20) NOT NULL,
	PRIMARY KEY  (`ref`),
	KEY `webauthn_user__status` (`webauthn_user`, `status`)
) $charset_collate;
";
		}

		$wp_error = new WP_Error();
		ITSEC_Lib::add_to_wp_error( $wp_error, ITSEC_Lib::db_delta_with_error_handling( $sql ) );

		foreach ( self::get_table_names() as $table ) {
			if ( ! count( $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->base_prefix}{$table}'" ) ) ) {
				$wp_error->add(
					'missing_table',
					sprintf( __( 'The %s table is not installed.', 'better-wp-security' ), $table )
				);
			}
		}

		/**
		 * Fires when the DB schema is installed or updated.
		 *
		 * @param WP_Error $wp_error
		 */
		do_action( 'itsec_install_schema', $wp_error );

		if ( $wp_error->has_errors() ) {
			return $wp_error;
		}

		return true;
	}

	public static function remove_database_tables() {
		global $wpdb;

		foreach ( self::get_table_names() as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}{$table};" );
		}

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->base_prefix}itsec_log;" );
	}
}
