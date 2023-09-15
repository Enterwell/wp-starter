<?php

namespace iThemesSecurity\Lib\REST;

use iThemesSecurity\Lib\Site_Types;
use iThemesSecurity\User_Groups\User_Group;

final class Site_Types_Controller extends \WP_REST_Controller {

	/** @var Site_Types\Registry */
	private $registry;

	/** @var Site_Types\Defaults */
	private $defaults;

	/**
	 * Site_Types_Controller constructor.
	 *
	 * @param Site_Types\Registry $registry
	 * @param Site_Types\Defaults $defaults
	 */
	public function __construct( Site_Types\Registry $registry, Site_Types\Defaults $defaults ) {
		$this->registry  = $registry;
		$this->defaults  = $defaults;
		$this->namespace = 'ithemes-security/v1';
		$this->rest_base = 'site-types';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\w-]+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
			],
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ $this, 'update_item_permissions_check' ],
				'args'                => $this->get_endpoint_args_for_item_schema( 'PUT' ),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_items( $request ) {
		return array_map( function ( Site_Types\Site_Type $site_type ) use ( $request ) {
			return $this->prepare_response_for_collection(
				$this->prepare_item_for_response( new Site_Types\Controller( $site_type ), $request )
			);
		}, $this->registry->get_site_types() );
	}

	public function get_item_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_item( $request ) {
		if ( ! $site_type = $this->registry->get_by_slug( $request['id'] ) ) {
			return new \WP_Error( 'rest_not_found', __( 'Site Type not found.', 'better-wp-security' ), [ 'status' => \WP_Http::NOT_FOUND ] );
		}

		return $this->prepare_item_for_response( new Site_Types\Controller( $site_type ), $request );
	}

	public function update_item_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function update_item( $request ) {
		$controller = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $controller ) ) {
			return $controller;
		}

		return $this->prepare_item_for_response( $controller, $request );
	}

	protected function prepare_item_for_database( $request ) {
		if ( ! $site_type = $this->registry->get_by_slug( $request['id'] ) ) {
			return new \WP_Error( 'rest_not_found', __( 'Site Type not found.', 'better-wp-security' ), [ 'status' => \WP_Http::NOT_FOUND ] );
		}

		$answers = [];
		$latest  = null;

		foreach ( $request['answers'] ?? [] as $i => $answer ) {
			if ( $i === count( $request['answers'] ) - 1 ) {
				$latest = $answer;
				break;
			}

			$question = \ITSEC_Lib::find_where( $site_type->get_questions(), static function ( Site_Types\Question $question ) use ( $answer ) {
				return $question->get_id() === $answer['question'];
			} );

			$user_groups = array_map( function ( $user_group ) {
				return ( new User_Group( $user_group['id'] ) )
					->set_label( $user_group['label'] )
					->set_users( array_filter( array_map( 'get_userdata', $user_group['users'] ) ) )
					->set_roles( $user_group['roles'] )
					->set_canonical_roles( $user_group['canonical'] )
					->set_min_role( $user_group['min_role'] );
			}, $answer['user_groups'] );

			$answers[] = new Site_Types\Answered_Question(
				$question,
				$answer['answer'],
				$user_groups ?? [],
				$answer['user_groups_settings'] ?? [],
				$answer['canonical_group_substitutions'] ?? [],
				$answer['modules'] ?? [],
				$answer['settings'] ?? []
			);
		}

		$controller = new Site_Types\Controller( $site_type, $answers );

		if ( null !== $latest ) {
			$question = \ITSEC_Lib::find_where( $site_type->get_questions(), static function ( Site_Types\Question $question ) use ( $latest ) {
				return $question->get_id() === $latest['question'];
			} );

			$answered = $controller->answer( $question, $latest['answer'] );

			if ( is_wp_error( $answered ) ) {
				return $answered;
			}
		}

		return $controller;
	}

	/**
	 * Prepares a site type controller for a REST API response.
	 *
	 * @param Site_Types\Controller $item
	 * @param \WP_REST_Request      $request
	 *
	 * @return \WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		if ( ! $site_type = $item->get_selected_site_type() ) {
			return new \WP_REST_Response();
		}

		$data = [
			'id'            => $site_type->get_slug(),
			'title'         => $site_type->get_title(),
			'description'   => $site_type->get_description(),
			'icon'          => $site_type->get_icon(),
			'recommended'   => $site_type->get_slug() === $this->defaults->get_suggested_site_type(),
			'next_question' => null,
			'answers'       => [],
		];

		if ( $next = $item->get_next_question() ) {
			$data['next_question'] = [
				'id'            => $next->get_id(),
				'prompt'        => $next->get_prompt(),
				'description'   => $next->get_description(),
				'answer_schema' => $next->get_answer_schema(),
			];

			if ( null !== ( $default = $this->defaults->get_default_for_question( $next->get_id() ) ) ) {
				$data['next_question']['answer_schema']['default'] = $default;
			}
		}

		foreach ( $item->get_previous() as $previous ) {
			$data['answers'][] = [
				'question'                      => $previous->get_question()->get_id(),
				'answer'                        => $previous->get_answer(),
				'modules'                       => $previous->get_enabled_modules(),
				'settings'                      => $previous->get_settings(),
				'user_groups'                   => array_map(
					function ( $user_group ) { return $user_group->jsonSerialize(); },
					$previous->get_user_groups()
				),
				'user_groups_settings'          => $previous->get_user_group_settings(),
				'canonical_group_substitutions' => $previous->get_canonical_user_group_substitutions(),
			];
		}

		return new \WP_REST_Response( $data );
	}

	public function get_item_schema() {
		if ( ! $this->schema ) {
			$this->schema = [
				'title'      => 'ithemes-security-site-type',
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'type'       => 'object',
				'properties' => [
					'id'            => [
						'description' => __( 'The unique id identifying the site type.', 'better-wp-security' ),
						'type'        => 'string',
						'readonly'    => true,
					],
					'title'         => [
						'description' => __( 'The title of the Site Type.', 'better-wp-security' ),
						'type'        => 'string',
						'readonly'    => true,
					],
					'description'   => [
						'description' => __( 'The description of the Site Type.', 'better-wp-security' ),
						'type'        => 'string',
						'readonly'    => true,
					],
					'icon'          => [
						'description' => __( 'The name of the icon representing the Site Type.', 'better-wp-security' ),
						'type'        => 'string',
						'readonly'    => true,
					],
					'next_question' => [
						'description' => __( 'The next question to ask the user.', 'better-wp-security' ),
						'type'        => 'object',
						'readonly'    => true,
						'properties'  => [
							'id'            => [
								'type' => 'string',
							],
							'prompt'        => [
								'type' => 'string',
							],
							'answer_schema' => [
								'type' => 'string',
							],
						],
					],
					'answers'       => [
						'description' => __( 'The list of answers.', 'better-wp-security' ),
						'type'        => 'array',
						'items'       => [
							'type'       => 'object',
							'properties' => [
								'question'                      => [
									'description' => __( 'The question id.', 'better-wp-security' ),
									'type'        => 'string',
								],
								'answer'                        => [
									'description' => __( 'The user provided answer.', 'better-wp-security' ),
									'type'        => [ 'array', 'object', 'boolean', 'number', 'integer', 'string' ],
								],
								'modules'                       => [
									'type'     => 'array',
									'readonly' => true,
									'items'    => [
										'type' => 'string',
									],
								],
								'settings'                      => [
									'type'     => 'object',
									'readonly' => true,
								],
								'user_groups'                   => [
									'type'     => 'array',
									'readonly' => true,
									'items'    => [
										'type' => 'object',
									],
								],
								'user_groups_settings'          => [
									'type'                 => 'object',
									'readonly'             => true,
									'additionalProperties' => [
										'type' => 'object',
									],
								],
								'canonical_group_substitutions' => [
									'type'                 => 'object',
									'readonly'             => true,
									'additionalProperties' => [
										'type' => 'string',
									],
								],
							],
						],
						'arg_options' => [
							'validate_callback' => function ( $value ) {
								if ( ! is_array( $value ) ) {
									return new \WP_Error( 'rest_invalid_type', __( 'Answers must be an array.', 'better-wp-security' ) );
								}

								foreach ( $value as $i => $item ) {
									if ( ! is_array( $item ) ) {
										return new \WP_Error( 'rest_invalid_type', sprintf( __( 'Answers entry %d must be an object.', 'better-wp-security' ), $i ) );
									}

									if ( ! isset( $item['question'], $item['answer'] ) ) {
										return new \WP_Error( 'rest_property_required', sprintf( __( 'The question and answer properties are required for answers entry %d.', 'better-wp-security' ), $i ) );
									}

									if ( ! is_string( $item['question'] ) ) {
										return new \WP_Error( 'rest_invalid_type', sprintf( __( 'The question property must be a string for answers entry %d.', 'better-wp-security' ), $i ) );
									}
								}

								return true;
							},
							'sanitize_callback' => function ( $value ) {
								return is_array( $value ) ? $value : [];
							},
						],
					],
				],
			];
		}

		return $this->schema;
	}
}
