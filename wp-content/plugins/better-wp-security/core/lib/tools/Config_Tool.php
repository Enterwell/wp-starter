<?php

namespace iThemesSecurity\Lib\Tools;

use iThemesSecurity\Module_Config;

abstract class Config_Tool implements Tool {

	/** @var string */
	private $slug;

	/** @var Module_Config */
	private $config;

	/**
	 * Config_Tool constructor.
	 *
	 * @param string        $slug
	 * @param Module_Config $config
	 */
	public function __construct( string $slug, Module_Config $config ) {
		$this->slug   = $slug;
		$this->config = $config;
	}

	public function get_slug(): string {
		return $this->slug;
	}

	public function get_module(): string {
		return $this->config->get_id();
	}

	public function is_available(): bool {
		return true;
	}

	public function get_condition(): array {
		return $this->get_config()['condition'] ?? [];
	}

	public function get_title(): string {
		return $this->get_config()['title'] ?? $this->config->translate( Module_Config::T_ABOUT )->get_title();
	}

	public function get_description(): string {
		return $this->get_config()['description'] ??
		       ( $this->config->get_type() === 'tool' ? $this->config->translate( Module_Config::T_ABOUT )->get_description() : '' );
	}

	public function get_help(): string {
		return $this->get_config()['help'] ??
		       ( $this->config->get_type() === 'tool' ? $this->config->translate( Module_Config::T_ABOUT )->get_help() : '' );
	}

	public function get_keywords(): array {
		return $this->get_config()['keywords'] ?? [];
	}

	public function is_toggleable(): bool {
		return ! empty( $this->get_config()['toggle'] );
	}

	public function get_schedule(): string {
		return $this->get_config()['schedule'] ?? '';
	}

	public function get_form(): array {
		return $this->get_config()['form'] ?? [];
	}

	private function get_config(): array {
		return $this->config->translate( Module_Config::T_TOOLS )->get_tools()[ $this->get_slug() ];
	}
}
