<?php

final class ITSEC_System_Tweaks {
	private static $instance = false;

	private $hooks_added = false;


	private function __construct() {
		$this->add_hooks();
	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function activate() {
		$self = self::get_instance();

		$self->add_hooks();
		ITSEC_Response::regenerate_server_config();
	}

	public static function deactivate() {
		$self = self::get_instance();

		$self->remove_hooks();
		ITSEC_Response::regenerate_server_config();
	}

	public function add_hooks() {
		if ( ITSEC_Core::is_temp_disable_modules_set() ) {
			return;
		}

		if ( $this->hooks_added ) {
			return;
		}

		add_filter( 'itsec_filter_apache_server_config_modification', array( $this, 'filter_apache_server_config_modification' ) );
		add_filter( 'itsec_filter_nginx_server_config_modification', array( $this, 'filter_nginx_server_config_modification' ) );
		add_filter( 'itsec_filter_litespeed_server_config_modification', array( $this, 'filter_litespeed_server_config_modification' ) );

		$this->hooks_added = true;
	}

	public function remove_hooks() {
		remove_filter( 'itsec_filter_apache_server_config_modification', array( $this, 'filter_apache_server_config_modification' ) );
		remove_filter( 'itsec_filter_nginx_server_config_modification', array( $this, 'filter_nginx_server_config_modification' ) );
		remove_filter( 'itsec_filter_litespeed_server_config_modification', array( $this, 'filter_litespeed_server_config_modification' ) );

		$this->hooks_added = false;
	}

	public function filter_apache_server_config_modification( $modification ) {
		require_once( dirname( __FILE__ ) . '/config-generators.php' );

		return ITSEC_System_Tweaks_Config_Generators::filter_apache_server_config_modification( $modification );
	}

	public function filter_nginx_server_config_modification( $modification ) {
		require_once( dirname( __FILE__ ) . '/config-generators.php' );

		return ITSEC_System_Tweaks_Config_Generators::filter_nginx_server_config_modification( $modification );
	}

	public function filter_litespeed_server_config_modification( $modification ) {
		require_once( dirname( __FILE__ ) . '/config-generators.php' );

		return ITSEC_System_Tweaks_Config_Generators::filter_litespeed_server_config_modification( $modification );
	}
}


ITSEC_System_Tweaks::get_instance();
