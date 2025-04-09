<?php

namespace iThemesSecurity\Lib\Site_Types\Question;

use iThemesSecurity\Lib\Site_Types\Templated_Question;

class Site_Scan_Question extends Templated_Question {
	public function get_id(): string {
		return self::SCAN_SITE;
	}

	protected function get_prompt_fallback(): string {
		return __( 'Before we configure Solid Security, let’s scan your site for vulnerabilities…', 'better-wp-security' );
	}

	public function get_description(): string {
		return __( 'Scanning your website helps you better understand your security situation. Having more context around your site’s security is helpful to know for your initial setup.', 'better-wp-security' );
	}

	public function get_answer_schema(): array {
		return [
			'type' => [ 'boolean', 'integer' ],
		];
	}
}
