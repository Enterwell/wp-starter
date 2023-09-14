<?php

namespace iThemesSecurity\Lib\REST;

use iThemesSecurity\Module_Config;

final class Modules_Controller extends \WP_REST_Controller {

	/**
	 * Modules_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'ithemes-security/v1';
		$this->rest_base = 'modules';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\w-]+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
				'args'                => [ 'context' => $this->get_context_param( [ 'default' => 'view' ] ) ],
			],
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ $this, 'update_item_permissions_check' ],
				'args'                => $this->get_endpoint_args_for_item_schema( 'PUT' ),
			],
			'schema'      => [ $this, 'get_public_item_schema' ],
			'allow_batch' => [ 'v1' => true ],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_items( $request ) {
		$modules = \ITSEC_Modules::get_available_modules();

		if ( $request['status'] ) {
			$modules = array_filter( $modules, function ( $module ) use ( $request ) {
				return $this->get_module_status( $module ) === $request['status'];
			} );
		}

		$responses = [];

		foreach ( $modules as $module ) {
			if ( ( $config = \ITSEC_Modules::get_config( $module ) ) && ! $config->get_extends() ) {
				$responses[] = $this->prepare_response_for_collection(
					$this->prepare_item_for_response( $config, $request )
				);
			}
		}

		return new \WP_REST_Response( $responses );
	}

	public function get_item_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_item( $request ) {
		if ( ! $config = \ITSEC_Modules::get_config( $request['id'] ) ) {
			return new \WP_Error(
				'rest_module_not_found',
				__( 'No module was found with that id.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		return $this->prepare_item_for_response( $config, $request );
	}

	public function update_item_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function update_item( $request ) {
		if ( ! $config = \ITSEC_Modules::get_config( $request['id'] ) ) {
			return new \WP_Error(
				'rest_module_not_found',
				__( 'No module was found with that id.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$status = $request['status']['selected'] ?? $this->get_module_status( $config->get_id() );

		if ( $status !== $this->get_module_status( $config->get_id() ) ) {
			if ( 'active' === $status ) {
				$error = \ITSEC_Modules::activate( $config->get_id() );
			} else {
				$error = \ITSEC_Modules::deactivate( $config->get_id() );
			}

			if ( is_wp_error( $error ) ) {
				\ITSEC_Lib_REST::add_status_to_error( \WP_Http::BAD_REQUEST, $error );

				return $error;
			}
		}

		$request['context'] = 'edit';

		return $this->prepare_item_for_response( $config, $request );
	}

	/**
	 * Prepares an individual module for response.
	 *
	 * @param Module_Config    $item
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$item = $item->translate();

		$data = [
			'id'                    => $item->get_id(),
			'status'                => [
				'selected' => $this->get_module_status( $item->get_id() ),
				'default'  => $item->get_status(),
			],
			'type'                  => $item->get_type(),
			'order'                 => $item->get_order(),
			'onboard'               => $item->is_onboard(),
			'side_effects'          => $item->has_side_effects(),
			'keywords'              => $item->get_keywords(),
			'title'                 => $item->get_title(),
			'description'           => $item->get_description(),
			'help'                  => $item->get_help(),
			'user_groups'           => $item->get_user_groups(),
			'password_requirements' => $item->get_password_requirements(),
			'tools'                 => $item->get_tools() ?: new \stdClass(),
			'requirements'          => $item->get_requirements(),
		];

		$fields = $this->get_fields_for_response( $request );

		if ( $settings = \ITSEC_Modules::get_settings_obj( $item->get_id() ) ) {
			if ( rest_is_field_included( 'settings.show_ui', $fields ) ) {
				$data['settings']['show_ui'] = $settings->show_ui();
			}

			if ( rest_is_field_included( 'settings.schema', $fields ) ) {
				$data['settings']['schema'] = $settings->get_settings_schema();

				foreach ( $settings->get_defaults() as $setting => $default ) {
					$data['settings']['schema']['properties'][ $setting ]['default'] = $default;
				}
			}

			if ( rest_is_field_included( 'settings.conditional', $fields ) ) {
				$data['settings']['conditional'] = [];

				foreach ( $settings->get_conditional_settings() as $setting ) {
					$data['settings']['conditional'][ $setting ] = $settings->get_conditional_setting_config( $setting );
				}
			}

			if ( rest_is_field_included( 'settings.interactive', $fields ) ) {
				$data['settings']['interactive'] = array_values(
					array_filter( $settings->get_known_settings(), [ $settings, 'is_interactive_setting' ] )
				);
			}

			if ( rest_is_field_included( 'settings.removed', $fields ) ) {
				$data['settings']['removed'] = $item->get_removed_settings();
			}

			if ( rest_is_field_included( 'settings.deprecated', $fields ) ) {
				$data['settings']['deprecated'] = $item->get_deprecated_settings();
			}

			if ( rest_is_field_included( 'settings.onboard', $fields ) ) {
				$data['settings']['onboard'] = $item->get_onboard_settings();
			}

			if ( rest_is_field_included( 'settings.import', $fields ) ) {
				$data['settings']['import'] = $item->get_import_settings();
			}
		}

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $request['context'] ?: 'view' );

		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		return $response;
	}

	/**
	 * Gets the module's status.
	 *
	 * @param string $id The module id.
	 *
	 * @return string
	 */
	private function get_module_status( string $id ): string {
		return \ITSEC_Modules::is_active( $id ) ? 'active' : 'inactive';
	}

