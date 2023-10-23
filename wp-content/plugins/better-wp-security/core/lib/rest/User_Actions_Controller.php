<?php

namespace iThemesSecurity\Lib\REST;

use iThemesSecurity\Lib\User_Actions_Background_Process;

class User_Actions_Controller extends \WP_REST_Controller {

	protected $namespace = 'ithemes-security/v1';
	protected $rest_base = 'user-actions';

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => $this->get_endpoint_args_for_item_schema(),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function create_item( $request ) {
		if ( $request['users'] && $request['query'] ) {
			return new \WP_Error(
				'itsec_user_actions_invalid_users',
				__( 'Passing both a list of users and a query of users to apply actions for is not supported.', 'better-wp-security' ),
				[ 'status' => \WP_Http::BAD_REQUEST ]
			);
		}

		$process = User_Actions_Background_Process::create( wp_get_current_user(), $request['actions'], $request['query'] );

		if ( $request->has_param( 'users' ) ) {
			foreach ( $request['users'] as $user ) {
				$process->push_to_queue( $user );
			}
			$process->save();
		} elseif ( $request->has_param( 'query' ) ) {
			$process->push_queried_items( 1 );
		}

		$process->dispatch();

		return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	public function get_item_schema() {
		$actions = [
			'force-logout'   => [
				'type' => 'boolean',
				'enum' => [ true ],
			],
			'reset-password' => [
				'type' => 'boolean',
				'enum' => [ true ],
			],
		];

		if ( current_user_can( 'delete_users' ) ) {
			$actions['delete'] = [
				'type' => 'boolean',
				'enum' => [ true ],
			];
		}

		$schema = [
			'title'      => 'ithemes-security-user-actions',
			'type'       => 'object',
			'properties' => [
				'users'   => [
					'type'  => 'array',
					'items' => [
						'type' => 'integer',
					],
				],
				'query'   => [
					'type'       => 'object',
					'properties' => [],
				],
				'actions' => [
					'type'                 => 'object',
					'additionalProperties' => false,
					'properties'           => $actions,
					'minProperties'        => 1,
					'required'             => true,
				],
			],
		];

		return apply_filters( 'itsec_rest_user_actions_schema', $schema );
	}
}
