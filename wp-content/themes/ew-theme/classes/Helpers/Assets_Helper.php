<?php

namespace EwStarter\Helpers;

use Exception;

class Assets_Helper {
	/**
	 * @param $svg_relative_path
	 *
	 * @throws Exception
	 */
	public static function load_svg( $svg_relative_path ): void {

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

		include $icon_path;
	}

	/**
	 * Get images path.
	 *
	 * @param $path
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function get_image_src( $path ): string {

		// Image path
		$image_path = THEME_DIR . '/assets/images/' . $path;

		// If file not exists
		if ( ! file_exists( $image_path ) ) {
			throw new Exception( 'Image does not exit!' );
		}

		// Image url
		$image_url = THEME_URL . '/assets/images/' . $path;

		// Returns image url
		return $image_url;
	}
}
