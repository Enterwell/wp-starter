<?php

class ITSEC_Salts_Setup {

	public function __construct() {
		add_action( 'itsec_modules_do_plugin_uninstall', [ $this, 'execute_uninstall' ] );
		add_action( 'itsec_modules_do_plugin_upgrade', [ $this, 'execute_upgrade' ] );
	}

	/**
	 * Execute module uninstall
	 *
	 * @since 4.7.0
	 *
	 * @return void
	 */
	public function execute_uninstall() {
		delete_site_option( 'itsec_salts' );
	}

	/**
	 * Execute module upgrade
	 *
	 * @param int $itsec_old_version
	 */
	public function execute_upgrade( $itsec_old_version ) {
		if ( $itsec_old_version < 4041 ) {
			$last_generated = get_site_option( 'itsec_salts' );

			if ( is_int( $last_generated ) && $last_generated >= 0 ) {
				ITSEC_Modules::set_setting( 'wordpress-salts', 'last_generated', $last_generated );
			}
		}
	}
}

new ITSEC_Salts_Setup();
