<?php

namespace Ew;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly;

require_once "class-ew-view-models-loader.php";

/**
 * Class Ew_Theme
 * @package Ew
 */
class Ew_Theme {

	/**
	 * Load this class.
	 */
	public static function load() {

		// Add action ew theme setup
		add_action( 'after_setup_theme', [ static::class, 'theme_setup' ] );

		// Add action for enqueue scripts
		add_action( 'wp_enqueue_scripts', [ static::class, 'import_styles_and_scripts' ] );

		// Add action to wp head
		add_action( 'wp_head', [ static::class, 'ew_setup_theme_js_vars' ], 100 );

		// Load theme view models
		$vm_loader = new EW_View_Models_Loader();
		$vm_loader->load_theme_view_models();
	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	public static function theme_setup() {

		// Add support for title tag
		add_theme_support( 'title-tag' );

		// Add support for post thumbnails
		add_theme_support( 'post-thumbnails' );

		// Register main nav menu
		register_nav_menu( EW_MAIN_NAV_MENU, __( 'Main navigation menu', THEME_TEXTDOMAIN ) );
	}

	/**
	 * Imports all theme functions and styles.
	 */
	public static function import_styles_and_scripts() {

		// Get theme json config
		$theme_config = static::get_theme_json_config();

		// If development environment is defined load
		// development styles and scripts
		if ( defined( 'EW_DEV' ) ) {

			// Include styles
			wp_enqueue_style( 'ew_styles_main', THEME_URL . "/assets/dist/main.css", [], false, false );

			// Include scripts
			wp_enqueue_script( 'ew_scripts_main', "//192.168.0.39:10000/bundle.min.js", [], false, true );

			return;
		}

		// If development environment is not defined, load production styles and scripts
		wp_enqueue_style( 'ew_styles_main', THEME_URL . '/assets/dist/styles.min.css?v=' . $theme_config['version'] );
		wp_enqueue_script( 'ew_scripts_main', THEME_URL . '/assets/dist/bundle.min.js?v=' . $theme_config['version'], [], false, true );
	}

	/**
	 * Get theme json config.
	 *
	 * @return array|mixed|object
	 * @throws Exception
	 */
	public static function get_theme_json_config() {

		// Config file path
		$config_file_path = THEME_DIR . '/ew-theme-config.json';

		// If file not exists
		if ( ! file_exists( $config_file_path ) ) {
			throw new Exception( 'Configuration file does not exit!' );
		}

		// Get config file content
		$config_file_content = file_get_contents( $config_file_path );

		// Decode json file
		$theme_config = json_decode( $config_file_content, 'ARRAY_A' );

		// Validate files
		if ( empty( $theme_config ) ) {
			throw new Exception( 'Configuration file not valid.' );
		}

		// Returns theme config
		return $theme_config;
	}

	/**
	 * Sets up and injects theme javascript global variables.
	 */
	public static function ew_setup_theme_js_vars() {

		// Setup initial js vars
		$theme_js_vars = [
			'ew' => [
				'apiData' => [
					'apiNonce' => wp_create_nonce( 'wp_rest' ),
					'apiUrl'   => '/wp-json/wp-ew/v1/'
				]
			],
		];

		// Apply filters to those
		$theme_js_vars = apply_filters( 'ew_theme_js_vars', $theme_js_vars );

		// Create script tag
		$js_vars_output = '<script type="text/javascript">';

		// Inject vars
		foreach ( $theme_js_vars as $var_name => $var_value ) {

			// Encode value as JSON
			$var_value = wp_json_encode( $var_value );

			// Add var to output
			$js_vars_output .= "var $var_name = $var_value;\r\n";
		}

		// Close script tag
		$js_vars_output .= '</script>';

		// Echo vars
		echo $js_vars_output;
	}

}