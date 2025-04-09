<?php

namespace iThemesSecurity\Lib\Site_Types\Question;

use iThemesSecurity\Lib\Site_Types\Answer_Handler;
use iThemesSecurity\Lib\Site_Types\Has_Prerequisites;
use iThemesSecurity\Lib\Site_Types\Question;
use iThemesSecurity\Lib\Site_Types\Questions_Provider;
use iThemesSecurity\Lib\Site_Types\Responds;
use iThemesSecurity\User_Groups\User_Group;

final class Client_Question_Pack implements Questions_Provider {
	const PREREQUISITE_IS_CLIENT = [
		'type' => 'boolean',
		'enum' => [ true ],
	];

	const PREREQUISITE_CREATE_CLIENT_USER_GROUPS = [
		'type' => 'boolean',
		'enum' => [ false ],
	];

	public function get_questions(): array {
		return [
			new class implements Question, Responds {
				public function get_id(): string {
					return self::IS_CLIENT;
				}

				public function get_prompt(): string {
					return __( 'Choosing who you are setting the site up for will help to preconfigure some settings for you.', 'better-wp-security' );
				}

				public function get_description(): string {
					return __( 'If you are setting up Solid Security for a client, next you‘ll select which users are theirs. Identifying your clients allows you to control their access to things like the security settings or if they are allowed to view the Security Dashboard.', 'better-wp-security' );
				}

				public function get_answer_schema(): array {
					return [
						'type'    => 'boolean',
						'default' => false,
					];
				}

				public function respond( Answer_Handler $handler ) {
					if ( ! $handler->get_answer() ) {
						$handler->enable_setting_for_canonical( 'administrator', 'global', 'manage_group' );
						$handler->enable_setting_for_canonical( 'administrator', 'dashboard', 'group' );
					}
				}
			},

			new class implements Question, Has_Prerequisites {
				public function get_prerequisites(): array {
					return [
						self::IS_CLIENT => Client_Question_Pack::PREREQUISITE_IS_CLIENT,
					];
				}

				public function get_id(): string {
					return self::CLIENTS_CAN_MANAGE;
				}

				public function get_prompt(): string {
					return __( 'Should your clients be able to view and make changes to the Solid Security settings?', 'better-wp-security' );
				}

				public function get_description(): string {
					return __( 'Restricting client access to the security settings will prevent them from making unwanted changes and seeing security notifications they may not understand.', 'better-wp-security' );
				}

				public function get_answer_schema(): array {
					return [
						'type'     => 'boolean',
						'title'    => __( 'Yes, allow managing of Solid Security', 'better-wp-security' ),
						'default'  => false,
						'uiSchema' => [
							'ui:widget' => 'ToggleWidget',
						],
					];
				}
			},

			new class implements Question, Has_Prerequisites, Responds {
				public function get_prerequisites(): array {
					return [
						self::CLIENTS_CAN_MANAGE => Client_Question_Pack::PREREQUISITE_CREATE_CLIENT_USER_GROUPS,
					];
				}

				public function get_id(): string {
					return self::SELECT_CLIENTS;
				}

				public function get_prompt(): string {
					return __( 'Which users are your clients?', 'better-wp-security' );
				}

				public function get_description(): string {
					return __( 'Solid Security Pro will group client users together, allowing you to manage their access to sensitive information and which security settings you enable for them.', 'better-wp-security' );
				}

				public function get_answer_schema(): array {
					return [
						'title'       => __( 'Users', 'better-wp-security' ),
						'type'        => 'array',
						'items'       => [
							'type'    => 'integer',
							'minimum' => 0,
						],
						'minItems'    => 1,
						'uniqueItems' => true,
						'default'     => [],
						'uiSchema'    => [
							'ui:field'   => 'EntitySelectField',
							'ui:options' => [
								'path'      => '/wp/v2/users',
								'query'     => [
									'per_page'     => 100,
									'context'      => 'embed',
									'itsec_global' => true,
								],
								'labelAttr' => 'name',
							],
						],
					];
				}

				public function respond( Answer_Handler $handler ) {
					$user_group = new User_Group( wp_generate_uuid4() );
					$user_group->set_label( __( 'Clients', 'better-wp-security' ) );

					foreach ( $handler->get_answer() as $id ) {
						if ( $user = get_userdata( $id ) ) {
							$user_group->add_user( $user );
						}
					}

					$handler->create_user_group( $user_group );
				}
			},

			new class implements Question, Has_Prerequisites, Responds {
				public function get_prerequisites(): array {
					return [
						self::CLIENTS_CAN_MANAGE => Client_Question_Pack::PREREQUISITE_CREATE_CLIENT_USER_GROUPS,
					];
				}

				public function get_id(): string {
					return self::SELECT_SITE_ADMINS;
				}

				public function get_prompt(): string {
					return __( 'Which users will manage Solid Security on this site?', 'better-wp-security' );
				}

				public function get_description(): string {
					return __( 'Include yourself and anyone else who will need access to Solid Security. Grouping these users together will allow you to manage their access to sensitive information and which security settings you enable for them.', 'better-wp-security' );
				}

				public function get_answer_schema(): array {
					return [
						'title'       => __( 'Users', 'better-wp-security' ),
						'type'        => 'array',
						'items'       => [
							'type'    => 'integer',
							'minimum' => 0,
						],
						'default'     => [
							get_current_user_id(),
						],
						'minItems'    => 1,
						'uniqueItems' => true,
						'uiSchema'    => [
							'ui:field'   => 'EntitySelectField',
							'ui:options' => [
								'path'      => '/wp/v2/users',
								'query'     => [
									'per_page'     => 100,
									'context'      => 'embed',
									'itsec_global' => true,
								],
								'labelAttr' => 'name',
							],
						],
					];
				}

				public function respond( Answer_Handler $handler ) {
					$user_group = new User_Group( wp_generate_uuid4() );
					$user_group->set_label( __( 'Security Managers', 'better-wp-security' ) );

					foreach ( $handler->get_answer() as $id ) {
						if ( $user = get_userdata( $id ) ) {
							$user_group->add_user( $user );
						}
					}

					$handler->create_user_group( $user_group );
					$handler->enable_setting_for( $user_group, 'global', 'manage_group' );
					$handler->enable_setting_for( $user_group, 'dashboard', 'group' );

					if ( $handler->has_answered( self::PASSWORD_REQUIREMENTS ) ) {
						$answer = $handler->get_previous( self::PASSWORD_REQUIREMENTS )->get_answer();

						foreach ( [ 'strength', 'hibp' ] as $requirement ) {
							if ( ! $answer[ $requirement ] ) {
								continue;
							}

							$handler->enable_setting_for(
								$user_group,
								'password-requirements',
								sprintf( 'requirement_settings.%s.group', $requirement )
							);

							if ( \ITSEC_Modules::is_available( 'passwordless-login' ) ) {
								$handler->enable_setting_for( $user_group, 'passwordless-login', 'group' );
							}
						}
					}

					if ( $handler->has_answered( self::TWO_FACTOR ) ) {
						$answer = $handler->get_previous( self::TWO_FACTOR )->get_answer();

						if ( $answer['enabled'] ) {
							if ( \ITSEC_Core::get_install_type() === 'pro' ) {
								$handler->enable_setting_for( $user_group, 'two-factor', 'protect_user_group' );
							}
						}
					}
				}
			},
		];
	}
}
