<?php

final class ITSEC_Backup_Settings extends \iThemesSecurity\Config_Settings {
	public function get_default( $setting, $default = null ) {
		if ( 'location' === $setting ) {
			return ITSEC_Core::get_storage_dir( 'backups' );
		}

		return parent::get_default( $setting, $default );
	}

	public function get_settings_schema() {
		$schema = parent::get_settings_schema();

		$schema['properties']['exclude']['items']['enum'] = array_values( array_unique( array_merge(
			$this->get( 'exclude', [] ),
			$this->get_excludable_tables()
		) ) );

		return $schema;
	}

	protected function get_excludable_tables(): array {
		global $wpdb;

		$ignore = [
			'posts',
			'comments',
			'links',
			'options',
			'postmeta',
			'terms',
			'term_taxonomy',
			'term_relationships',
			'termmeta',
			'commentmeta',
			'categories',
			'post2cat',
			'link2cat',
			'users',
			'usermeta',
			'blogs',
			'blogmeta',
			'signups',
			'site',
			'sitemeta',
			'sitecategories',
			'registration_log',
		];

		$tables = $wpdb->get_col( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->base_prefix . '%' ) );

		if ( ! is_multisite() ) {
			$clean = array_map( static function ( $table ) use ( $wpdb ) {
				return substr( $table, strlen( $wpdb->base_prefix ) );
			}, $tables );
		} else {
			$clean = array_map( static function ( $table ) use ( $wpdb ) {
				return preg_replace( "/^{$wpdb->base_prefix}(\d)*_/", '', $table );
			}, $tables );
		}

		return array_values( array_diff( array_unique( $clean ), $ignore ) );
	}
}

ITSEC_Modules::register_settings( new ITSEC_Backup_Settings( ITSEC_Modules::get_config( 'backup' ) ) );
