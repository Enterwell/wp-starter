<?php

namespace EwStarter;

use Ew\WpHelpers\Controllers\AController;

/**
 * Class AEwStarter_Controller
 * @package EwStarter
 */
abstract class AEwStarter_Controller extends AController {
	protected $base_route = 'wp-ew';
	protected $version = 'v1';

	/**
	 * Gets controller base route.
	 * Controller base route is initial route
	 * for all controllers in one plugin, for example all plugins in
	 * Books plugin will have routes like:
	 *
	 * /wp-json/books-plugin/v1/{route-name}
	 *
	 * 'books-plugin' is base route for books plugin.
	 *
	 * @since   1.0.0
	 * @return  string
	 */
	public function get_base_route() {
		return $this->base_route;
	}

	/**
	 * Gets controller api version.
	 * For example all plugins in
	 * Books plugin will have routes like:
	 *
	 * /wp-json/books-plugin/v1/{route-name}
	 *
	 * 'v1' is API version for our Books plugin.
	 *
	 * @since   1.0.0
	 * @return  string
	 */
	public function get_version() {
		return $this->version;
	}
}