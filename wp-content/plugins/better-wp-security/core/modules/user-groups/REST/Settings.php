<?php

namespace iThemesSecurity\User_Groups\REST;

use iThemesSecurity\User_Groups\Matchable;
use iThemesSecurity\User_Groups\Matchables_Source;
use iThemesSecurity\User_Groups\Settings_Proxy;
use iThemesSecurity\User_Groups\Settings_Registration;
use iThemesSecurity\User_Groups\Settings_Registry;
use iThemesSecurity\User_Groups\Repository\User_Group_Not_Found;

class Settings extends \WP_REST_Controller {

	/** @var Matchables_Source */
	private $source;

	/** @var Settings_Registry */
	private $settings_registry;

	/** @var Settings_Proxy */
	private $proxy;

	/**
	 * REST constructor.
	 *
	 * @param Matchables_Source $source
	 * @param Settings_Registry $settings_registry
	 * @param Settings_Proxy    $proxy
	 */
	public function __construct( Matchables_Source $source, Settings_Registry $settings_registry, Settings_Proxy $proxy ) {
		$this->source            = $source;
		$this->settings_registry = $settings_registry;
		$this->proxy             = $proxy;
		$this->namespace         = 'ithemes-security/v1';
		$this->rest_base         = 'user-matchable-settings';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			],
			[
				'methods'             => 'PATCH',
				'callback'            => [ $this, 'patch_items' ],
				'permission_callback' => [ $this, 'patch_items_permissions_check' ],
				'args'                => array_merge(
					$this->get_endpoint_args_for_item_schema( 'PATCH' ),
					$this->get_collection_params()
				),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>.*)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
				'args'                => [
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
				],
			],
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => [ $this, 'update_item_permissions_check' ],
				'args'                => $this->get_endpoint_args_for_item_schema( 'PUT' ),
			],
			'schema'        => [ $this, 'get_public_item_schema' ],
			'args'          => [
				'id' => [
					'type' => 'string',
				],
			],
			'show_in_index' => false,
			'allow_batch'   => [
				'v1' => true,
			],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_items( $request ) {
		$matchables = $this->source->all();
		$return     = [];

		foreach ( $matchables as $matchable ) {
			$return[ $matchable->get_id() ] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $matchable, $request ) );
		}

		return new \WP_REST_Response( $return );
	}

	public function get_item_permissions_check( $request ) {
		return $this->update_item_permissions_check( $request );
	}

	public function get_item( $request ) {
		try {
			return $this->prepare_item_for_response( $this->source->find( $request['id'] ), $request );
		} catch ( User_Group_Not_Found $e ) {
			return new \WP_Error( 'rest_user_group_not_found', $e->getMessage(), [ 'status' => \WP_Http::NOT_FOUND ] );
		}
	}

	public function update_item_permissions_check( $request ) {
		if ( ! \ITSEC_Core::current_user_can_manage() ) {
			return false;
		}

		if ( ! $this->source->has( $request['id'] ) ) {
			return new \WP_Error( 'rest_user_group_not_found', __( 'No user group found.', 'better-wp-security' ), [ 'status' => \WP_Http::NOT_FOUND ] );
		}

		return true;
	}

	public function update_item( $request ) {
		\ITSEC_Core::set_interactive();

		try {
			$matchable   = $this->source->find( $request['id'] );
			$maybe_error = $this->update_matchable_for_request( $matchable, $request );

			if ( is_wp_error( $maybe_error ) ) {
				return $maybe_error;
			}

			$request['context'] = 'edit';

			return $this->prepare_item_for_response( $matchable, $request );
		} catch ( User_Group_Not_Found $e ) {
			return new \WP_Error( 'rest_user_group_not_found', $e->getMessage(), [ 'status' => \WP_Http::NOT_FOUND ] );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'internal_server_error', __( 'An unexpected error occurred.', 'better-wp-security' ), [ 'status' => \WP_Http::INTERNAL_SERVER_ERROR ] );
		}
	}

	/**
	 * Patch items.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function patch_items( \WP_REST_Request $request ) {
		\ITSEC_Core::set_interactive();

		$request['context'] = 'edit';

		$return = [];

		if ( isset( $request['include'] ) ) {
			$matchables = $request['include'];
		} else {
			$matchables = $this->source->all();
		}

		foreach ( $matchables as $matchable ) {
			if ( $matchable instanceof Matchable ) {
				$id = $matchable->get_id();
			} else {
				$id = $matchable;
			}

			$href = rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $id ) );

			try {
				if ( ! $matchable instanceof Matchable ) {
					$matchable = $this->source->find( $id );
				}

				$maybe_error = $this->update_matchable_for_request( $matchable, $request );

				if ( is_wp_error( $maybe_error ) ) {
					$return[] = [
						'href'   => $href,
						'status' => \WP_Http::BAD_REQUEST,
						'error'  => rest_get_server()->response_to_data( \ITSEC_Lib_REST::error_to_response( $maybe_error ), false ),
					];

					continue;
				}

				$return[] = [
					'href'     => $href,
					'status'   => \WP_Http::OK,
					'response' => $this->prepare_response_for_collection( $this->prepare_item_for_response( $matchable, $request ) ),
				];
			} catch ( User_Group_Not_Found $e ) {
				$return[] = [
					'href'   => $href,
					'status' => \WP_Http::NOT_FOUND,
					'error'  => rest_get_server()->response_to_data( \ITSEC_Lib_REST::error_to_response(
						new \WP_Error( 'rest_user_group_not_found', $e->getMessage(), [ 'status' => \WP_Http::NOT_FOUND ] )
					), false ),
				];
			} catch ( \Exception $e ) {
				$return[] = [
					'href'   => $href,
					'status' => \WP_Http::NOT_FOUND,
					'error'  => rest_get_server()->response_to_data( \ITSEC_Lib_REST::error_to_response(
						new \WP_Error( 'internal_server_error', __( 'An unexpected error occurred.', 'better-wp-security' ), [ 'status' => \WP_Http::INTERNAL_SERVER_ERROR ] )
					), false ),
				];
			}
		}

		return new \WP_REST_Response( $return, 207 );
	}

	/**
	 * Check if the user has permissions to perform a patch.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool
	 */
	public function patch_items_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function prepare_item_for_response( $item, $request ) {
		$schema = $this->get_item_schema();
		$data   = [];

		if ( ! $item instanceof Matchable ) {
			return new \WP_REST_Response( $data );
		}

		foreach ( $this->settings_registry->get_settings() as $registration ) {
			if ( ! isset( $schema['properties'][ $registration->get_module() ]['properties'][ $registration->get_setting() ] ) ) {
				continue;
			}

			$data[ $registration->get_module() ][ $registration->get_setting() ] = $this->proxy->is_enabled( $item, $registration );
		}

		$data = $this->filter_response_by_context( $data, $request['context'] );

		return new \WP_REST_Response( $data );
	}

	/**
	 * Update a matchable based on the request data.
	 *
	 * @param Matchable        $matchable
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|null
	 */
	protected function update_matchable_for_request( Matchable $matchable, \WP_REST_Request $request ) {
		$schema = $this->get_item_schema();

		foreach ( $this->settings_registry->get_settings() as $registration ) {
			if ( ! isset( $request[ $registration->get_module() ][ $registration->get_setting() ] ) ) {
				continue;
			}

			if ( ! isset( $schema['properties'][ $registration->get_module() ]['properties'][ $registration->get_setting() ] ) ) {
				continue;
			}

			$enabled = $request[ $registration->get_module() ][ $registration->get_setting() ];
			$updated = $this->proxy->set_enabled( $matchable, $registration, $enabled );

			if ( is_wp_error( $updated ) ) {
				return $updated;
			}
		}

		return null;
	}

	public function get_item_schema() {
		if ( ! empty( $this->schema ) && ! \ITSEC_Core::is_test_suite( 'wpunit' ) ) {
			return $this->schema;
		}

		$schema = [
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'ithemes-security-user-group-settings',
			'type'                 => 'object',
			'properties'           => [],
			'additionalProperties' => false,
			'links'                => [
				[
					'rel'        => 'self',
					'href'       => rest_url( sprintf( '%s/%s/{id}', $this->namespace, $this->rest_base ) ),
					'hrefSchema' => [
						'type'       => 'object',
						'properties' => [
							'id' => [
								'type' => 'string',
							],
						],
					]
				]
			],
		];

		foreach ( $this->settings_registry->get_settings() as $registration ) {
			if ( Settings_Registration::T_MULTIPLE !== $registration->get_type() ) {
				continue;
			}

			$title = $registration->get_module();

			if ( $labels = \ITSEC_Modules::get_labels( $registration->get_module() ) ) {
				$title = $labels['title'];
			}

			if ( ! isset( $schema['properties'][ $registration->get_module() ] ) ) {
				$schema['properties'][ $registration->get_module() ] = [
					'title'                => $title,
					'type'                 => 'object',
					'properties'           => [],
					'context'              => [ 'view', 'edit', 'embed' ],
					'additionalProperties' => false,
				];
			}

			$labels = $registration->get_labels();

			$schema['properties'][ $registration->get_module() ]['properties'][ $registration->get_setting() ] = [
				'title'       => isset( $labels['title'] ) ? $labels['title'] : '',
				'description' => isset( $labels['description'] ) ? $labels['description'] : '',
				'type'        => 'boolean',
				'context'     => [ 'view', 'edit', 'embed' ],
			];
		}

		if ( isset( $this->schema ) ) {
			$this->schema = $schema;
		}

		return $schema;
	}

	public function get_collection_params() {
		return [
			'context' => $this->get_context_param( [ 'default' => 'view' ] ),
			'include' => [
				'type'  => 'array',
				'items' => [
					'type' => 'string',
				],
			],
		];
	}
}
