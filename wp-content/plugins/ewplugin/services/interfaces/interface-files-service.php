<?php

namespace EwStarter\Services\Interfaces;

use Exception;

/**
 * Files service interface.
 */
interface Files_Service_Interface {
	/**
	 * Checks if file type and extension matches
	 * for given filename, returns filename with
	 * proper extension.
	 *
	 * @param string $file_content
	 * @param string $file_name
	 *
	 * @return string
	 */
	function check_filetype_and_ext( string $file_content, string $file_name ): string;

	/**
	 * Function used to delete the file.
	 *
	 * @param string $file_path
	 *
	 * @return void
	 */
	function unlink( string $file_path ): void;

	/**
	 * @param string $file_content
	 * @param string $file_name
	 * @param string $folder_name
	 *
	 * @return string
	 * @throws Exception
	 */
	function save_file_to_uploads( string $file_content, string $file_name, string $folder_name ): string;
}
