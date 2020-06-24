<?php

namespace EwStarter;

use Twig_Extension;
use Twig_SimpleFunction;
use Exception;

/**
 * Class Ew_Twig_Extension_Image
 * @package EwStarter
 */
class Ew_Twig_Extension_Image extends Twig_Extension {

	/**
	 * Get functions.
	 *
	 * @return array|Twig_SimpleFunction[]
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction( 'image', [ $this, 'get_images_path' ] ),
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
	public function get_images_path($path) {

		// Image path
		$image_path = THEME_DIR.'/assets/images/'.$path;

		// If file not exists
		if ( ! file_exists( $image_path ) ) {
			throw new Exception( 'Image does not exit!' );
		}

		// Image url
		$image_url = THEME_URL.'/assets/images/'.$path;

		// Returns image url
		return $image_url;
	}

}