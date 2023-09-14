<?php

use iThemesSecurity\Ban_Hosts;
use iThemesSecurity\Lib\REST\Modules_Controller;
use iThemesSecurity\Lib\REST\Settings_Controller;
use iThemesSecurity\Lib\REST\Site_Types_Controller;
use iThemesSecurity\Lib\REST\Tools_Controller;

class ITSEC_REST {
	public function run() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ), 0 );
		add_filter( 'rest_response_link_curies', array( $this, 'register_curie' ) );
		add_filter( 'rest_index', array( $this, 'modify_global_index' ) );
		add_filter( 'rest_namespace_index', array( $this, 'modify_index' ), 10, 2 );
		add_filter( 'rest_user_collection_params', [ $this, 'register_global_users_query' ] );
		add_filter( 'rest_user_query', [ $this, 'apply_global_users_query' ], 10, 2 );
		add_filter( 'rest_request_from_url', [ $this, 'retain_auth_header_from_embeds' ] );
		add_filter( 'rest_avatar_sizes', [ $this, 'add_avatar_size' ] );
		add_filter( 'rest_allowed_cors_headers', [ $this, 'add_allowed_cors_headers' ] );

		if ( ! ITSEC_Lib::is_wp_version_at_least( '5.6', true ) ) {
			add_filter( 'itsec_filter_apache_server_config_modification', [ $this, 'add_htaccess_authorization_header' ] );
			add_filter( 'itsec_filter_litespeed_server_config_modification', [ $this, 'add_htaccess_authorization_header' ] );
		}
	}

	/**
	 * Runs when the REST API is initialized.
	 */
	public function rest_api_init() {
		ITSEC_Modules::load_module_file( 'rest.php', ':active' );
		ITSEC_Modules::get_container()->get( Ban_Hosts\REST::class )->register_routes();
		ITSEC_Modules::get_container()->get( Modules_Controller::class )->register_routes();
		ITSEC_Modules::get_container()->get( Settings_Controller::class )->register_routes();
		ITSEC_Modules::get_container()->get( Site_Types_Controller::class )->register_routes();
		ITSEC_Modules::get_container()->get( Tools_Controller::class )->register_routes();

		foreach ( ITSEC_Modules::get_container()->get( 'rest.controllers' ) as $controller ) {
			$controller->register_routes();
		}

		register_rest_route( 'ithemes-security/rpc', 'discover', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'discover' ],
			'permission_callback' => 'ITSEC_Core::current_user_can_manage',
			'args'                => [
				'url' => [
					'type'     => 'string',
					'format'   => 'uri',
					'required' => true,
				],
			]
		] );
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
	 * Modifies the global `/wp-json` index.
	 *
	 * @param WP_REST_Response $response
	 *
	 * @return WP_REST_Response
	 */
	public function modify_global_index( $response ) {
		$response->data['multisite'] = is_multisite();

		return $response;
	}

	/**
	 * Modify the ithemes-security/v1 index to include some additional global information we need.
	 *
	 * @param WP_REST_Response $response
	 * @param WP_REST_Request  $request
	 *
	 * @return WP_REST_Response
	 */
	public function modify_index( $response, $request ) {
		if ( $request['namespace'] !== 'ithemes-security/v1' ) {
			return $response;
		}

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

		if ( ITSEC_Core::current_user_can_manage() ) {
			$response->data['requirements_info'] = ITSEC_Lib::get_requirements_info();
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

	/**
	 * Retain the authorization header when doing internal embed requests.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Request
	 */
	public function retain_auth_header_from_embeds( $request ) {
		$headers = rest_get_server()->get_headers( $_SERVER );

		if ( isset( $headers['AUTHORIZATION'] ) && 0 === strpos( $request->get_route(), '/ithemes-security/v1/' ) ) {
			$request->add_header( 'Authorization', $headers['AUTHORIZATION'] );
		}

		return $request;
	}

	/**
	 * Adds larger avatar sizes to the REST API responses.
	 *
	 * @param int[] $sizes The existing sizes.
	 *
	 * @return array
	 */
	public function add_avatar_size( $sizes ) {
		$sizes[] = 128;

		return $sizes;
	}

	/**
	 * Adds the HTTP 1.0 compat header to the list of CORS request headers.
	 *
	 * @param array $headers
	 *
	 * @return array
	 */
	public function add_allowed_cors_headers( $headers ) {
		if ( ! in_array( 'X-HTTP-Method-Override', $headers, true ) ) {
			$headers[] = 'X-HTTP-Method-Override';
		}

		return $headers;
	}

	/**
	 * Discovers the REST API for a given host.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function discover( WP_REST_Request $request ) {
		$head = wp_safe_remote_head( $request['url'], [
			'redirection' => 5,
		] );

		if ( is_wp_error( $head ) ) {
			return new WP_Error(
				'itsec.discover.cannot-connect',
				wp_sprintf( __( 'Cannot connect to site: %l.', 'better-wp-security' ), ITSEC_Lib::get_error_strings( $head ) ),
				[ 'status' => WP_Http::INTERNAL_SERVER_ERROR ]
			);
		}

		$header = wp_remote_retrieve_header( $head, 'Link' );

		if ( ! $header ) {
			return new WP_Error(
				'itsec.discover.missing-link-header',
				__( 'No Link header was found.', 'better-wp-security' ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		$rest_url = '';
		$parsed   = ITSEC_Lib::parse_header_with_attributes( $header );

		foreach ( $parsed as $url => $attributes ) {
			foreach ( $attributes as $attribute => $value ) {
				if ( 'rel' === $attribute && 'https://api.w.org/' === $value ) {
					$rest_url = $url;
					break 2;
				}
			}
		}

		if ( ! $rest_url ) {
			return new WP_Error(
				'itsec.discover.invalid-link-header',
				__( 'Could not find a REST API URL in the Link header.', 'better-wp-security' ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		$index = wp_safe_remote_get( add_query_arg( [
			'_fields' => 'name,description,url,home,namespaces,authentication,_links,_embedded',
			'_embed'  => 'wp:featuredmedia',
		], $rest_url ) );

		if ( is_wp_error( $index ) ) {
			return new WP_Error(
				'itsec.discover.index.cannot-connect',
				wp_sprintf( __( 'Cannot connect to index: %l.', 'better-wp-security' ), ITSEC_Lib::get_error_strings( $index ) ),
				[ 'status' => WP_Http::INTERNAL_SERVER_ERROR ]
			);
		}

		$status = wp_remote_retrieve_response_code( $index );

		if ( $status !== 200 ) {
			return new WP_Error(
				'itsec.discover.index.non-200',
				sprintf( __( 'REST API index returned a non-200 status code (%d).', 'better-wp-security' ), $status ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		$body = wp_remote_retrieve_body( $index );

		if ( ! $body || ! $data = json_decode( $body, true ) ) {
			return new WP_Error(
				'itsec.discover.index.empty',
				__( 'REST API index returned no data.', 'better-wp-security' ),
				[ 'status' => WP_HTTP::BAD_REQUEST ]
			);
		}

		$itsec_index = wp_safe_remote_get( ITSEC_Lib_REST::rest_url(
			$rest_url,
			'ithemes-security/v1'
		) );

		if ( is_wp_error( $itsec_index ) ) {
			return new WP_Error(
				'itsec.discover.itsec-index-cannot-connect',
				wp_sprintf( __( 'Cannot connect to index: %l.', 'better-wp-security' ), ITSEC_Lib::get_error_strings( $itsec_index ) ),
				[ 'status' => WP_Http::INTERNAL_SERVER_ERROR ]
			);
		}

		$status = wp_remote_retrieve_response_code( $itsec_index );

		if ( $status !== 200 ) {
			return new WP_Error(
				'itsec.discover.itsec-index.non-200',
				sprintf( __( 'iThemes Security REST API index returned a non-200 status code (%d).', 'better-wp-security' ), $status ),
				[ 'status' => WP_Http::BAD_REQUEST ]
			);
		}

		$body = wp_remote_retrieve_body( $itsec_index );

		if ( ! $body || ! $itsec_data = json_decode( $body, true ) ) {
			return new WP_Error(
				'itsec.discover.itsec-index.empty',
				__( 'iThemes Security REST API index returned no data.', 'better-wp-security' ),
				[ 'status' => WP_HTTP::BAD_REQUEST ]
			);
		}

		return new WP_REST_Response( [
			'url'   => $rest_url,
			'index' => $data,
			'itsec' => $itsec_data,
		] );
	}

	public function add_htaccess_authorization_header( $rules ) {
		$rules .= "\n";
		$rules .= "\t# " . __( 'Pass through Authorization header.', 'better-wp-security' ) . "\n";
		$rules .= <<<'APACHE'
	<IfModule mod_rewrite.c>
		RewriteEngine On
		RewriteCond %{HTTP:Authorization} ^(.*)
		RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
	</IfModule>
APACHE;
		$rules .= "\n";

		return $rules;
	}
}
