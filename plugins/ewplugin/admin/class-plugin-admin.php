<?php

namespace EwStarter\Admin;

use EwStarter\Admin\Interfaces\Plugin_Admin_Interface;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    EWPlugin
 * @subpackage EWPlugin/admin
 * @author     Enterwell <info@enterwell.net>
 */
class Plugin_Admin implements Plugin_Admin_Interface {
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts(): void {
	}

	/**
	 * Register styles for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles(): void {
	}
}
