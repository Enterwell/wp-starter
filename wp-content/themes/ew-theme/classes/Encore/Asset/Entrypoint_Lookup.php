<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EwStarter\Encore\Asset;

use EwStarter\Encore\Exception\Entrypoint_Not_Found_Exception;

/**
 * Returns the CSS or JavaScript files needed for a Webpack entry.
 *
 * This reads a JSON file with the format of Webpack Encore's entrypoints.json file.
 *
 * @final
 */
class Entrypoint_Lookup implements Entrypoint_Lookup_Interface, Integrity_Data_Provider_Interface
{
	/** @var string */
	private string $entrypoint_json_path;

	/** @var array */
	private array $entries_data = [];

	/** @var array */
	private array $returned_files = [];

	/** @var bool */
	private bool $strict_mode;

	/**
	 * @param string $entrypoint_json_path
	 * @param bool $strict_mode
	 */
	public function __construct(string $entrypoint_json_path, bool $strict_mode = true)
	{
		$this->entrypoint_json_path = $entrypoint_json_path;
		$this->strict_mode = $strict_mode;
	}

	/**
	 * @param string $entry_name
	 * @return array
	 */
	public function get_javascript_files(string $entry_name): array
	{
		return $this->get_entry_files($entry_name, 'js');
	}

	/**
	 * @param string $entry_name
	 * @return array
	 */
	public function get_css_files(string $entry_name): array
	{
		return $this->get_entry_files($entry_name, 'css');
	}

	/**
	 * @return array
	 */
	public function get_integrity_data(): array
	{
		$entries_data = $this->get_entries_data();

		if (!\array_key_exists('integrity', $entries_data)) {
			return [];
		}

		return $entries_data['integrity'];
	}

	/**
	 * Resets the state of this service.
	 */
	public function reset()
	{
		$this->returned_files = [];
	}

	/**
	 * @param string $entry_name
	 * @param string $key
	 * @return array
	 */
	private function get_entry_files(string $entry_name, string $key): array
	{
		$this->validate_entry_name($entry_name);
		$entries_data = $this->get_entries_data();
		$entry_data = $entries_data['entrypoints'][$entry_name] ?? [];

		if (!isset($entry_data[$key])) {
			// If we don't find the file type then just send back nothing.
			return [];
		}

		// make sure to not return the same file multiple times
		$entry_files = $entry_data[$key];
		$new_files = array_values(array_diff($entry_files, $this->returned_files));
		$this->returned_files = array_merge($this->returned_files, $new_files);

		return $new_files;
	}

	/**
	 * @param string $entry_name
	 */
	private function validate_entry_name(string $entry_name)
	{
		$entries_data = $this->get_entries_data();
		if (!isset($entries_data['entrypoints'][$entry_name]) && $this->strict_mode) {
			$without_extension = substr($entry_name, 0, strrpos($entry_name, '.'));

			if (isset($entries_data['entrypoints'][$without_extension])) {
				throw new Entrypoint_Not_Found_Exception(sprintf('Could not find the entry "%s". Try "%s" instead (without the extension).', $entry_name, $without_extension));
			}

			throw new Entrypoint_Not_Found_Exception(sprintf('Could not find the entry "%s" in "%s". Found: %s.', $entry_name, $this->entrypoint_json_path, implode(', ', array_keys($entries_data))));
		}
	}

	/**
	 * @return array
	 */
	private function get_entries_data(): array
	{
		if (!empty($this->entries_data)) {
			return $this->entries_data;
		}

		if (!file_exists($this->entrypoint_json_path)) {
			if (!$this->strict_mode) {
				return [];
			}
			throw new \InvalidArgumentException(sprintf('Could not find the entrypoints file from Webpack: the file "%s" does not exist.', $this->entrypoint_json_path));
		}

		$this->entries_data = json_decode(file_get_contents($this->entrypoint_json_path), true);

		if (null === $this->entries_data) {
			throw new \InvalidArgumentException(sprintf('There was a problem JSON decoding the "%s" file', $this->entrypoint_json_path));
		}

		if (!isset($this->entries_data['entrypoints'])) {
			throw new \InvalidArgumentException(sprintf('Could not find an "entrypoints" key in the "%s" file', $this->entrypoint_json_path));
		}

		return $this->entries_data;
	}
}
