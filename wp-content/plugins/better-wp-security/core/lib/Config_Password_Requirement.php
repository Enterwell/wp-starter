<?php

namespace iThemesSecurity\Lib;

use iThemesSecurity\Module_Config;

abstract class Config_Password_Requirement implements Password_Requirement {

	/** @var Module_Config */
	private $config;

	/** @var string */
	private $code;

	/**
	 * Config_Password_Requirement constructor.
	 *
	 * @param Module_Config $config
	 * @param string        $code
	 */
	public function __construct( Module_Config $config, string $code ) {
		$this->config = $config;
		$this->code   = $code;
	}

	public function get_code(): string {
		return $this->code;
	}

	public function get_module(): string {
		return $this->config->get_id();
	}

	public function get_title(): string {
		return $this->get_config()['title'] ?? $this->config->translate( Module_Config::T_ABOUT )->get_title();
	}

	public function get_description(): string {
		return $this->get_config()['description'] ?? $this->config->translate( Module_Config::T_ABOUT )->get_description();
	}

	public function get_meta_key(): string {
		return "_itsec_password_evaluation_{$this->get_code()}";
	}

	public function get_settings_schema(): array {
		$schema = $this->get_config()['settings'] ?? [];

		if ( $this->has_user_group() ) {
			if ( ! $schema ) {
				$schema['type']       = 'object';
				$schema['properties'] = [];
			}

			$schema['properties']['group'] = [
				'type'     => 'array',
				'items'    => [
					'type' => 'string'
				],
				'default'  => [],
				'readonly' => true,
			];

			$schema['uiSchema'] = array_merge(
				[
					'group' => [
						'ui:widget' => 'hidden',
					]
				],
				$schema['uiSchema'] ?? []
			);
		}

		if ( ! $schema ) {
			return $schema;
		}

		$schema['title']       = $schema['title'] ?? $this->get_title();
		$schema['description'] = $schema['description'] ?? $this->get_description();

		$all_hidden = true;

		foreach ( $schema['properties'] ?? [] as $property => $prop_schema ) {
			if ( 'hidden' !== ( $schema['uiSchema'][ $property ]['ui:widget'] ?? '' ) ) {
				$all_hidden = false;
				break;
			}
		}

		if ( $all_hidden ) {
			$schema['uiSchema']['ui:widget'] = 'hidden';
		}

		return $schema;
	}

	public function has_user_group(): bool {
		return ! empty( $this->get_config()['user-group'] );
	}

	private function get_config(): array {
		return $this->config->translate( Module_Config::T_PASSWORD_REQUIREMENTS )
		                    ->get_password_requirements()[ $this->get_code() ];
	}
}
