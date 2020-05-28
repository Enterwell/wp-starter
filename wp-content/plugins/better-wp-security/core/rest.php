<?php

class ITSEC_REST {
	public function run() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ), 0 );
		add_filter( 'rest_response_link_curies', array( $this, 'register_curie' ) );
		add_filter( 'rest_namespace_index', array( $this, 'modify_index' ) );
		add_filter( 'rest_user_collection_params', [ $this, 'register_global_users_query' ] );
		add_filter( 'rest_user_query', [ $this, 'apply_global_users_query' ], 10, 2 );
	}

	/**
	 * Runs when the REST API is initialized.
	 */
	public function rest_api_init() {
		ITSEC_Modules::load_module_file( 'rest.php', ':active' );
	}

	/**
	 * Register the CURIE to shorten link refs.
	 *
	 * @param array $curies
	 *
	 * @return array
	 */
	public function register_curie( $curies ) {
		ITSEC_Lib::load( 'rest' );

		$curies[] = array(
			'name'      => 'ithemes-security',
			'href'      => ITSEC_Lib_REST::LINK_REL . '{rel}',
			'templated' => true,
		);

		return $curies;
	}

	/**
	 * Modify the ithemes-security/v1 index to include some additional global information we need.
	 *
	 * @param WP_REST_Response $response
	 *
	 * @return WP_REST_Response
	 */
	public function modify_index( $response ) {
		if (
			ITSEC_Core::current_user_can_manage() ||
			current_user_can( 'create_users' ) ||
			current_user_can( 'edit_users' ) ||
			current_user_can( 'promote_users' )
		) {
			$roles = [];

			foreach ( wp_roles()->get_names() as $role => $label ) {
				$roles[ $role ] = [
					'label'     => translate_user_role( $label ),
					'canonical' => ITSEC_Lib_Canonical_Roles::get_canonical_role_from_role( $role ),
				];
			}

			$response->data['roles'] = $roles;
		}

		$response->data['supports'] = apply_filters( 'itsec_rest_supports', [] );

		return $response;
	}

	/**
	 * Registers the "itsec_global" query parameter for the users endpoint.
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function register_global_users_query( $params ) {
		if ( is_multisite() ) {
			$params['itsec_global'] = [
				'description' => __( 'Return results for users across the entire network, not just the current site.', 'better-wp-security' ),
				'type'        => 'boolean',
				'default'     => false,
			];
		}

		return $params;
	}

	/**
	 * Applies the "itsec_global" query parameter.
	 *
	 * @param array           $prepared_args
	 * @param WP_REST_Request $request
	 *
	 * @return array
	 */
	public function apply_global_users_query( $prepared_args, $request ) {
		if ( is_multisite() && $request['itsec_global'] && current_user_can( 'manage_network_users' ) ) {
			$prepared_args['blog_id'] = null;
		}

		return $prepared_args;
	}
}
