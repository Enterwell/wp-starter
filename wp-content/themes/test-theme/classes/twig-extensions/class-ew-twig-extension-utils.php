<?php

namespace Ew;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class Ew_Twig_Extension_Utils
 * @package Ew
 */
class Ew_Twig_Extension_Utils extends Twig_Extension {

	/**
	 * Get functions.
	 *
	 * @return array|Twig_SimpleFunction[]
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction( 'is_admin_logged_in', [ $this, 'is_admin_logged_in' ] ),
			new Twig_SimpleFunction( 'get_theme_url', [ $this, 'get_theme_url' ] ),
		];
	}

	/**
	 * Returns true if current user is admin; false otherwise
	 *
	 * @return bool
	 */
	public function is_admin_logged_in(  ) {
		return current_user_can( 'administrator' );
	}

	/**
	 * Returns the theme URL
	 *
	 * @return string
	 */
	public function get_theme_url() {
		return THEME_URL;
	}

}