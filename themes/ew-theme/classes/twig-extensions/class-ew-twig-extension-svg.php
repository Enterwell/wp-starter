<?php

namespace EwStarter;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Exception;

/**
 * Class Ew_Twig_Extension_Svg
 * @package EwStarter
 */
class Ew_Twig_Extension_Svg extends AbstractExtension {

	/**
	 * Get functions.
	 * @return array
	 */
	public function getFunctions(): array
	{
		return [
			new TwigFunction( 'load_svg', [ $this, 'load_svg' ] ),
		];
	}

	/**
	 * Get svg.
	 *
	 * @param $svg_relative_path
	 *
	 * @throws Exception
	 */
	public function load_svg( $svg_relative_path ) {

		// Get svg file path
		$default_svg_path = THEME_DIR . '/assets/images/';
		$svg_path         = apply_filters( 'ew_svg_path', $default_svg_path );

		// Check if icon has svg extension
		$icon_name_parts = explode( '.', $svg_relative_path );
		$icon_extension  = end( $icon_name_parts );

		$hasExtension = strtolower( $icon_extension ) === 'svg';

		// Add svg extension to icon if it is not already there, construct full icon pat
		$icon_path = $svg_path . $svg_relative_path . ( $hasExtension ? '' : '.svg' );

		// If file not exists
		if ( ! file_exists( $icon_path ) ) {
			throw new Exception( "SVG icon does not exist for path: $icon_path!" );
		}

		/** @noinspection PhpIncludeInspection */
		include $icon_path;
	}

}
