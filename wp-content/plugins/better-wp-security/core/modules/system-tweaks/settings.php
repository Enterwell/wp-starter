<?php

use iThemesSecurity\Config_Settings;

final class ITSEC_System_Tweaks_Settings extends Config_Settings {
	protected function handle_settings_changes( $old_settings ) {
		parent::handle_settings_changes( $old_settings );

		if ( array_diff_assoc( $this->settings, $old_settings ) ) {
			ITSEC_Response::regenerate_server_config();
		}
	}
}

ITSEC_Modules::register_settings( new ITSEC_System_Tweaks_Settings( ITSEC_Modules::get_config( 'system-tweaks' ) ) );
