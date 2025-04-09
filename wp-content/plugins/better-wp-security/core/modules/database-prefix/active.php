<?php

use iThemesSecurity\Lib\Tools\Tools_Registry;
use iThemesSecurity\Lib\Result;
use iThemesSecurity\Lib\Tools\Config_Tool;

add_action( 'itsec_register_tools', function ( Tools_Registry $registry ) {
	$registry->register( new class ( 'change-database-prefix', ITSEC_Modules::get_config( 'database-prefix' ) ) extends Config_Tool {
		public function run( array $form = [] ): Result {
			global $wpdb;

			// Generate a new table prefix that doesn't conflict with any other in use in the database
			do {
				$avail = 'abcdefghijklmnopqrstuvwxyz0123456789';

				// First character should be alpha
				$new_prefix = $avail[ mt_rand( 0, 25 ) ];

				// Length of new prefix
				$prelength = mt_rand( 4, 9 );

				// Generate remaining characters
				for ( $i = 0; $i < $prelength; $i ++ ) {
					$new_prefix .= $avail[ mt_rand( 0, 35 ) ];
				}

				// Complete with underscore
				$new_prefix .= '_';

				// If there are no tables with that prefix in the database set checkPrefix to false
				$prefix_exists = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s;', $new_prefix . '%' ), ARRAY_N );
			} while ( $prefix_exists );

			$config_file_path = ITSEC_Lib_Config_File::get_wp_config_file_path();
			$config           = ITSEC_Lib_File::read( $config_file_path );

			if ( is_wp_error( $config ) ) {
				return Result::error( new WP_Error(
					$config->get_error_code(),
					/* translators: 1: Specific error details */
					sprintf( __( 'Unable to read the <code>wp-config.php</code> file in order to update the Database Prefix. %s', 'better-wp-security' ), $config->get_error_message() )
				) );
			}

			$regex  = '/(\$table_prefix\s*=\s*)([\'"]).+?\\2(\s*;)/';
			$config = preg_replace( $regex, "\${1}'$new_prefix'\${3}", $config );

			$write_result = ITSEC_Lib_File::write( $config_file_path, $config );

			if ( is_wp_error( $write_result ) ) {
				return Result::error( new WP_Error(
					$write_result->get_error_code(),
					/* translators: 1: Specific error details */
					sprintf( __( 'Unable to update the <code>wp-config.php</code> file in order to update the Database Prefix. %s', 'better-wp-security' ), $write_result->get_error_message() )
				) );
			}

			$result = Result::success( $new_prefix );
			/* translators: New database prefix. */
			$result->add_success_message( sprintf( __( 'Database prefix changed to %s.', 'better-wp-security' ), $new_prefix ) );

			// Retrieve a list of all tables in the DB
			$tables = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->base_prefix . '%' ), ARRAY_N );

			// Rename each table
			foreach ( $tables as $table ) {
				$table = substr( $table[0], strlen( $wpdb->base_prefix ), strlen( $table[0] ) ); //Get the table name without the old prefix

				// Rename the table and generate an error if there is a problem
				if ( $wpdb->query( 'RENAME TABLE `' . $wpdb->base_prefix . $table . '` TO `' . $new_prefix . $table . '`;' ) === false ) {
					$result->add_warning_message( sprintf( __( 'Could not rename table %1$s. You may have to rename the table manually.', 'better-wp-security' ), $wpdb->base_prefix . $table ) );
				}
			}

			// Multisite requires us to rename each blogs' options
			if ( is_multisite() ) {
				$blogs = $wpdb->get_col( "SELECT blog_id FROM `" . $new_prefix . "blogs` WHERE public = '1' AND archived = '0' AND mature = '0' AND spam = '0' ORDER BY blog_id DESC" ); //get list of blog id's

				// Make sure there are other blogs to update
				if ( is_array( $blogs ) ) {
					// Update each blog's user_roles option
					foreach ( $blogs as $blog ) {
						$wpdb->query( 'UPDATE `' . $new_prefix . $blog . '_options` SET option_name = "' . $new_prefix . $blog . '_user_roles" WHERE option_name = "' . $wpdb->base_prefix . $blog . '_user_roles" LIMIT 1;' );
					}
				}
			}

			// Update options table and set flag to false if there's an error
			$updated_options = $wpdb->query( 'UPDATE `' . $new_prefix . 'options` SET option_name = "' . $new_prefix . 'user_roles" WHERE option_name = "' . $wpdb->base_prefix . 'user_roles" LIMIT 1;' );

			if ( $updated_options === false ) {
				$result->add_warning_message( __( 'Could not update prefix references in options table.', 'better-wp-security' ) );
			}

			// Get all rows in usermeta
			$rows = $wpdb->get_results( "SELECT * FROM `{$new_prefix}usermeta`" );

			// Update all prefixes in usermeta
			foreach ( $rows as $row ) {
				if ( 0 !== strpos( $row->meta_key, $wpdb->base_prefix ) ) {
					continue;
				}

				$pos     = $new_prefix . substr( $row->meta_key, strlen( $wpdb->base_prefix ), strlen( $row->meta_key ) );
				$updated = $wpdb->query( $wpdb->prepare( "UPDATE `{$new_prefix}usermeta` SET meta_key = %s WHERE meta_key = %s LIMIT 1", $pos, $row->meta_key ) );

				if ( ! $updated ) {
					$result->add_warning_message( __( 'Could not update prefix references in usermeta table.', 'better-wp-security' ) );
				}
			}

			return $result;
		}
	} );
} );
