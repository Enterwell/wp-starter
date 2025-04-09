<?php

class ITSEC_Security_Check_Pro_Setup {
	public function __construct() {
		add_action( 'itsec_modules_do_plugin_upgrade', [ $this, 'execute_upgrade' ] );
	}

	public function execute_upgrade( $old_version ) {
		if ( $old_version < 4126 && ITSEC_Modules::is_active( 'security-check-pro' ) ) {
			ITSEC_Security_Check_Pro_Utility::get_server_response();
		}
	}
}

new ITSEC_Security_Check_Pro_Setup();
