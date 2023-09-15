<?php

use iThemesSecurity\Config_Settings;

final class ITSEC_Wordpress_Tweaks_Settings extends Config_Settings {
	protected function handle_settings_changes( $old_settings ) {
		parent::handle_settings_changes( $old_settings );

		if ( $this->settings['file_editor'] !== $old_settings['file_editor'] ) {
			ITSEC_Response::regenerate_wp_config();
		}

		if ( $this->settings['disable_xmlrpc'] !== $old_settings['disable_xmlrpc'] ) {
			ITSEC_Response::regenerate_server_config();
		}
	}
}

ITSEC_Modules::register_settings( new ITSEC_WordPress_Tweaks_Settings( ITSEC_Modules::get_config( 'wordpress-tweaks' ) ) );
