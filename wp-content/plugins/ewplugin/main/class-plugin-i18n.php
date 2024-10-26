<?php

namespace EwStarter\Main;

use EwStarter\Main\Interfaces\Plugin_i18n_Interface;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    EWPlugin
 * @subpackage EWPlugin/includes
 * @author     Enterwell <info@enterwell.net>
 */
class Plugin_i18n implements Plugin_i18n_Interface {
	/**
	 * The domain specified for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $domain The domain identifier for this plugin.
	 */
	private $domain;

	/**
	 * @param string $domain
	 */
	public function __construct( string $domain ) {
		$this->domain = $domain;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			$this->domain,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
