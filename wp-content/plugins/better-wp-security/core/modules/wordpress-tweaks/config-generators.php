<?php

final class ITSEC_WordPress_Tweaks_Config_Generators {
	public static function filter_wp_config_modification( $modification ) {
		$input = ITSEC_Modules::get_settings( 'wordpress-tweaks' );

		if ( $input['file_editor'] ) {
			$modification .= "define( 'DISALLOW_FILE_EDIT', true ); // " . __( 'Disable File Editor - Security > Settings > WordPress Tweaks > File Editor', 'better-wp-security' ) . "\n";
		}

		return $modification;
	}

	public static function filter_litespeed_server_config_modification( $modification ) {
		return self::filter_apache_server_config_modification( $modification, 'litespeed' );
	}

	public static function filter_apache_server_config_modification( $modification, $server = 'apache' ) {
		$input = ITSEC_Modules::get_settings( 'wordpress-tweaks' );

		if ( 'disable' === $input['disable_xmlrpc'] ) {
			$modification .= "\n";
			$modification .= "\t# " . __( 'Disable XML-RPC - Security > Settings > WordPress Tweaks > XML-RPC', 'better-wp-security' ) . "\n";
			$modification .= "\t<files xmlrpc.php>\n";

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

		return $modification;
	}

	public static function filter_nginx_server_config_modification( $modification ) {
		$input = ITSEC_Modules::get_settings( 'wordpress-tweaks' );

		if ( 'disable' === $input['disable_xmlrpc'] ) {
			$modification .= "\n";
			$modification .= "\t# " . __( 'Disable XML-RPC - Security > Settings > WordPress Tweaks > XML-RPC', 'better-wp-security' ) . "\n";
			$modification .= "\tlocation ~ xmlrpc.php { deny all; }\n";
		}

		return $modification;
	}
}
