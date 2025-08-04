<?php

namespace EwStarter;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Exception;

/**
 * Class Ew_Twig_Extension_Image
 * @package EwStarter
 */
class Ew_Twig_Extension_Image extends AbstractExtension {

	/**
	 * Get functions.
	 *
	 * @return array
	 */
	public function getFunctions(): array
	{
		return [
			new TwigFunction( 'image', [ $this, 'get_images_path' ] ),
		];
	}

	/**
	 * Get images path.
	 *
	 * @param $path
	 *
	 * @return string
	 * @throws Exception
	 */
	public function get_images_path($path): string
	{

		// Image path
		$image_path = THEME_DIR . '/assets/images/' . $path;

		// If file not exists
		if ( ! file_exists( $image_path ) ) {
			throw new Exception( 'Image does not exit!' );
		}

		// Returns image url
		return THEME_URL . '/assets/images/' . $path;
	}

}
