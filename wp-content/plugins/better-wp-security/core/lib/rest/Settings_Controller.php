<?php

namespace iThemesSecurity\Lib\REST;

use iThemesSecurity\Module_Config;

final class Settings_Controller extends \WP_REST_Controller {

	/**
	 * Settings_Controller constructor.
	 */
	public function __construct() {
		$this->namespace = 'ithemes-security/v1';
		$this->rest_base = 'settings';
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
			],
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ $this, 'update_item_permissions_check' ],
				'args'                => $this->get_endpoint_args_for_item_schema( 'PUT' ),
			],
			[
				'methods'             => 'PATCH',
				'callback'            => [ $this, 'patch_item' ],
				'permission_callback' => [ $this, 'patch_item_permissions_check' ],
				'args'                => $this->get_endpoint_args_for_item_schema( 'PATCH' ),
			],
			[
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ $this, 'delete_item_permissions_check' ],
			],
			'allow_batch' => [ 'v1' => true ],
			'schema'      => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_items( $request ) {
		$responses = [];

		foreach ( \ITSEC_Modules::get_config_list( $request['modules'] ) as $config ) {
			if ( ! \ITSEC_Modules::get_settings_obj( $config->get_id() ) ) {
				continue;
			}

			$responses[ $config->get_id() ] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $config, $request )
			);
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
		\ITSEC_Core::set_interactive();

		if ( ! $config = \ITSEC_Modules::get_config( $request['id'] ) ) {
			return new \WP_Error(
				'rest_module_not_found',
				__( 'No module was found with that id.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		if ( ! $obj = \ITSEC_Modules::get_settings_obj( $request['id'] ) ) {
			return new \WP_Error(
				'rest_unsupported_module',
				__( 'This module does not have settings.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$settings = $request->get_json_params() ?: $request->get_body_params();
		$updated  = $obj->set_all( $obj->prepare_from_rest( $settings ) );
		$updated  = \ITSEC_Lib::updated_settings_to_wp_error( $updated );

		if ( is_wp_error( $updated ) ) {
			\ITSEC_Lib_REST::add_status_to_error( \WP_Http::BAD_REQUEST, $updated );

			return $updated;
		}

		return $this->prepare_item_for_response( $config, $request );
	}

	public function patch_item_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function patch_item( $request ) {
		\ITSEC_Core::set_interactive();

		if ( ! $config = \ITSEC_Modules::get_config( $request['id'] ) ) {
			return new \WP_Error(
				'rest_module_not_found',
				__( 'No module was found with that id.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		if ( ! $obj = \ITSEC_Modules::get_settings_obj( $request['id'] ) ) {
			return new \WP_Error(
				'rest_unsupported_module',
				__( 'This module does not have settings.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$current = $obj->get_all();

		foreach ( $request->get_json_params() ?: $request->get_body_params() as $setting => $value ) {
			$current[ $setting ] = $value;
		}

		$updated = $obj->set_all( $obj->prepare_from_rest( $current ) );
		$updated = \ITSEC_Lib::updated_settings_to_wp_error( $updated );

		if ( is_wp_error( $updated ) ) {
			\ITSEC_Lib_REST::add_status_to_error( \WP_Http::BAD_REQUEST, $updated );

			return $updated;
		}

		return $this->prepare_item_for_response( $config, $request );
	}

	public function delete_item_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function delete_item( $request ) {
		\ITSEC_Core::set_interactive();

		if ( ! $config = \ITSEC_Modules::get_config( $request['id'] ) ) {
			return new \WP_Error(
				'rest_module_not_found',
				__( 'No module was found with that id.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$defaults = \ITSEC_Modules::get_defaults( $config->get_id() );
		$updated  = \ITSEC_Modules::set_settings( $config->get_id(), $defaults );
		$updated  = \ITSEC_Lib::updated_settings_to_wp_error( $updated );

		if ( is_wp_error( $updated ) ) {
			\ITSEC_Lib_REST::add_status_to_error( \WP_Http::BAD_REQUEST, $updated );

			return $updated;
		}

		return $this->prepare_item_for_response( $config, $request );
	}

	/**
	 * Prepares an individual module's settings for response.
	 *
	 * @param Module_Config    $item
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function prepare_item_for_response( $item, $request ) {
		if ( ! $obj = \ITSEC_Modules::get_settings_obj( $item->get_id() ) ) {
			return new \WP_Error(
				'rest_unsupported_module',
				__( 'This module does not have settings.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$settings = $obj->prepare_for_rest();
		$schema   = $obj->get_settings_schema();

		foreach ( $settings as $setting => $value ) {
			if ( ! is_array( $value ) || $value ) {
				continue;
			}

			$type = $schema['properties'][ $setting ]['type'] ?? null;

			if ( 'object' !== $type ) {
				continue;
			}

			$settings[ $setting ] = new \stdClass();
		}

		return new \WP_REST_Response( $settings );
	}

	public function get_item_schema() {
		return [
			'title'      => 'ithemes-security-settings',
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'properties' => [],
		];
	}

	public function get_collection_params() {
		return [
			'modules' => [
				'oneOf'   => [
					[
						'type' => 'string',
						'enum' => [ ':all', ':active' ],
					],
					[
						'type'  => 'array',
						'items' => [
							'type' => 'string'
						]
					]
				],
				'default' => ':all',
			]
		];
	}
}
