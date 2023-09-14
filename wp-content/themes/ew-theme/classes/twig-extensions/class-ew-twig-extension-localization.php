<?php

namespace EwStarter;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class Ew_Twig_Extension_Localization
 * @package EwStarter
 */
class Ew_Twig_Extension_Localization extends AbstractExtension {

	/**
	 * Get functions
	 *
	 * @return array
	 */
	public function getFunctions(): array
	{
		return [
			new TwigFunction( 'translate', [ $this, 'localization' ] ),
		];
	}

	/**
	 * Return translated version of passed string depending on current page language
	 *
	 * @param $string
	 */
	public function localization( $string ) {
		_e( $string, THEME_TEXTDOMAIN );
	}
}
