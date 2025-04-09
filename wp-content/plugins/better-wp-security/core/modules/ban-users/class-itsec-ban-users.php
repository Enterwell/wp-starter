<?php

class ITSEC_Ban_Users {
	private static $instance = false;

	private $hooks_added = false;


	private function __construct() {
		$this->init();
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
		if ( $this->hooks_added ) {
			return;
		}

		add_filter( 'itsec_filter_apache_server_config_modification', array( $this, 'filter_apache_server_config_modification' ) );
		add_filter( 'itsec_filter_nginx_server_config_modification', array( $this, 'filter_nginx_server_config_modification' ) );
		add_filter( 'itsec_filter_litespeed_server_config_modification', array( $this, 'filter_litespeed_server_config_modification' ) );
		add_filter( 'itsec_rest_prepare_ban_for_response', [ $this, 'prepare_ban_for_response' ], 10, 2 );

		$this->hooks_added = true;
	}

	public function remove_hooks() {
		remove_filter( 'itsec_filter_apache_server_config_modification', array( $this, 'filter_apache_server_config_modification' ) );
		remove_filter( 'itsec_filter_nginx_server_config_modification', array( $this, 'filter_nginx_server_config_modification' ) );
		remove_filter( 'itsec_filter_litespeed_server_config_modification', array( $this, 'filter_litespeed_server_config_modification' ) );
		remove_filter( 'itsec_rest_prepare_ban_for_response', [ $this, 'prepare_ban_for_response' ] );

		$this->hooks_added = false;
	}

	public function init() {
		$this->add_hooks();
	}

	public function filter_blacklisted_ips( $blacklisted_ips ) {
		_deprecated_function( __METHOD__, '6.7.0' );

		if ( ITSEC_Modules::get_setting( 'ban-users', 'enable_ban_lists' ) ) {
			$blacklisted_ips = array_merge( $blacklisted_ips, ITSEC_Modules::get_setting( 'ban-users', 'host_list', array() ) );
		}

		return $blacklisted_ips;
	}

	public function filter_apache_server_config_modification( $modification ) {
		require_once( dirname( __FILE__ ) . '/config-generators.php' );

		if ( ITSEC_Modules::get_setting( 'ban-users', 'default' ) ) {
			$modification .= ITSEC_Ban_Users_Config_Generators::get_server_config_default_blacklist_rules( 'apache' );
		}

		if ( ITSEC_Modules::get_setting( 'ban-users', 'enable_ban_lists' ) ) {
			$modification .= ITSEC_Ban_Users_Config_Generators::get_server_config_ban_hosts_rules( 'apache' );
			$modification .= ITSEC_Ban_Users_Config_Generators::get_server_config_ban_user_agents_rules( 'apache' );
		}

		return $modification;
	}

	public function filter_nginx_server_config_modification( $modification ) {
		require_once( dirname( __FILE__ ) . '/config-generators.php' );

		if ( ITSEC_Modules::get_setting( 'ban-users', 'default' ) ) {
			$modification .= ITSEC_Ban_Users_Config_Generators::get_server_config_default_blacklist_rules( 'nginx' );
		}

		if ( ITSEC_Modules::get_setting( 'ban-users', 'enable_ban_lists' ) ) {
			$modification .= ITSEC_Ban_Users_Config_Generators::get_server_config_ban_hosts_rules( 'nginx' );
			$modification .= ITSEC_Ban_Users_Config_Generators::get_server_config_ban_user_agents_rules( 'nginx' );
		}

		return $modification;
	}

	public function filter_litespeed_server_config_modification( $modification ) {
		require_once( dirname( __FILE__ ) . '/config-generators.php' );

		if ( ITSEC_Modules::get_setting( 'ban-users', 'default' ) ) {
			$modification .= ITSEC_Ban_Users_Config_Generators::get_server_config_default_blacklist_rules( 'litespeed' );
		}

		if ( ITSEC_Modules::get_setting( 'ban-users', 'enable_ban_lists' ) ) {
			$modification .= ITSEC_Ban_Users_Config_Generators::get_server_config_ban_hosts_rules( 'litespeed' );
			$modification .= ITSEC_Ban_Users_Config_Generators::get_server_config_ban_user_agents_rules( 'litespeed' );
		}

		return $modification;
	}

	/**
	 * Modifies the ban response to include links to the logs page.
	 *
	 * @param WP_REST_Response                          $response
	 * @param \iThemesSecurity\Ban_Hosts\Repository_Ban $ban
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_ban_for_response( $response, $ban ) {
		if ( ! $ban instanceof \iThemesSecurity\Ban_Users\Ban ) {
			return $response;
		}

		if ( ITSEC_Modules::get_setting( 'global', 'log_type' ) === 'file' ) {
			return $response;
		}

		if ( ITSEC_Lib_IP_Tools::is_range( $ban->get_host() ) ) {
			return $response;
		}

		if ( ITSEC_Core::current_user_can_manage() ) {
			$response->add_link( 'related', ITSEC_Core::get_logs_page_url( [ 'remote_ip' => $ban->get_host(), 'type' => 'all' ] ), [
				'media' => 'text/html',
				'title' => __( 'View Activity', 'better-wp-security' ),
			] );
		}

		return $response;
	}
}


ITSEC_Ban_Users::get_instance();
