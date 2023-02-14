<?php
/**
 * Created by PhpStorm.
 * User: mateikki
 * Date: 31.8.2020.
 * Time: 12:23
 */

namespace EwStarter;

/**
 * Class Files_Service
 * @package EwStarter
 */
class Files_Service {
	/**
	 * @param string $file_content
	 * @param string $file_name
	 * @param string $folder_name
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function save_file_to_uploads( string $file_content, string $file_name, string $folder_name ): string {
		$folder_path = implode( DIRECTORY_SEPARATOR, [ WP_CONTENT_DIR, 'uploads', $folder_name ] );

		// Ensure that folder exists
		if ( ! file_exists( $folder_path ) ) {
			$folder_created = mkdir( $folder_path, 755, true );
			if ( ! $folder_created ) {
				throw new \Exception( "Folder {$folder_path} could not be created!" );
			}
		}

		// Set file name
		$file_path = $folder_path . DIRECTORY_SEPARATOR . $file_name;

		// Alter file name if file with the same name already exists
		if ( file_exists( $file_path ) ) {
			$extension = pathinfo( $file_path, PATHINFO_EXTENSION );
			$name      = pathinfo( $file_path, PATHINFO_FILENAME );

			$file_name = $name . Random_Values_Helper::get_random_string( 4 ) . '.' . $extension;
			$file_path = $folder_path . DIRECTORY_SEPARATOR . $file_name;
		}

		$created = file_put_contents( $file_path, $file_content );
		if ( ! $created ) {
			throw new \Exception( 'File could not be created!' );
		}

		return $file_path;
	}
}
