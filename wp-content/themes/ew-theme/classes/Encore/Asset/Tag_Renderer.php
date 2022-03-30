<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EwStarter\Encore\Asset;

use Symfony\Contracts\Service\ResetInterface;

/**
 * @final
 */
class Tag_Renderer implements ResetInterface, Tag_Renderer_Interface
{
	/** @var Entrypoint_Lookup_Collection_Interface */
	private Entrypoint_Lookup_Collection_Interface $entrypoint_lookup_collection;

	/** @var array */
	private array $default_attributes;

	/** @var array */
	private array $rendered_files = [];

	/**
	 * @param Entrypoint_Lookup_Collection_Interface $entrypoint_lookup_collection
	 * @param array $defaultAttributes
	 */
	public function __construct(Entrypoint_Lookup_Collection_Interface $entrypoint_lookup_collection, array $defaultAttributes = [])
	{
		$this->entrypoint_lookup_collection = $entrypoint_lookup_collection;
		$this->default_attributes = $defaultAttributes;

		$this->reset();
	}


	/** @inheritDoc */
	public function render_webpack_script_tags(string $entry_name, string $package_name = null, string $entrypoint_name = '_default', array $extra_attributes = []): string
	{
		if ($package_name !== null) {
			throw new \InvalidArgumentException('packageName not implemented');
		}
		$script_tags = [];
		$entrypoint_lookup = $this->get_entrypoint_lookup($entrypoint_name);
		$integrity_hashes = ($entrypoint_lookup instanceof Integrity_Data_Provider_Interface) ? $entrypoint_lookup->get_integrity_data() : [];

		foreach ($entrypoint_lookup->get_javascript_files($entry_name) as $filename) {
			$attributes = $this->default_attributes;
			$attributes['src'] = $this->get_asset_path($filename, $package_name);
			$attributes = array_merge($attributes, $extra_attributes);

			if (isset($integrity_hashes[$filename])) {
				$attributes['integrity'] = $integrity_hashes[$filename];
			}

			$script_tags[] = sprintf(
				'<script %s></script>',
				$this->convert_array_to_attributes($attributes)
			);

			$this->rendered_files['scripts'][] = $attributes['src'];
		}

		return implode('', $script_tags);
	}

	/** @inheritDoc */
	public function render_webpack_link_tags(string $entry_name, string $package_name = null, string $entrypoint_name = '_default', array $extra_attributes = []): string
	{
		if ($package_name !== null) {
			throw new \InvalidArgumentException('packageName not implemented');
		}
		$script_tags = [];
		$entrypoint_lookup = $this->get_entrypoint_lookup($entrypoint_name);
		$integrity_hashes = ($entrypoint_lookup instanceof Integrity_Data_Provider_Interface) ? $entrypoint_lookup->get_integrity_data() : [];

		foreach ($entrypoint_lookup->get_css_files($entry_name) as $filename) {
			$attributes = $this->default_attributes;
			$attributes['rel'] = 'stylesheet';
			$attributes['href'] = $this->get_asset_path($filename, $package_name);
			$attributes = array_merge($attributes, $extra_attributes);

			if (isset($integrity_hashes[$filename])) {
				$attributes['integrity'] = $integrity_hashes[$filename];
			}

			$script_tags[] = sprintf(
				'<link %s>',
				$this->convert_array_to_attributes($attributes)
			);

			$this->rendered_files['styles'][] = $attributes['href'];
		}

		return implode('', $script_tags);
	}

	/** @inheritDoc */
	public function get_default_attributes(): array
	{
		return $this->default_attributes;
	}

	/**
	 *
	 */
	public function reset(): void
	{
		$this->rendered_files = [
			'scripts' => [],
			'styles' => [],
		];
	}

	/**
	 * @param string $assetPath
	 * @param string|null $packageName
	 * @return string
	 */
	private function get_asset_path(string $assetPath, string $packageName = null): string
	{
		if ($packageName !== null) {
			throw new \InvalidArgumentException('packageName not implemented');
		}

		//TODO: check if this always work.
		return $assetPath;
	}

	/**
	 * @param string $build_name
	 * @return Entrypoint_Lookup_Interface
	 */
	private function get_entrypoint_lookup(string $build_name): Entrypoint_Lookup_Interface
	{
		return $this->entrypoint_lookup_collection->get_entrypoint_lookup($build_name);
	}

	/**
	 * @param array $attributes_map
	 * @return string
	 */
	private function convert_array_to_attributes(array $attributes_map): string
	{
		// remove attributes set specifically to false
		$attributes_map = array_filter($attributes_map, static function ($value) {
			return false !== $value;
		});

		return implode(' ', array_map(
			static function ($key, $value) {
				// allows for things like defer: true to only render "defer"
				if (true === $value || null === $value) {
					return $key;
				}

				return sprintf('%s="%s"', $key, htmlentities($value));
			},
			array_keys($attributes_map),
			$attributes_map
		));
	}
}
