<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           EWPlugin
 *
 * @wordpress-plugin
 * Plugin Name:       EWPlugin
 * Plugin URI:        http://enterwell.net
 * Description:       Administration plugin.
 * Version:           1.0.0
 * Author:            Enterwell
 * Author URI:        http://enterwell.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ew-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$autoload_file_path = plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

if ( ! file_exists( $autoload_file_path ) ) {
	throw new Exception( 'Run composer install' );
} else {
	// Load composer and dependencies
	require_once $autoload_file_path;
	require_once plugin_dir_path( __FILE__ ) . 'load-plugin.php';
}
