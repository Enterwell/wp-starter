<?php

namespace iThemesSecurity\Lib\Site_Types\Question;

use iThemesSecurity\Lib\Site_Types\Answer_Handler;
use iThemesSecurity\Lib\Site_Types\Has_End_Users;
use iThemesSecurity\Lib\Site_Types\Questions_Provider;
use iThemesSecurity\Lib\Site_Types\Responds;
use iThemesSecurity\Lib\Site_Types\Templated_Question;
use iThemesSecurity\User_Groups\User_Group;

final class End_Users_Question_Pack implements Questions_Provider {

	/** @var Has_End_Users */
	private $site_type;

	/**
	 * End_Users_Question_Pack constructor.
	 *
	 * @param Has_End_Users $site_type
	 */
	public function __construct( Has_End_Users $site_type ) { $this->site_type = $site_type; }

	public function get_questions(): array {
		return [
			new class( $this->site_type ) extends Templated_Question implements Responds {
				public function __construct( Has_End_Users $site_type ) { parent::__construct( $site_type ); }

				public function get_id(): string {
					return self::SELECT_END_USERS;
				}

				public function get_answer_schema(): array {
					return [
						'type'        => 'array',
						'items'       => [
							'type'      => 'string',
							'enum'      => array_keys( wp_roles()->get_names() ),
							'enumNames' => array_values( wp_roles()->get_names() ),
						],
						'minItems'    => 1,
						'uniqueItems' => true,
						'default'     => [],
						'uiSchema'    => [
							'ui:widget' => 'checkboxes',
						]
					];
				}

				protected function get_prompt_fallback(): string {
					return __( 'Who are the end users of your website?', 'better-wp-security' );
				}

				protected function get_description_fallback(): string {
					return __( 'Select the WordPress user roles they are assigned to', 'better-wp-security' );
				}

				public function respond( Answer_Handler $handler ) {
					$user_group = new User_Group( wp_generate_uuid4() );
					$user_group->set_label( $this->site_type->get_end_users_group_label() );

					foreach ( $handler->get_answer() as $role ) {
						$user_group->add_role( $role );
					}

					$handler->create_user_group( $user_group );
					$handler->substitute_canonical_user_group( 'subscriber', $user_group );
				}
			},
		];
	}
}
