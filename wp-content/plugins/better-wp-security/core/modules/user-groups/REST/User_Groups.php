<?php

namespace iThemesSecurity\User_Groups\REST;

use iThemesSecurity\User_Groups\Repository\Repository;
use iThemesSecurity\User_Groups\User_Group;
use iThemesSecurity\User_Groups\Repository\User_Group_Not_Found;
use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\Exception\WP_Error;

class User_Groups extends \WP_REST_Controller {

	const ID_PATTERN = '(?P<id>[\\w_:-]+)';

	/** @var \iThemesSecurity\User_Groups\Repository\Repository */
	private $repository;

	/**
	 * REST constructor.
	 *
	 * @param \iThemesSecurity\User_Groups\Repository\Repository $repository
	 */
	public function __construct( Repository $repository ) {
		$this->repository = $repository;
		$this->namespace  = 'ithemes-security/v1';
		$this->rest_base  = 'user-groups';
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
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => [ $this, 'create_item_permissions_check' ],
				'args'                => array_merge( $this->get_endpoint_args_for_item_schema(), [
					'ignore_duplicate' => [
						'type' => 'boolean',
					],
				] ),
				'allow_batch'         => [
					'v1' => true,
				],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		register_rest_route( $this->namespace, $this->rest_base . '/' . self::ID_PATTERN, [
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
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => [ $this, 'delete_item_permissions_check' ],
			],
			'schema'      => [ $this, 'get_public_item_schema' ],
			'args'        => [
				'id' => [
					'type' => 'string',
				],
			],
			'allow_batch' => [
				'v1' => true,
			],
		] );
	}

	public function get_items_permissions_check( $request ) {
		if ( 'edit' === $request['context'] && ! current_user_can( 'itsec_edit_user_groups' ) ) {
			return new \WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit user groups.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
		}

		if ( ! current_user_can( 'itsec_list_user_groups' ) ) {
			return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you are not allowed to list user groups.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}

	public function get_items( $request ) {
		$data = [];

		foreach ( $this->repository->all() as $user_group ) {
			$data[] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $user_group, $request ) );
		}

		return new \WP_REST_Response( $data );
	}

	public function get_item_permissions_check( $request ) {
		if ( true !== ( $error = $this->check_group_exists( $request ) ) ) {
			return $error;
		}

		if ( 'edit' === $request['context'] && ! current_user_can( 'itsec_edit_user_group', $request['id'] ) ) {
			return new \WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit this user group.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
		}

		if ( ! current_user_can( 'itsec_read_user_group', $request['id'] ) ) {
			return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you are not allowed to view this user group.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}

	public function get_item( $request ) {
		try {
			return $this->prepare_item_for_response( $this->repository->get( $request['id'] ), $request );
		} catch ( User_Group_Not_Found $e ) {
			return new \WP_Error( 'rest_user_group_not_found', $e->getMessage(), [ 'status' => \WP_Http::NOT_FOUND ] );
		}
	}

	public function create_item_permissions_check( $request ) {
		if ( ! current_user_can( 'itsec_create_user_groups' ) ) {
			return new \WP_Error( 'rest_cannot_create', __( 'Sorry, you are not allowed to create user groups.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}

	public function create_item( $request ) {
		try {
			$user_group = $this->prepare_item_for_database( $request );

			if ( is_wp_error( $user_group ) ) {
				return $user_group;
			}

			if ( ! $request['ignore_duplicate'] && $response = $this->handle_duplicate_check( $user_group ) ) {
				return $response;
			}

			$this->repository->persist( $user_group, [] );
			$request['context'] = 'edit';

			$response = $this->prepare_item_for_response( $user_group, $request );
			$response->set_status( \WP_Http::CREATED );
			$response->header( 'Location', rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $user_group->get_id() ) ) );

			return $response;
		} catch ( \Exception $e ) {
			return new \WP_Error( 'internal_server_error', __( 'An unexpected error occurred.', 'better-wp-security' ), [ 'status' => \WP_Http::INTERNAL_SERVER_ERROR ] );
		}
	}

	public function update_item_permissions_check( $request ) {
		if ( true !== ( $error = $this->check_group_exists( $request ) ) ) {
			return $error;
		}

		if ( ! current_user_can( 'itsec_edit_user_group', $request['id'] ) ) {
			return new \WP_Error( 'rest_cannot_edit', __( 'Sorry, you are not allowed to edit this user group.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}

	/**
	 * Handle checking for duplicate user groups.
	 *
	 * @param User_Group $user_group
	 *
	 * @return \WP_REST_Response|null
	 */
	private function handle_duplicate_check( User_Group $user_group ) {
		/** @var User_Group[] $duplicates */
		$duplicates = array_filter( $this->repository->all(), static function ( User_Group $maybe_group ) use ( $user_group ) {
			return $user_group->equals( $maybe_group );
		} );

		if ( ! $duplicates ) {
			return null;
		}

		\ITSEC_Lib::load( 'rest' );
		$error    = new \WP_Error( 'rest_duplicate_user_group', __( 'Another user group with this configuration already exists.', 'better-wp-security' ), [ 'status' => \WP_Http::BAD_REQUEST ] );
		$response = \ITSEC_Lib_REST::error_to_response( $error );

		foreach ( $duplicates as $duplicate ) {
			$response->add_link( 'duplicate', rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $duplicate->get_id() ) ) );
		}

		return $response;
	}

	public function update_item( $request ) {
		try {
			$user_group = $this->prepare_item_for_database( $request );

			if ( is_wp_error( $user_group ) ) {
				return $user_group;
			}

			$this->repository->persist( $user_group, [] );
			$request['context'] = 'edit';

			return $this->prepare_item_for_response( $user_group, $request );
		} catch ( WP_Error $e ) {
			return $e->get_error();
		} catch ( \Exception $e ) {
			return new \WP_Error( 'internal_server_error', __( 'An unexpected error occurred.', 'better-wp-security' ), [ 'status' => \WP_Http::INTERNAL_SERVER_ERROR ] );
		}
	}

	public function delete_item_permissions_check( $request ) {
		if ( true !== ( $error = $this->check_group_exists( $request ) ) ) {
			return $error;
		}

		if ( ! current_user_can( 'itsec_delete_user_group', $request['id'] ) ) {
			return new \WP_Error( 'rest_cannot_delete', __( 'Sorry, you are not allowed to delete this user group.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}

	public function delete_item( $request ) {
		try {
			$user_group = $this->repository->get( $request['id'] );
			$this->repository->delete( $user_group );

			return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
		} catch ( User_Group_Not_Found $e ) {
			return new \WP_Error( 'rest_user_group_not_found', $e->getMessage(), [ 'status' => \WP_Http::NOT_FOUND ] );
		}
	}

	protected function prepare_item_for_database( $request ) {
		try {
			if ( isset( $request->get_url_params()['id'] ) ) {
				$user_group = $this->repository->get( $request->get_url_params()['id'] );
			} elseif ( isset( $request['id'] ) ) {
				if ( $this->repository->has( $request['id'] ) ) {
					throw WP_Error::from_code(
						'rest_duplicate_user_group_id',
						sprintf( __( 'A user group already exists with the id \'%s\'.', 'better-wp-security' ), $request['id'] ),
						[ 'status' => \WP_Http::BAD_REQUEST ]
					);
				}

				$user_group = new User_Group( $request['id'] );
			} else {
				$user_group = new User_Group( $this->repository->next_id() );
			}

			if ( isset( $request['users'] ) ) {
				$user_group->set_users( array_map( static function ( $id ) {
					if ( ! $user = get_userdata( $id ) ) {
						throw WP_Error::from_code( 'rest_user_not_found', sprintf( __( 'No user found for %d.', 'better-wp-security' ), $id ), [ 'status' => \WP_Http::BAD_REQUEST ] );
					}

					return $user;
				}, $request['users'] ) );
			}

			if ( isset( $request['roles'] ) ) {
				$user_group->set_roles( $request['roles'] );
			}

			if ( isset( $request['canonical'] ) ) {
				$user_group->set_canonical_roles( $request['canonical'] );
			}

			if ( isset( $request['min_role'] ) ) {
				$user_group->set_min_role( $request['min_role'] );
			}

			if ( isset( $request['label'] ) ) {
				$user_group->set_label( $request['label'] );
			}
		} catch ( WP_Error $e ) {
			return $e->get_error();
		} catch ( Invalid_Argument_Exception $e ) {
			return new \WP_Error( 'rest_invalid_param', $e->getMessage(), [ 'status' => \WP_Http::BAD_REQUEST ] );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'internal_server_error', __( 'An unexpected error occurred.', 'better-wp-security' ), [ 'status' => \WP_Http::INTERNAL_SERVER_ERROR ] );
		}

		if ( ! $user_group->is_configured() ) {
			return new \WP_Error(
				'rest_user_group_not_configured',
				__( 'A user group must have a minimum role, list of roles, or list of users to be created.', 'better-wp-security' ),
				[ 'status' => \WP_Http::BAD_REQUEST ]
			);
		}

		return $user_group;
	}

	public function prepare_item_for_response( $item, $request ) {
		if ( ! $item instanceof User_Group ) {
			return new \WP_REST_Response();
		}

		$fields = $this->get_fields_for_response( $request );
		$data   = [
			'id' => $item->get_id(),
		];

		if ( in_array( 'label', $fields, true ) ) {
			$data['label'] = $item->get_label();
		}

		if ( in_array( 'description', $fields, true ) ) {
			$data['description'] = $item->get_description();
		}

		if ( in_array( 'users', $fields, true ) ) {
			$data['users'] = wp_list_pluck( $item->get_users(), 'ID' );
		}

		if ( in_array( 'roles', $fields, true ) ) {
			$data['roles'] = $item->get_roles();
		}

		if ( in_array( 'canonical', $fields, true ) ) {
			$data['canonical'] = $item->get_canonical_roles();
		}

		if ( in_array( 'min_role', $fields, true ) ) {
			$data['min_role'] = $item->get_min_role();
		}

		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		return $response;
	}

	/**
	 * Check that a user group exists.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return bool|\WP_Error
	 */
	protected function check_group_exists( \WP_REST_Request $request ) {
		if ( $this->repository->has( $request['id'] ) ) {
			return true;
		}

		return new \WP_Error( 'rest_not_found', __( 'Sorry, no user group exists with that id.', 'better-wp-security' ), [ 'status' => \WP_Http::NOT_FOUND ] );
	}

	/**
	 * Prepare the links for each user group.
	 *
	 * @param User_Group $user_group
	 *
	 * @return array
	 */
	public function prepare_links( User_Group $user_group ) {
		$links = [
			'self' => [
				'href' => rest_url( "{$this->namespace}/{$this->rest_base}/{$user_group->get_id()}" ),
			],

			\ITSEC_Lib_REST::get_link_relation( 'user-matchable-settings' ) => [
				'href'       => rest_url( "{$this->namespace}/user-matchable-settings/{$user_group->get_id()}" ),
				'embeddable' => true,
			],
		];

		foreach ( $user_group->get_users() as $user ) {
			$links[ \ITSEC_Lib_REST::get_link_relation( 'user-group-member' ) ][] = [
				'href'       => rest_url( "wp/v2/users/{$user->ID}" ),
				'embeddable' => true
			];
		}

		return $links;
	}

	public function get_item_schema() {
		if ( ! empty( $this->schema ) && ! \ITSEC_Core::is_test_suite( 'wpunit' ) ) {
			return $this->schema;
		}

		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'ithemes-security-user-group',
			'type'       => 'object',
			'properties' => [
				'id'          => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'edit', 'embed' ],
				],
				'label'       => [
					'type'      => 'string',
					'minLength' => 1,
					'required'  => true,
					'context'   => [ 'view', 'edit', 'embed' ],
				],
				'description' => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'edit', 'embed' ],
				],
				'users'       => [
					'type'        => 'array',
					'items'       => [
						'type'    => 'integer',
						'minimum' => 0,
					],
					'uniqueItems' => true,
					'context'     => [ 'view', 'edit' ],
				],
				'roles'       => [
					'type'    => 'array',
					'items'   => [
						'type' => 'string',
						'enum' => array_keys( wp_roles()->get_names() ),
					],
					'context' => [ 'view', 'edit' ],
				],
				'canonical'   => [
					'type'        => 'array',
					'items'       => [
						'type' => 'string',
						'enum' => \ITSEC_Lib_Canonical_Roles::get_canonical_roles( is_multisite() ),
					],
					'arg_options' => [
						'validate_callback' => static function ( $value ) {
							return rest_validate_value_from_schema( $value, [
								'type'  => 'array',
								'items' => [
									'type' => 'string',
									'enum' => \ITSEC_Lib_Canonical_Roles::get_canonical_roles(),
								],
							] );
						},
					],
				],
				'min_role'    => [
					'type'    => 'string',
					'enum'    => array_merge( [ '' ], array_keys( wp_roles()->get_names() ) ),
					'context' => [ 'view', 'edit' ],
				],
			]
		];

		if ( isset( $this->schema ) ) {
			$this->schema = $schema;
		}

		return $schema;
	}

	public function get_collection_params() {
		return [
			'context' => $this->get_context_param( [ 'default' => 'view' ] ),
		];
	}
}
