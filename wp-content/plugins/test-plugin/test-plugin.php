<?php

use Ew\TestPluginActivator;
use Ew\TestPluginDeactivator;
use Ew\TestPlugin;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           TestPlugin
 *
 * @wordpress-plugin
 * Plugin Name:       TestPlugin
 * Plugin URI:        http://enterwell.net
 * Description:       Administration plugin.
 * Version:           1.0.0
 * Author:            Enterwell
 * Author URI:        http://enterwell.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       test-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-test-plugin-activator.php
 */
function activate_test_plugin() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-test-plugin-activator.php';
    TestPluginActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-test-plugin-deactivator.php
 */
function deactivate_test_plugin() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-test-plugin-deactivator.php';
    TestPluginDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_test_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_test_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-test-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_test_plugin() {

    $plugin = new TestPlugin();
    $plugin->run();

}
run_test_plugin();