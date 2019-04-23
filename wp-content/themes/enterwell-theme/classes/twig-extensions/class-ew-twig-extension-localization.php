<?php

namespace EwStarter;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class Ew_Twig_Extension_Localization
 * @package EwStarter
 */
class Ew_Twig_Extension_Localization extends Twig_Extension {

	/**
	 * Get functions
	 *
	 * @return array|Twig_SimpleFunction[]
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction( 'translate', [ $this, 'localization']),
		];
	}

	/**
	 * Return translated version of passed string depending on current page language
	 *
	 * @param $string
	 */
	public function localization( $string ) {
		_e( $string, THEME_TEXTDOMAIN);
	}
}