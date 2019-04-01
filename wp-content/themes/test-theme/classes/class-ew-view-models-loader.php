<?php

namespace Ew;

/**
 * Class EW_View_Models_Loader
 * @package Ew
 */
class EW_View_Models_Loader {
	/**
	 * Loads all view models required for theme.
	 */
	public function load_theme_view_models() {
		// Get view models dir name.
		$view_models_dir_name = apply_filters( 'ew_view_models_dir', 'view-models' );

		// Get view models dir
		$view_models_dir = THEME_DIR . DIRECTORY_SEPARATOR . $view_models_dir_name;

		// If there is no view models dir do nothing.
		if ( ! file_exists( $view_models_dir ) ) {
			return;
		}

		// Load interfaces
		$this->require_sources_from_dir( $view_models_dir );
	}

	/**
	 * Requires all source files from directory.
	 *
	 * @param string $dir
	 */
	private function require_sources_from_dir( $dir ) {
		// Get all files and folders of dir
		$dir_files = scandir( $dir );

		// Search for interfaces dir to load interfaces first
		$interfaces_folder_name = 'interfaces';
		$interfaces_dir_index   = array_search( $interfaces_folder_name, $dir_files );

		// Load interfaces if needed
		if ( $interfaces_dir_index !== false ) {
			// Remove interfaces from dir files
			unset( $dir_files[ $interfaces_dir_index ] );
			$interfaces_folder = $this->get_file_path( $dir, $interfaces_folder_name );

			if ( is_dir( $interfaces_folder ) ) {
				$this->require_sources_from_dir( $interfaces_folder );
			}
		}

		// Directories to load
		$directories_to_load = [];

		// Files to load
		$files_to_load = [];

		foreach ( $dir_files as $dir_file_name ) {
			if ( ! $this->validate_source_file_name( $dir_file_name ) ) {
				continue;
			}

			// Get dir file path
			$dir_file_path = $this->get_file_path( $dir, $dir_file_name );

			if ( is_dir( $dir_file_path ) ) {
				// File is dir, require sources from it
				$directories_to_load[] = $dir_file_path;
			} else if ( $this->validate_source_file_type( $dir_file_name ) ) {
				$files_to_load[] = $dir_file_path;
			}
		}

		// Load files
		foreach ( $files_to_load as $file_path ) {
			/** @noinspection PhpIncludeInspection */
			require_once $file_path;
		}

		// Load directories
		foreach ( $directories_to_load as $dir_path ) {
			// Call same function for dir
			$this->require_sources_from_dir( $dir_path );
		}
	}

	/**
	 * Gets file path from parent folder and file name.
	 *
	 * @param $file_folder
	 * @param $file_name
	 *
	 * @return string
	 */
	private function get_file_path( $file_folder, $file_name ) {

		// Return file path
		return $file_folder . DIRECTORY_SEPARATOR . $file_name;
	}

	/**
	 * Validates file name for scanning as source.
	 *
	 * @param  string $file_name
	 *
	 * @return bool
	 */
	private function validate_source_file_name( $file_name ) {

		$first_char = substr( $file_name, 0, 1 );

		return $first_char !== '.';
	}

	/**
	 * Validates file type for inclusion as source.
	 *
	 * @param  string $file_name
	 *
	 * @return bool
	 */
	private function validate_source_file_type( $file_name ) {

		// All allowed extensions
		$allowed_extensions = [ 'php' ];

		// Get file extension
		// Get file extension
		$file_name_parts = explode( ".", $file_name );
		$extension       = end( $file_name_parts );

		return in_array( $extension, $allowed_extensions );
	}
}