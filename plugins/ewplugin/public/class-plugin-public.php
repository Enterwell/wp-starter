<?php

namespace EwStarter\Public;

use EwStarter\Public\Interfaces\Plugin_Public_Interface;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    EWPlugin
 * @subpackage EWPlugin/public
 * @author     Enterwell <info@enterwell.net>
 */
class Plugin_Public implements Plugin_Public_Interface {
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles(): void {
		// TODO: Add stlyes
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts(): void {
		// TODO: Add scripts
	}

}
