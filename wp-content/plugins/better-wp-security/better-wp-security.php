<?php

/*
 * Plugin Name: iThemes Security
 * Plugin URI: https://ithemes.com/security
 * Description: Take the guesswork out of WordPress security. iThemes Security offers 30+ ways to lock down WordPress in an easy-to-use WordPress security plugin.
 * Author: iThemes
 * Author URI: https://ithemes.com
 * Version: 7.7.1
 * Text Domain: better-wp-security
 * Network: True
 * License: GPLv2
 * Requires PHP: 5.6
 * Requires at least: 5.2
 */

if ( version_compare( phpversion(), '5.6.0', '<' ) ) {
	function itsec_free_minimum_php_version_notice() {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'iThemes Security requires PHP 5.6 or higher.', 'better-wp-security' ) . '</p></div>';
	}

	add_action( 'admin_notices', 'itsec_free_minimum_php_version_notice' );

	return;
}

if ( version_compare( $GLOBALS['wp_version'], '5.2.0', '<' ) ) {
	function itsec_minimum_wp_version_notice() {
		echo '<div class="notice notice-error"><p>' . esc_html__( 'iThemes Security Pro requires WordPress 5.2 or later.', 'better-wp-security' ) . '</p></div>';
	}

	add_action( 'admin_notices', 'itsec_minimum_wp_version_notice' );

	return;
}

function itsec_load_textdomain() {

	if ( function_exists( 'determine_locale' ) ) {
		$locale = determine_locale();
	} elseif ( function_exists( 'get_user_locale' ) && is_admin() ) {
		$locale = get_user_locale();
	} else {
		$locale = get_locale();
	}

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
$itsec_core->init( __FILE__,  'iThemes Security' );
