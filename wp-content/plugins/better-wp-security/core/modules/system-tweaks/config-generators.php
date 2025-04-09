<?php

final class ITSEC_System_Tweaks_Config_Generators {
	public static function filter_litespeed_server_config_modification( $modification ) {
		return self::filter_apache_server_config_modification( $modification, 'litespeed' );
	}

	public static function filter_apache_server_config_modification( $modification, $server = 'apache' ) {
		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-utility.php' );

		$input = ITSEC_Modules::get_settings( 'system-tweaks' );
		$wp_includes = WPINC;

		if ( $input['protect_files'] ) {
			$files = array(
				'.htaccess',
				'readme.html',
				'readme.txt',
				'wp-config.php',
			);

			$modification .= "\n";
			$modification .= "\t# " . __( 'Protect System Files - Security > Settings > System Tweaks > System Files', 'better-wp-security' ) . "\n";

			foreach ( $files as $file ) {
				$modification .= "\t<files $file>\n";

				if ( 'apache' === $server ) {
					$modification .= "\t\t<IfModule mod_authz_core.c>\n";
					$modification .= "\t\t\tRequire all denied\n";
					$modification .= "\t\t</IfModule>\n";
					$modification .= "\t\t<IfModule !mod_authz_core.c>\n";
					$modification .= "\t\t\tOrder allow,deny\n";
					$modification .= "\t\t\tDeny from all\n";
					$modification .= "\t\t</IfModule>\n";
				} else {
					$modification .= "\t\t<IfModule mod_litespeed.c>\n";
					$modification .= "\t\t\tOrder allow,deny\n";
					$modification .= "\t\t\tDeny from all\n";
					$modification .= "\t\t</IfModule>\n";
				}

				$modification .= "\t</files>\n";
			}
		}

		if ( $input['directory_browsing'] ) {
			$modification .= "\n";
			$modification .= "\t# " . __( 'Disable Directory Browsing - Security > Settings > System Tweaks > Directory Browsing', 'better-wp-security' ) . "\n";
			$modification .= "\tOptions -Indexes\n";
		}

		$rewrites = '';

		if ( $input['protect_files'] ) {
			$rewrites .= "\n";
			$rewrites .= "\t\t# " . __( 'Protect System Files - Security > Settings > System Tweaks > System Files', 'better-wp-security' ) . "\n";
			$rewrites .= "\t\tRewriteRule ^wp-admin/install\.php$ - [F]\n";
			$rewrites .= "\t\tRewriteRule ^wp-admin/includes/ - [F]\n";

			if ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) {
				$rewrites .= "\t\tRewriteRule ^$wp_includes/ms-files.php$ - [S=4]\n";
			}

			$rewrites .= "\t\tRewriteRule !^$wp_includes/ - [S=3]\n";
			$rewrites .= "\t\tRewriteRule ^$wp_includes/[^/]+\.php$ - [F]\n";
			$rewrites .= "\t\tRewriteRule ^$wp_includes/js/tinymce/langs/.+\.php - [F]\n";
			$rewrites .= "\t\tRewriteRule ^$wp_includes/theme-compat/ - [F]\n";

			$hide_dirs = implode( '|', array( 'git', 'svn' ) );
			$rewrites  .= "\t\tRewriteCond %{REQUEST_FILENAME} -f\n";
			$rewrites  .= "\t\tRewriteRule (^|.*/)\.({$hide_dirs})/.* - [F]\n";
		}

		if ( $input['uploads_php'] ) {
			$dir = ITSEC_Lib_Utility::get_relative_upload_url_path();

			if ( ! empty( $dir ) ) {
				$dir = preg_quote( $dir );

				$rewrites .= "\n";
				$rewrites .= "\t\t# " . __( 'Disable PHP in Uploads - Security > Settings > System Tweaks > PHP in Uploads', 'better-wp-security' ) . "\n";
				$rewrites .= "\t\tRewriteRule ^$dir/.*\.(?:php[1-7]?|pht|phtml?|phps)\\.?$ - [NC,F]\n";
			}
		}

		if ( $input['plugins_php'] ) {
			$dir = ITSEC_Lib_Utility::get_relative_url_path( WP_PLUGIN_URL );

			if ( ! empty( $dir ) ) {
				$dir = preg_quote( $dir );

				$rewrites .= "\n";
				$rewrites .= "\t\t# " . __( 'Disable PHP in Plugins - Security > Settings > System Tweaks > PHP in Plugins', 'better-wp-security' ) . "\n";
				$rewrites .= "\t\tRewriteRule ^$dir/.*\.(?:php[1-7]?|pht|phtml?|phps)\\.?$ - [NC,F]\n";
			}
		}

