<?php

/**
 * Class ITSEC_REST_Dashboard_Dashboards_Controller
 */
class ITSEC_REST_Dashboard_Dashboards_Controller extends ITSEC_REST_Dashboard_Controller {

	public function __construct() {
		$this->namespace = 'ithemes-security/v1';
		$this->rest_base = 'dashboards';
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			'schema'        => array( $this, 'get_public_item_schema' ),
			'show_in_index' => false,
		) );

		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array( 'context' => $this->get_context_param( array( 'default' => 'view' ) ) )
			),
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			),
			'schema'        => array( $this, 'get_public_item_schema' ),
			'show_in_index' => false,
		) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_items_permissions_check( $request ) {
		return is_user_logged_in();
	}

	/**
	 * @inheritDoc
	 */
	public function get_items( $request ) {
		$owned  = ITSEC_Dashboard_Util::get_owned_dashboards();
		$shared = ITSEC_Dashboard_Util::get_shared_dashboards();

		$cap = $request['context'] === 'edit' ? 'itsec_edit_dashboard' : 'itsec_view_dashboard';

		$data = array();

		foreach ( $owned as $post ) {
			if ( current_user_can( $cap, $post->ID ) ) {
				$data[ $post->ID ] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $post, $request ) );
			}
		}

		foreach ( $shared as $post ) {
			if ( ! isset( $data[ $post->ID ] ) && current_user_can( $cap, $post->ID ) ) {
				$data[ $post->ID ] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $post, $request ) );
			}
		}

		return new WP_REST_Response( array_values( $data ) );
	}

	/**
	 * @inheritDoc
	 */
	public function get_item_permissions_check( $request ) {

		$id = (int) $request['id'];

		if ( ITSEC_Dashboard::CPT_DASHBOARD !== get_post_type( $id ) ) {
			return new WP_Error( 'rest_not_found', esc_html__( 'Not Found', 'better-wp-security' ), array( 'status' => 404 ) );
		}

		if ( 'edit' === $request['context'] && ! current_user_can( 'itsec_edit_dashboard', $id ) ) {
			return new WP_Error( 'rest_forbidden_context', esc_html__( 'Sorry, you are not allowed to edit this dashboard.', 'better-wp-security' ), array( 'status' => 403 ) );
		}

		if ( ! current_user_can( 'itsec_view_dashboard', $id ) ) {
			return new WP_Error( 'rest_cannot_view', esc_html__( 'Sorry, you do not have permission to view this dashboard.', 'better-wp-security' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_item( $request ) {
		return $this->prepare_item_for_response( get_post( (int) $request['id'] ), $request );
	}

	/**
	 * @inheritDoc
	 */
	public function create_item_permissions_check( $request ) {
		if ( current_user_can( 'itsec_create_dashboards' ) ) {
			return true;
		}

		return new WP_Error( 'rest_cannot_create', esc_html__( 'Sorry, you do not have permission to create dashboards.', 'better-wp-security' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/**
	 * @inheritDoc
	 */
	public function create_item( $request ) {

		$data = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$post_id = wp_insert_post( ITSEC_Lib::slash( $data ), true );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		if ( isset( $request['preset'] ) ) {
			$this->use_layout( $post_id, $request['preset'] );
		}

		$post    = get_post( $post_id );
		$updated = $this->update_additional_fields_for_object( $post, $request );

		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $post, $request );
		$response->header( 'Location', rest_url( "{$this->namespace}/{$this->rest_base}/{$post_id}" ) );
		$response->set_status( WP_Http::CREATED );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function update_item_permissions_check( $request ) {
		$id = (int) $request['id'];

		if ( ITSEC_Dashboard::CPT_DASHBOARD !== get_post_type( $id ) ) {
			return new WP_Error( 'not_found', esc_html__( 'Not Found', 'better-wp-security' ), array( 'status' => 404 ) );
		}

		if ( current_user_can( 'itsec_edit_dashboard', $id ) ) {
			return true;
		}

		return new WP_Error( 'rest_cannot_edit', esc_html__( 'Sorry, you do not have permission to edit this dashboard.', 'better-wp-security' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/**
	 * @inheritDoc
	 */
	public function update_item( $request ) {

		$data = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$post_id = wp_update_post( array_merge( array( 'ID' => (int) $request['id'] ), ITSEC_Lib::slash( $data ) ), true );

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$post    = get_post( $post_id );
		$updated = $this->update_additional_fields_for_object( $post, $request );

		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		$request->set_param( 'context', 'edit' );

		return $this->prepare_item_for_response( $post, $request );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_item_permissions_check( $request ) {
		$id = (int) $request['id'];

		if ( ITSEC_Dashboard::CPT_DASHBOARD !== get_post_type( $id ) ) {
			return new WP_Error( 'not_found', esc_html__( 'Not Found', 'better-wp-security' ), array( 'status' => 404 ) );
		}

		if ( current_user_can( 'itsec_edit_dashboard', $id ) ) {
			return true;
		}

		return new WP_Error( 'rest_cannot_delete', esc_html__( 'Sorry, you do not have permission to delete this dashboard.', 'better-wp-security' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/**
	 * @inheritDoc
	 */
	public function delete_item( $request ) {
		if ( ! wp_delete_post( (int) $request['id'], true ) ) {
			return new WP_Error( 'rest_cannot_delete', __( 'The dashboard cannot be deleted.', 'better-wp-security' ), array( 'status' => 500 ) );
		}

		return new WP_REST_Response( null, WP_Http::NO_CONTENT );
	}

	/**
	 * @inheritDoc
	 */
	protected function prepare_item_for_database( $request ) {
		$for_db = array(
			'post_type'   => ITSEC_Dashboard::CPT_DASHBOARD,
			'post_author' => get_current_user_id(),
			'post_status' => 'publish',
		);

		if ( isset( $request['label'] ) ) {
			$for_db['post_title'] = is_string( $request['label'] ) ? $request['label'] : $request['label']['raw'];
		}

		return $for_db;
	}

	/**
	 * @inheritDoc
	 *
	 * @param WP_Post $item
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = array(
			'id'         => (int) $item->ID,
			'created_by' => (int) $item->post_author,
			'created_at' => mysql_to_rfc3339( $item->post_date_gmt ),
			'label'      => array(
				'raw'      => $item->post_title,
				'rendered' => get_the_title( $item )
			),
		);

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $request['context'] );

		$response = new WP_REST_Response( $data );
		$response->add_link( 'self', rest_url( "{$this->namespace}/{$this->rest_base}/{$item->ID}" ), array(
			'targetHints' => array(
				'allow' => $this->build_allow_target_hints( "/{$this->namespace}/{$this->rest_base}/{$item->ID}", array( 'id' => $item->ID ) )
			),
		) );
		$response->add_link( 'author', rest_url( "wp/v2/users/{$data['created_by']}" ), array( 'embeddable' => true ) );
		$response->add_link( 'https://api.w.org/items', rest_url( "{$this->namespace}/{$this->rest_base}/{$item->ID}/cards" ) );

		foreach ( ITSEC_Dashboard_Util::get_registered_cards() as $card ) {
			$response->add_link(
				'create-form',
				rest_url( "{$this->namespace}/{$this->rest_base}/{$item->ID}/cards/{$card->get_slug()}" ),
				array(
					'title'       => $card->get_label(),
					'targetHints' => array(
						'allow' => current_user_can( 'itsec_edit_dashboard', $item->ID ) ? array( 'POST' ) : array(),
						'link'  => array(
							'<' . rest_url( "{$this->namespace}/dashboard-available-cards/{$card->get_slug()}" ) . '>; rel="about"',
						)
					)
				)
			);
		}

		/**
		 * Fires when the response for a Security Dashboard is being prepared for the REST API.
		 *
		 * @param WP_REST_Response $response
		 * @param WP_Post          $item
		 * @param WP_REST_Request  $request
		 */
		do_action( 'itsec_prepare_dashboard_response', $response, $item, $request );

		return $response;
	}

	/**
	 * Use a preset layout for a new dashboard.
	 *
	 * @param int    $id     Dashboard ID
	 * @param string $layout Layout type to use.
	 */
	private function use_layout( $id, $layout ) {
		if ( $layout === 'default' ) {
			$cards = array_map( function ( ITSEC_Dashboard_Card $card ) {
				$exclude = [
					'fingerprinting',
					'security-summary', // Manually add as first.
				];

				if ( in_array( $card->get_slug(), $exclude, true ) ) {
					return null;
				}

				if ( $card->get_max() !== 1 ) {
					return null;
				}

				return [
					'type' => $card->get_slug(),
				];
			}, ITSEC_Dashboard_Util::get_registered_cards() );
			array_unshift( $cards, [ 'type' => 'security-summary' ] );
			ITSEC_Dashboard_Util::import_cards( $id, array_filter( $cards ), array( 'skip_unknown' => true ) );

			return;
		}

		$layout = sanitize_title( $layout ); // This is set to an enum in the schema, but for additional safety.
		$file   = dirname( dirname( __FILE__ ) ) . "/layouts/{$layout}.json";

		if ( file_exists( $file ) && ( $json = file_get_contents( $file ) ) && false !== ( $cards = json_decode( $json, true ) ) && wp_is_numeric_array( $cards ) ) {
			ITSEC_Dashboard_Util::import_cards( $id, $cards, array( 'skip_unknown' => true ) );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'ithemes-security-dashboard',
			'type'       => 'object',
			'properties' => [
				'id'         => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view', 'edit', 'embed' ],
				],
				'created_by' => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view', 'edit', 'embed' ],
				],
				'created_at' => [
					'type'     => 'string',
					'format'   => 'date-time',
					'readonly' => true,
					'context'  => [ 'view', 'edit' ],
				],
				'label'      => [
					'context' => [ 'view', 'edit', 'embed' ],
					'oneOf'   => [
						[
							'type'      => 'string',
							'minLength' => 1,
							'context'   => [ 'view', 'edit', 'embed' ],
						],
						[
							'context'    => [ 'view', 'edit', 'embed' ],
							'type'       => 'object',
							'properties' => [
								'raw'      => [
									'type'      => 'string',
									'minLength' => 1,
									'context'   => [ 'edit' ],
								],
								'rendered' => [
									'type'     => 'string',
									'readonly' => true,
									'context'  => [ 'view', 'edit', 'embed' ],
								]
							]
						]
					],
				],
				'preset'     => [
					'type'       => 'string',
					'enum'       => [ 'default' ],
					'createOnly' => true,
				]
			],
		];

		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * @inheritDoc
	 */
	public function get_collection_params() {
		return array(
			'context'    => $this->get_context_param( array( 'default' => 'view' ) ),
			'created_by' => array(
				'type'              => 'integer',
				'validate_callback' => 'get_userdata',
			),
		);
	}
}
