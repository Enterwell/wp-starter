<?php
/**
 * Created by PhpStorm.
 * User: mateikki
 * Date: 31.8.2020.
 * Time: 12:23
 */

namespace EwStarter\Services;

use EwStarter\Helpers\Random_Values_Helper;
use EwStarter\Services\Interfaces\Files_Service_Interface;
use Exception;

/**
 * Class Files_Service
 * @package EwStarter
 */
class Files_Service implements Files_Service_Interface {
	public function check_filetype_and_ext( string $file_content, string $file_name ): string {
		// Save file to tmp location
		$tmp_file = $this->save_file_to_uploads( $file_content, $file_name, 'tmp' );

		// WordPress validate file and extension
		$validate = wp_check_filetype_and_ext( $tmp_file, $file_name );

		if ( $validate['ext'] === false ) {
			throw new Exception( 'File type/ext is not valid!' );
		}

		// Remove tmp file
		$this->unlink( $tmp_file );

		return ! empty( $validate['proper_filename'] ) ? $validate['proper_filename'] : $file_name;
	}

	/**
	 * @param string $file_path
	 *
	 * @return void
	 */
	public function unlink( string $file_path ): void {
		unlink( $file_path );
	}

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
