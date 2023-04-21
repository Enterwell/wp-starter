<?php

namespace EwStarter;

use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly;

require_once "class-ew-view-models-loader.php";

/**
 * Class Ew_Theme
 * @package EwStarter
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

        // Add action to wp head
        add_action( 'admin_head', [ static::class, 'add_editor_custom_styles' ], 20 );

		// Load theme view models
		$vm_loader = new EW_View_Models_Loader();
		$vm_loader->load_theme_view_models();

		#region Remove comments support
		// Removes from admin menu
		add_action( 'admin_menu', function () {
			remove_menu_page( 'edit-comments.php' );
		} );

		// Removes from post and pages
		add_action( 'init', function () {
			remove_post_type_support( 'post', 'comments' );
			remove_post_type_support( 'page', 'comments' );
		}, 100 );

		// Removes from admin bar
		add_action( 'wp_before_admin_bar_render', function () {
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu( 'comments' );
		} );
		#endregion

		#region Security settings
		// Disable XMLRPC
		add_filter( 'xmlrpc_enabled', '__return_false' );

		// Remove WP version
		add_filter( 'the_generator', '__return_false' );
		#endregion

		// Disable all automatic WP updates
		add_filter( 'automatic_updater_disabled', '__return_true' );
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
	 * Imports styles and scripts from CDN.
	 */
	public static function import_styles_and_scripts() {
		// Load scripts from CDN here

		// Import WP jQuery
		self::add_script_to_page('__return_true', 'jquery', '', [], false, true );
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

    /**
     * Adds CSS styles to wp-admin
     */
	public static function add_editor_custom_styles() {
        global $ew_twig;

        echo $ew_twig->entry_renderer->renderWebpackLinkTags('editor_styles');
    }

	/**
	 * Enqueues script to specific pages based on callback method
	 * Calls wp_enqueue_script WP method
	 *
	 * @param $callback callable Condition function
	 * @param $handle string Script name
	 * @param string $src
	 * @param array $deps
	 * @param false $ver
	 * @param false $in_footer
	 */
	public static function add_script_to_page($callback, $handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
		!$callback() ?: wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
	}

	/**
	 * Enqueues styles to specific pages based on callback method
	 * Calls wp_enqueue_style method
	 *
	 * @param $callback callable Condition function
	 * @param $handle string Style name
	 * @param string $src
	 * @param array $deps
	 * @param false $ver
	 * @param string $media
	 */
	public static function add_style_to_page($callback, $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		!$callback() ?: wp_enqueue_style($handle, $src, $deps, $ver, $media);
	}
}