		if ( $input['themes_php'] ) {
			$dir = ITSEC_Lib_Utility::get_relative_url_path( get_theme_root_uri() );

			if ( ! empty( $dir ) ) {
				$dir = preg_quote( $dir );

				$rewrites .= "\n";
				$rewrites .= "\t\t# " . __( 'Disable PHP in Themes - Security > Settings > System Tweaks > PHP in Themes', 'better-wp-security' ) . "\n";
				$rewrites .= "\t\tRewriteRule ^$dir/.*\.(?:php[1-7]?|pht|phtml?|phps)\\.?$ - [NC,F]\n";
			}
		}

		if ( ! empty( $rewrites ) ) {
			$modification .= "\n";
			$modification .= "\t<IfModule mod_rewrite.c>\n";
			$modification .= "\t\tRewriteEngine On\n";
			$modification .= $rewrites;
			$modification .= "\t</IfModule>\n";
		}

		return $modification;
	}

	public static function filter_nginx_server_config_modification( $modification ) {
		require_once( ITSEC_Core::get_core_dir() . '/lib/class-itsec-lib-utility.php' );

		$input = ITSEC_Modules::get_settings( 'system-tweaks' );
		$wp_includes = WPINC;

		if ( $input['protect_files'] ) {
			$config_file = ITSEC_Lib::get_htaccess();

			if ( 0 === strpos( $config_file, ABSPATH ) ) {
				$config_file = '/' . substr( $config_file, strlen( ABSPATH ) );
			} else {
				$config_file = '/nginx.conf';
			}

			$modification .= "\n";
			$modification .= "\t# " . __( 'Protect System Files - Security > Settings > System Tweaks > System Files', 'better-wp-security' ) . "\n";
			$modification .= "\tlocation = /wp-admin/install.php { deny all; }\n";
			$modification .= "\tlocation = $config_file { deny all; }\n";
			$modification .= "\tlocation ~ /\.htaccess$ { deny all; }\n";
			$modification .= "\tlocation ~ /readme\.html$ { deny all; }\n";
			$modification .= "\tlocation ~ /readme\.txt$ { deny all; }\n";
			$modification .= "\tlocation ~ /wp-config.php$ { deny all; }\n";
			$modification .= "\tlocation ~ ^/wp-admin/includes/ { deny all; }\n";

			if ( ! is_multisite() || ! get_site_option( 'ms_files_rewriting' ) ) {
				// nginx can only reliably block PHP files in wp-includes if requests to wp-includes/ms-files.php are
				// not required. This is because there is no skip directive as Apache has.
				$modification .= "\tlocation ~ ^/$wp_includes/[^/]+\.php$ { deny all; }\n";
			}

			$modification .= "\tlocation ~ ^/$wp_includes/js/tinymce/langs/.+\.php$ { deny all; }\n";
			$modification .= "\tlocation ~ ^/$wp_includes/theme-compat/ { deny all; }\n";
			$modification .= "\tlocation ~ ^.*/\.git/.*$ { deny all; }\n";
			$modification .= "\tlocation ~ ^.*/\.svn/.*$ { deny all; }\n";
		}

		// Rewrite Rules for Disable PHP in Uploads
		if ( $input['uploads_php'] ) {
			$dir = ITSEC_Lib_Utility::get_relative_upload_url_path();

			if ( ! empty( $dir ) ) {
				$dir = preg_quote( $dir );

				$modification .= "\n";
				$modification .= "\t# " . __( 'Disable PHP in Uploads - Security > Settings > System Tweaks > PHP in Uploads', 'better-wp-security' ) . "\n";
				$modification .= "\tlocation ~ ^/$dir/.*\.(?:php[1-7]?|pht|phtml?|phps)$ { deny all; }\n";
			}
		}

		// Rewrite Rules for Disable PHP in Plugins
		if ( $input['plugins_php'] ) {
			$dir = ITSEC_Lib_Utility::get_relative_url_path( WP_PLUGIN_URL );

			if ( ! empty( $dir ) ) {
				$dir = preg_quote( $dir );

				$modification .= "\n";
				$modification .= "\t# " . __( 'Disable PHP in Plugins - Security > Settings > System Tweaks > PHP in Plugins', 'better-wp-security' ) . "\n";
				$modification .= "\tlocation ~ ^/$dir/.*\.(?:php[1-7]?|pht|phtml?|phps)$ { deny all; }\n";
			}
		}

		// Rewrite Rules for Disable PHP in Themes
		if ( $input['themes_php'] ) {
			$dir = ITSEC_Lib_Utility::get_relative_url_path( get_theme_root_uri() );

			if ( ! empty( $dir ) ) {
				$dir = preg_quote( $dir );

				$modification .= "\n";
				$modification .= "\t# " . __( 'Disable PHP in Themes - Security > Settings > System Tweaks > PHP in Themes', 'better-wp-security' ) . "\n";
				$modification .= "\tlocation ~ ^/$dir/.*\.(?:php[1-7]?|pht|phtml?|phps)$ { deny all; }\n";
			}
		}

		return $modification;
	}
}
