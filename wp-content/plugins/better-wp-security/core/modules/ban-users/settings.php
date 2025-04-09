<?php

use iThemesSecurity\Ban_Users\Database_Repository;
use iThemesSecurity\Config_Settings;

final class ITSEC_Ban_Users_Settings extends Config_Settings {
	public function get( $name, $default = null ) {
		if ( $name === 'host_list' ) {
			return ITSEC_Modules::get_container()->get( Database_Repository::class )->get_legacy_hosts();
		}

		return parent::get( $name, $default );
	}

	protected function handle_settings_changes( $old_settings ) {
		parent::handle_settings_changes( $old_settings );

		foreach ( $this->settings as $key => $val ) {
			if ( ! isset( $old_settings[ $key ] ) || $old_settings[ $key ] !== $val ) {
				\ITSEC_Response::regenerate_server_config();
				break;
			}
		}
	}
}

ITSEC_Modules::register_settings( new ITSEC_Ban_Users_Settings( ITSEC_Modules::get_config( 'ban-users' ) ) );
