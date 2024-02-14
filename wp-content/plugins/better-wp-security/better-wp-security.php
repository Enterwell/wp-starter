<?php

/*
 * Plugin Name: Solid Security Basic
 * Plugin URI: https://solidwp.com/products/security
 * Description: Shield your site from cyberattacks and prevent security vulnerabilities. The only security plugin you need for a solid foundation.
 * Author: SolidWP
 * Author URI: https://solidwp.com
 * Version: 9.3.1
 * Text Domain: better-wp-security
 * Network: True
 * License: GPLv2
 * Requires PHP: 7.3
 * Requires at least: 6.3
 */

if ( version_compare( phpversion(), '7.3.0', '<' ) ) {
	function itsec_free_minimum_php_version_notice() {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Solid Security Basic requires PHP 7.3 or higher.', 'better-wp-security' ) . '</p></div>';
	}

	add_action( 'admin_notices', 'itsec_free_minimum_php_version_notice' );

	return;
}

if ( version_compare( $GLOBALS['wp_version'], '6.3', '<' ) ) {
	function itsec_minimum_wp_version_notice() {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'Solid Security Basic requires WordPress 6.3 or later.', 'better-wp-security' ) . '</p></div>';
	}

	add_action( 'admin_notices', 'itsec_minimum_wp_version_notice' );

	return;
}

function itsec_load_textdomain() {
	$locale = determine_locale();
	$locale = apply_filters( 'plugin_locale', $locale, 'better-wp-security' );

	load_textdomain( 'better-wp-security', WP_LANG_DIR . "/plugins/better-wp-security/better-wp-security-$locale.mo" );
	load_plugin_textdomain( 'better-wp-security' );
}

add_action( 'plugins_loaded', 'itsec_load_textdomain' );

if ( isset( $itsec_dir ) || class_exists( 'ITSEC_Core' ) ) {
	include( dirname( __FILE__ ) . '/core/show-multiple-version-notice.php' );
	return;
}

if ( file_exists( __DIR__ . '/vendor-prod/autoload.php' ) ) {
	require_once( __DIR__ . '/vendor-prod/autoload.php' );
}

$itsec_dir = dirname( __FILE__ );

if ( is_admin() ) {
	require( "$itsec_dir/lib/icon-fonts/load.php" );
}

require( "$itsec_dir/core/core.php" );
$itsec_core = ITSEC_Core::get_instance();
$itsec_core->init( __FILE__,  'Solid Security Basic' );
