<?php

class ITSEC_SSL {
	private static $instance = false;

	private $config_hooks_added = false;
	private $http_site_url;
	private $https_site_url;

	private function __construct( $run ) {
		if ( $run ) {
			$this->init();
		}
	}

	public static function get_instance( $run = true ) {
		if ( ! self::$instance ) {
			self::$instance = new self( $run );
		}

		return self::$instance;
	}

	public static function activate() {
		$self = self::get_instance();

		$self->add_config_hooks();
		ITSEC_Response::regenerate_wp_config();
	}

	public static function deactivate() {
		$self = self::get_instance( false );
		$self->remove_config_hooks();
		ITSEC_Response::regenerate_wp_config();
	}

	public function add_config_hooks() {
		if ( $this->config_hooks_added ) {
			return;
		}

		add_filter( 'itsec_filter_wp_config_modification', array( $this, 'filter_wp_config_modification' ) );

		$this->config_hooks_added = true;
	}

	public function remove_config_hooks() {
		remove_filter( 'itsec_filter_wp_config_modification', array( $this, 'filter_wp_config_modification' ) );

		$this->config_hooks_added = false;
	}

	public function init() {
		$this->add_config_hooks();

		add_filter( 'option_siteurl', array( $this, 'get_https_url' ), 5 );
		add_filter( 'option_home', array( $this, 'get_https_url' ), 5 );

		if ( is_ssl() ) {
			$this->http_site_url  = site_url( '', 'http' );
			$this->https_site_url = site_url( '', 'https' );

			add_filter( 'the_content', array( $this, 'replace_content_urls' ) );
			add_filter( 'script_loader_src', array( $this, 'script_loader_src' ) );
			add_filter( 'style_loader_src', array( $this, 'style_loader_src' ) );
			add_filter( 'upload_dir', array( $this, 'upload_dir' ) );
		} elseif ( 'cli' !== php_sapi_name() && ! ITSEC_Core::doing_data_upgrade() && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			$this->redirect_to_https();
		}
	}

	public function get_https_url( $url ) {
		return preg_replace( '/^http:/', 'https:', $url );
	}

	private function redirect_to_https() {
		add_filter( 'wp_safe_redirect_fallback', function () {
			return set_url_scheme( wp_validate_redirect( home_url( $_SERVER['REQUEST_URI'] ), home_url() ), 'https' );
		} );

		$redirect = "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		wp_safe_redirect( $redirect, 301, 'Solid Security' );
		exit();
	}

	/**
	 * Replace urls in content with ssl
	 *
	 * @since 4.1
	 *
	 * @param string $content the content
	 *
	 * @return string the content
	 */
	public function replace_content_urls( $content ) {
		return str_replace( $this->http_site_url, $this->https_site_url, $content );
	}

	/**
	 * Replace urls in scripts with ssl
	 *
	 * @since 4.4
	 *
	 * @param string $script_loader_src the url
	 *
	 * @return string the url
	 */
	public function script_loader_src( $script_loader_src ) {
		return str_replace( $this->http_site_url, $this->https_site_url, $script_loader_src );
	}

	/**
	 * Replace urls in styles with ssl
	 *
	 * @since 4.4
	 *
	 * @param string $style_loader_src the url
	 *
	 * @return string the url
	 */
	public function style_loader_src( $style_loader_src ) {
		return str_replace( $this->http_site_url, $this->https_site_url, $style_loader_src );
	}

	/**
	 * filter uploads dir so that plugins using it to determine upload URL also work
	 *
	 * @since 4.0
	 *
	 * @param array $uploads
	 *
	 * @return array
	 */
	public function upload_dir( $upload_dir ) {
		$upload_dir['url']     = str_replace( $this->http_site_url, $this->https_site_url, $upload_dir['url'] );
		$upload_dir['baseurl'] = str_replace( $this->http_site_url, $this->https_site_url, $upload_dir['baseurl'] );

		return $upload_dir;
	}

	public function filter_wp_config_modification( $modification ) {
		$modification .= "define( 'FORCE_SSL_ADMIN', true ); // " . __( 'Redirect All HTTP Page Requests to HTTPS - Security > Settings > Enforce SSL', 'better-wp-security' ) . "\n";

		return $modification;
	}
}
