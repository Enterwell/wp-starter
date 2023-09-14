<?php

namespace iThemesSecurity\Lib\Site_Types\Question;

use iThemesSecurity\Lib\Site_Types\Answer_Handler;
use iThemesSecurity\Lib\Site_Types\Question;
use iThemesSecurity\Lib\Site_Types\Questions_Provider;
use iThemesSecurity\Lib\Site_Types\Responds;

final class Global_Question_Pack implements Questions_Provider {
	public function get_questions(): array {
		$questions = [];

		if ( ! \ITSEC_Modules::is_active( 'security-check-pro' ) ) {
			$questions[] = new class implements Question, Responds {
				public function get_id(): string {
					return self::SECURITY_CHECK_PRO;
				}

				public function get_prompt(): string {
					return __( 'Enable Security Check Pro?', 'better-wp-security' );
				}

				public function get_description(): string {
					return __( 'Detects the correct way to identify user IP addresses based on your server configuration by making an API request to iThemes.com servers. No user information is sent to iThemes.', 'better-wp-security' );
				}

				public function get_answer_schema(): array {
					return [
						'type'        => 'boolean',
						'title'       => __( 'Enable Security Check Pro', 'better-wp-security' ),
						'description' => '<a href="https://ithemes.com/privacy-policy/">' . __( 'Read our Privacy Policy', 'better-wp-security' ) . '</a>',
						'default'     => false,
						'uiSchema'    => [
							'ui:widget' => 'ToggleWidget',
						],
					];
				}

				public function respond( Answer_Handler $handler ) {
					$handler->enable_module( 'security-check-pro' );
					$handler->set_setting( 'global', 'proxy', 'security-check' );
				}
			};
		}

		return $questions;
	}
}
