<?php

namespace iThemesSecurity\Lib\Site_Types\Question;

use iThemesSecurity\Lib\Site_Types\Answer_Handler;
use iThemesSecurity\Lib\Site_Types\Question;
use iThemesSecurity\Lib\Site_Types\Questions_Provider;
use iThemesSecurity\Lib\Site_Types\Responds;

final class Login_Security_Question_Pack implements Questions_Provider {

	public function get_questions(): array {
		$questions = [];

		$questions[] = new class implements Question, Responds {
			public function get_id(): string {
				return self::FIREWALL;
			}

			public function get_answer_schema(): array {
				return [
					'type'       => 'object',
					'required'   => [ 'brute-force', 'network-brute-force' ],
					'properties' => [
						'brute-force'         => [
							'type' => 'boolean',
						],
						'network-brute-force' => [
							'type' => 'boolean',
						],
					],
					'default'    => [
						'brute-force'         => true,
						'network-brute-force' => false,
					],
				];
			}

			public function get_prompt(): string {
				return __( 'Brute Force protection is the first brick in your firewall', 'better-wp-security' );
			}

			public function get_description(): string {
				return __( 'The built-in Solid Security firewall stops automated password-guessing attacks by temporarily blocking them when you enable Local Brute Force. Layer in additional protection by enabling Network Brute Force. Join with other SolidWP-protected sites, combining intelligence to combat sophisticated attackers.', 'better-wp-security' );
			}

			public function respond( Answer_Handler $handler ) {
				if ( $handler->get_answer()['brute-force'] ) {
					$handler->enable_module( 'brute-force' );
				} else {
					$handler->disable_module( 'brute-force' );
				}

				if ( $handler->get_answer()['network-brute-force'] ) {
					$handler->enable_module( 'network-brute-force' );
					$handler->set_setting( 'network-brute-force', 'email', wp_get_current_user()->user_email );
				} else {
					$handler->disable_module( 'network-brute-force' );
				}
			}
		};

		$questions[] = new class implements Question, Responds {
			public function get_id(): string {
				return self::PASSWORD_REQUIREMENTS;
			}

			public function get_answer_schema(): array {
				return [
					'type'       => 'object',
					'required'   => [ 'strength', 'hibp', 'users' ],
					'properties' => [
						'strength' => [
							'type'  => 'boolean',
							'label' => __( 'Require Strong Passwords', 'better-wp-security' ),
						],
						'hibp'     => [
							'type'  => 'boolean',
							'label' => __( 'Refuse Compromised Passwords', 'better-wp-security' ),
						],
						'users'    => [
							'type' => 'string',
							'enum' => [
								'administrator',
								'editor',
								'everyone',
							],
						],
					],
					'default'    => [
						'strength' => true,
						'hibp'     => true,
						'users'    => 'editor',
					],
				];
			}

			public function get_prompt(): string {
				return __( 'Hackers love guessable passwords; make them hate yours', 'better-wp-security' );
			}

			public function get_description(): string {
				return __( 'Enabling the recommended features will prompt users who do not have a strong password, or are using a previously compromised password, to choose a new one the next time they log in.', 'better-wp-security' );
			}

			public function respond( Answer_Handler $handler ) {
				foreach ( [ 'strength', 'hibp' ] as $requirement ) {
					if ( $handler->get_answer()[ $requirement ] !== true ) {
						continue;
					}

					if ( \ITSEC_Modules::is_available( 'passwordless-login' ) ) {
						$handler->enable_module( 'passwordless-login' );
					}

					switch ( $handler->get_answer()['users'] ) {
						case 'administrator':
							$canonicals = [ 'administrator' ];
							break;
						case 'editor':
							$canonicals = [ 'administrator', 'editor' ];
							break;
						case 'everyone':
						default:
							$canonicals = \ITSEC_Lib_Canonical_Roles::get_canonical_roles( false );
							break;
					}

					foreach ( $canonicals as $canonical ) {
						$handler->enable_setting_for_canonical(
							$canonical,
							'password-requirements',
							sprintf( 'requirement_settings.%s.group', $requirement )
						);
					}
				}
			}
		};

		$questions[] = new class implements Question, Responds {
			public function get_id(): string {
				return self::TWO_FACTOR;
			}

			public function get_answer_schema(): array {
				return [
					'type'       => 'object',
					'default'    => [
						'enabled' => true,
						'users'   => 'editor',
					],
					'required'   => [ 'enabled', 'users' ],
					'properties' => [
						'enabled' => [
							'type' => 'boolean',
						],
						'users'   => [
							'type' => 'string',
							'enum' => [
								'administrator',
								'editor',
								'everyone',
							],
						],
					],
				];
			}

			public function get_prompt(): string {
				return __( 'Let’s add an additional step when logging in', 'better-wp-security' );
			}

			public function get_description(): string {
				if ( \ITSEC_Core::get_install_type() === 'pro' ) {
					return __( 'The selected users will be required to configure a Two-Factor method the next time they log in.', 'better-wp-security' );
				}

				return __( 'The selected users will be <b>prompted</b> to configure a Two-Factor method the next time they log in.', 'better-wp-security' );
			}

			public function respond( Answer_Handler $handler ) {
				if ( $handler->get_answer()['enabled'] !== true ) {
					return;
				}

				$handler->enable_module( 'two-factor' );
				$handler->enable_module( 'passwordless-login' );

				switch ( $handler->get_answer()['users'] ) {
					case 'administrator':
						$exclude = [ 'editor', 'author', 'contributor', 'subscriber' ];
						$protect = [ 'administrator' ];

						break;
					case 'editor':
						$exclude = [ 'author', 'contributor', 'subscriber' ];
						$protect = [ 'administrator', 'editor' ];
						break;
					case 'everyone':
					default:
						$exclude = [];
						$protect = \ITSEC_Lib_Canonical_Roles::get_canonical_roles( false );
						break;
				}

				foreach ( $exclude as $canonical ) {
					$handler->enable_setting_for_canonical( $canonical, 'two-factor', 'exclude_group' );
				}

				if ( \ITSEC_Core::get_install_type() === 'pro' ) {
					foreach ( $protect as $canonical ) {
						$handler->enable_setting_for_canonical( $canonical, 'two-factor', 'protect_user_group' );
					}
				}
			}
		};

		return $questions;
	}
}
