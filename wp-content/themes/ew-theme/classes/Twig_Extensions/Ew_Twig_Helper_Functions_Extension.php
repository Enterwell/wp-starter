<?php

namespace EwStarter\Twig_Extensions;

use EwStarter\Helpers\Assets_Helper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class Ew_Twig_Extension_Utils
 * @package EwStarter
 */
class Ew_Twig_Helper_Functions_Extension extends AbstractExtension {

	/**
	 * Get functions.
	 *
	 * @return array|TwigFunction[]
	 */
	public function getFunctions() {
		return [
			// Localization functions from WP -> twig
			new TwigFunction( '_e', '_e' ),
			new TwigFunction( '__', '__' ),

			// Helper functions
			new TwigFunction( 'function', function ( $function_name, ...$args ) {
				$function_name( $args );
			} ),
			new TwigFunction( 'get_theme_url', function () {
				return THEME_URL;
			} ),
			new TwigFunction( 'get_theme_dir', function () {
				return THEME_DIR;
			} ),

			// Asset helper functions
			new TwigFunction( 'image', [ Assets_Helper::class, 'get_image_src' ] ),
			new TwigFunction( 'load_svg', [ Assets_Helper::class, 'load_svg' ] ),
		];
	}

}
