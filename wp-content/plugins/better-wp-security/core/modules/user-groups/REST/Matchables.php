<?php

namespace iThemesSecurity\User_Groups\REST;

use iThemesSecurity\User_Groups\Matchable;
use iThemesSecurity\User_Groups\Matchables_Source;
use iThemesSecurity\User_Groups\User_Group;

class Matchables extends \WP_REST_Controller {

	/** @var Matchables_Source */
	private $source;

	/**
	 * Matchables constructor.
	 *
	 * @param Matchables_Source $source
	 */
	public function __construct( Matchables_Source $source ) {
		$this->source    = $source;
		$this->namespace = 'ithemes-security/v1';
		$this->rest_base = 'user-matchables';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_items' ],
			'permission_callback' => [ $this, 'get_items_permissions_check' ],
			'schema'              => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function get_items_permissions_check( $request ) {
		if ( ! current_user_can( 'itsec_list_user_groups' ) ) {
			return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you are not allowed to list user matchables.', 'better-wp-security' ), [ 'status' => rest_authorization_required_code() ] );
		}

		return true;
	}

	public function get_items( $request ) {
		$data = [];

		foreach ( $this->source->all() as $matchable ) {
			$data[] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $matchable, $request ) );
		}

		return new \WP_REST_Response( $data );
	}

	public function prepare_item_for_response( $item, $request ) {
		if ( ! $item instanceof Matchable ) {
			return new \WP_REST_Response();
		}

		$data = [
			'id'    => $item->get_id(),
			'label' => $item->get_label(),
			'type'  => $item instanceof User_Group ? 'user-group' : 'meta',
		];

		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		return $response;
	}

	/**
	 * Prepare the links for each user group.
	 *
	 * @param Matchable $matchable
	 *
	 * @return array
	 */
	public function prepare_links( Matchable $matchable ) {
		$links = [];

		if ( $matchable instanceof User_Group ) {
			$links['self'] = [
				'href'       => add_query_arg( 'context', 'view', rest_url( "{$this->namespace}/user-groups/{$matchable->get_id()}" ) ),
				'embeddable' => true,
			];
		}

		$links[ \ITSEC_Lib_REST::get_link_relation( 'user-matchable-settings' ) ] = [
			'href'       => rest_url( "{$this->namespace}/user-matchable-settings/{$matchable->get_id()}" ),
			'embeddable' => true,
		];

		return $links;
	}

	public function get_item_schema() {
		if ( ! empty( $this->schema ) && ! \ITSEC_Core::is_test_suite( 'wpunit' ) ) {
			return $this->schema;
		}

		$schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'ithemes-security-user-matchable',
			'type'       => 'object',
			'properties' => [
				'id'    => [
					'type' => 'string',
				],
				'label' => [
					'type' => 'string',
				],
			],
			'links'      => [
				[
					'rel'        => 'self',
					'href'       => rest_url( sprintf( '%s/user-groups/{id}', $this->rest_base ) ),
					'hrefSchema' => [
						'type'       => 'object',
						'properties' => [
							'id' => [
								'type' => 'string',
							],
						],
					]
				]
			]
		];

		if ( isset( $this->schema ) ) {
			$this->schema = $schema;
		}

		return $schema;
	}
}
