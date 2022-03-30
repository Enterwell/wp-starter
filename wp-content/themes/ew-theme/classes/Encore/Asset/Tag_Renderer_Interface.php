<?php

namespace EwStarter\Encore\Asset;

/**
 * Tag renderer used to render encore asset tags (script/link).
 */
interface Tag_Renderer_Interface
{
	/**
	 * @param string $entry_name
	 * @param string|null $package_name
	 * @param string $entrypoint_name
	 * @param array $extra_attributes
	 * @return string
	 */
	function render_webpack_script_tags(string $entry_name, string $package_name = null, string $entrypoint_name = '_default', array $extra_attributes = []): string;

	/**
	 * @param string $entry_name
	 * @param string|null $package_name
	 * @param string $entrypoint_name
	 * @param array $extra_attributes
	 * @return string
	 */
	function render_webpack_link_tags(string $entry_name, string $package_name = null, string $entrypoint_name = '_default', array $extra_attributes = []): string;

	/**
	 * @return array
	 */
	public function get_default_attributes(): array;
}
