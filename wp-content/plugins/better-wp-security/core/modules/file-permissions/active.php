<?php

use iThemesSecurity\Lib\Tools\Config_Tool;
use iThemesSecurity\Lib\Tools\Tools_Registry;
use \iThemesSecurity\Lib\Result;

add_action( 'itsec_register_tools', function ( Tools_Registry $registry ) {
	$registry->register( new class( 'check-file-permissions', ITSEC_Modules::get_config( 'file-permissions' ) ) extends Config_Tool {
		public function run( array $form = [] ): Result {
			$wp_upload_dir = ITSEC_Core::get_wp_upload_dir();

			$paths = [
				ABSPATH                                              => 0755,
				ABSPATH . WPINC                                      => 0755,
				ABSPATH . 'wp-admin'                                 => 0755,
				ABSPATH . 'wp-admin/js'                              => 0755,
				WP_CONTENT_DIR                                       => 0755,
				get_theme_root()                                     => 0755,
				WP_PLUGIN_DIR                                        => 0755,
				$wp_upload_dir['basedir']                            => 0755,
				ITSEC_Lib_Config_File::get_wp_config_file_path()     => 0444,
				ITSEC_Lib_Config_File::get_server_config_file_path() => 0444,
			];

			$data = [];

			foreach ( $paths as $path => $suggested ) {
				$display_path = preg_replace( '/^' . preg_quote( ABSPATH, '/' ) . '/', '', $path );
				$display_path = ltrim( $display_path, '/' );

				if ( empty( $display_path ) ) {
					$display_path = '/';
				}

				$data[ $path ] = [
					'path'      => $display_path,
					'suggested' => sprintf( '%o', $suggested ),
					'actual'    => sprintf( '%o', fileperms( $path ) & 0777 ),
				];
			}

			return Result::success( $data );
		}
	} );
} );
