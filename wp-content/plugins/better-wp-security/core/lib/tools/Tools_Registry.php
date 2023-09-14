<?php

namespace iThemesSecurity\Lib\Tools;

final class Tools_Registry {

	/** @var Tool[] */
	private $tools = [];

	/**
	 * Registers a tool.
	 *
	 * @param Tool $tool
	 *
	 * @return $this
	 */
	public function register( Tool $tool ): self {
		$this->tools[ $tool->get_slug() ] = $tool;

		return $this;
	}

	/**
	 * Checks if a tool is registered.
	 *
	 * @param string $slug The tool slug.
	 *
	 * @return bool
	 */
	public function is_registered( string $slug ): bool {
		return isset( $this->tools[ $slug ] );
	}

	/**
	 * Gets a tool by slug.
	 *
	 * @param string $slug
	 *
	 * @return Tool
	 */
	public function get_tool( string $slug ): Tool {
		return $this->tools[ $slug ];
	}

	/**
	 * Gets the list of registered tools.
	 *
	 * @return Tool[]
	 */
	public function get_tools(): array {
		return $this->tools;
	}
}
