<?php

namespace EwStarter\Main;

use Dotenv\Dotenv;
use EwStarter\Admin\Interfaces\Plugin_Admin_Interface;
use EwStarter\Controllers\User_Applications_Controller;
use EwStarter\Main\Interfaces\Plugin_i18n_Interface;
use EwStarter\Main\Interfaces\Plugin_Loader_Interface;
use EwStarter\Public\Interfaces\Plugin_Public_Interface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    EWPlugin
 * @subpackage EWPlugin/includes
 * @author     Enterwell <info@enterwell.net>
 */
class Plugin {
	/** @var ContainerInterface */
	protected ContainerInterface $container;

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Loader_Interface $loader Maintains and registers all hooks for the plugin.
	 */
	protected Plugin_Loader_Interface $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected string $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @throws ContainerExceptionInterface
	 *
	 * @since    1.0.0
	 */
	public function __construct( ContainerInterface $container ) {
		$this->container   = $container;
		$this->plugin_name = $container->get( 'plugin.name' );
		$this->version     = $container->get( 'plugin.version' );

		$this->loader = $container->get( Plugin_Loader_Interface::class );

		$this->load_env();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->initialize_controllers();
		$this->initialize_post_types();
	}

	private function load_env(): void {
		if ( ! file_exists( PLUGIN_DIR . '.env' ) ) {
			return;
		}
		$dotenv = Dotenv::createImmutable( PLUGIN_DIR );
		$dotenv->load();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @throws ContainerExceptionInterface
	 * @since    1.0.0
	 */
	private function set_locale(): void {
		/** @var Plugin_i18n_Interface $plugin_i18n */
		$plugin_i18n = $this->container->get( Plugin_i18n_Interface::class );
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @throws ContainerExceptionInterface
	 * @since    1.0.0
	 */
	private function define_admin_hooks(): void {
		$plugin_admin = $this->container->get( Plugin_Admin_Interface::class );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @throws ContainerExceptionInterface
	 * @since    1.0.0
	 */
	private function define_public_hooks(): void {
		$plugin_public = $this->container->get( Plugin_Public_Interface::class );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
	}


	/**
	 * Initializes all new post types.
	 *
	 * @since 1.0.0
	 */
	private function initialize_post_types(): void {
		// Init all custom post types.
	}

	/**
	 * Initializes all custom REST API controllers.
	 *
	 * @throws ContainerExceptionInterface
	 * @since 1.0.0
	 */
	private function initialize_controllers(): void {
		$controllers = [
			$this->container->get( User_Applications_Controller::class )
		];

		foreach ( $controllers as $controller ) {
			$this->loader->add_action( 'rest_api_init', $controller, 'register_routes' );
		}
	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run(): void {
		$this->loader->run();
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version(): string {
		return $this->version;
	}

}
