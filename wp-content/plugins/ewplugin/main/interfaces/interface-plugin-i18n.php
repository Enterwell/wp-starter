<?php
namespace EwStarter\Main\Interfaces;

interface Plugin_i18n_Interface {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	function load_plugin_textdomain(): void;
}