	/**
	 * Prepares the list of links to be attached to the module.
	 *
	 * @param Module_Config $config
	 *
	 * @return array[]
	 */
	private function prepare_links( Module_Config $config ): array {
		$links = [
			'self' => [
				'href' => rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $config->get_id() ) ),
			]
		];

		if ( $config->get_settings() ) {
			$links[ \ITSEC_Lib_REST::get_link_relation( 'settings' ) ] = [
				'href'       => rest_url( sprintf( '%s/settings/%s', $this->namespace, $config->get_id() ) ),
				'embeddable' => true,
			];
		}

		return $links;
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = [
			'title'      => 'ithemes-security-module',
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'properties' => [
				'id'                    => [
					'title'    => __( 'Module ID', 'better-wp-security' ),
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'status'                => [
					'title'      => __( 'Module Status', 'better-wp-security' ),
					'type'       => 'object',
					'properties' => [
						'selected' => [
							'type'    => 'string',
							'enum'    => [ 'active', 'inactive' ],
							'context' => [ 'view', 'edit', 'embed' ],
						],
						'default'  => [
							'type'     => 'string',
							'enum'     => [ 'always-active', 'default-active', 'default-inactive' ],
							'context'  => [ 'edit' ],
							'readonly' => true,
						],
					],
					'context'    => [ 'view', 'edit', 'embed' ],
				],
				'type'                  => [
					'title'    => __( 'The Module Type', 'better-wp-security' ),
					'type'     => 'string',
					'enum'     => [ 'recommended', 'advanced', 'tool' ],
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'order'                 => [
					'title'    => __( 'Module Order' ),
					'type'     => 'integer',
					'context'  => [ 'edit' ],
					'readonly' => true,
				],
				'onboard'               => [
					'title'    => __( 'Show in Onboard', 'better-wp-security' ),
					'type'     => 'boolean',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'side_effects'          => [
					'title'    => __( 'Has Side-Effects', 'better-wp-security' ),
					'type'     => 'boolean',
					'context'  => [ 'edit' ],
					'readonly' => true,
				],
				'keywords'              => [
					'title'    => __( 'Module Search Keywords', 'better-wp-security' ),
					'type'     => 'array',
					'items'    => [
						'type' => 'string',
					],
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'title'                 => [
					'title'    => __( 'Module Title', 'better-wp-security' ),
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'description'           => [
					'title'    => __( 'Module Description', 'better-wp-security' ),
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'help'                  => [
					'title'    => __( 'Module Help', 'better-wp-security' ),
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'user_groups'           => [
					'title'    => __( 'User Groups', 'better-wp-security' ),
					'type'     => 'object',
					'context'  => [ 'edit' ],
					'readonly' => true,
				],
				'password_requirements' => [
					'title'    => __( 'Password Requirements', 'better-wp-security' ),
					'type'     => 'object',
					'context'  => [ 'edit' ],
					'readonly' => true,
				],
				'requirements'          => [
					'title'    => __( 'Requirements', 'better-wp-security' ),
					'type'     => 'object',
					'context'  => [ 'edit' ],
					'readonly' => true,
				],
				'tools'                 => [
					'title'                => __( 'Module Tools', 'better-wp-security' ),
					'type'                 => 'object',
					'additionalProperties' => [
						'type' => 'object',
					],
				],
				'settings'              => [
					'title'      => __( 'Module Settings Configuration', 'better-wp-security' ),
					'type'       => 'object',
					'properties' => [
						'show_ui'     => [
							'description' => __( 'Should a settings UI be shown.' ),
							'type'        => 'boolean',
						],
						'schema'      => [
							'description' => __( 'The schema describing the settings.', 'better-wp-security' ),
							'type'        => 'object',
						],
						'conditional' => [
							'description' => __( 'Map of setting names to conditional setting definitions.', 'better-wp-security' ),
							'type'        => 'object',
						],
						'interactive' => [
							'description' => __( 'List of interactive setting names.', 'better-wp-security' ),
							'type'        => 'array',
							'items'       => [
								'type' => 'string',
							],
						],
						'removed'     => [
							'description' => __( 'List of removed setting names.', 'better-wp-security' ),
							'type'        => 'array',
							'items'       => [
								'type' => 'string',
							],
						],
						'deprecated'  => [
							'description' => __( 'List of deprecated setting names.', 'better-wp-security' ),
							'type'        => 'array',
							'items'       => [
								'type' => 'string',
							],
						],
						'onboard'     => [
							'description' => __( 'List of settings to onboard.', 'better-wp-security' ),
							'type'        => 'array',
							'items'       => [
								'type' => 'string',
							],
						],
						'import'      => [
							'description' => __( 'List of settings to display when importing.', 'better-wp-security' ),
							'type'        => 'array',
							'items'       => [
								'type' => 'string',
							],
						],
					],
					'context'    => [ 'edit' ],
					'readonly'   => true,
				],
			],
		];

		return $this->add_additional_fields_schema( $this->schema );
	}

	public function get_collection_params() {
		return [
			'context' => $this->get_context_param( [ 'default' => 'view' ] ),
			'status'  => [
				'type' => 'string',
				'enum' => [
					'active',
					'inactive',
				],
			],
		];
	}
}
