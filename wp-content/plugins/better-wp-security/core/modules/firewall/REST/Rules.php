<?php

namespace iThemesSecurity\Modules\Firewall\REST;

use iThemesSecurity\Modules\Firewall\Rules\Repository;
use iThemesSecurity\Modules\Firewall\Rules\Rule;
use iThemesSecurity\Modules\Firewall\Rules\Rules_Options;

class Rules extends \WP_REST_Controller {

	/** @var Repository */
	private $repository;

	protected $namespace = 'ithemes-security/v1';
	protected $rest_base = 'firewall/rules';

	public function __construct( Repository $repository ) { $this->repository = $repository; }

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => $this->get_collection_params(),
			],
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'create_item' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => $this->get_endpoint_args_for_item_schema(),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => [
					'id'      => [
						'type' => 'integer',
					],
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
				],
			],
			[
				'methods'             => 'PUT',
				'callback'            => [ $this, 'update_item' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => $this->get_endpoint_args_for_item_schema( 'PUT' ),
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete_item' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => [
					'id' => [
						'type' => 'integer',
					],
				],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function get_items( $request ) {
		$options = ( new Rules_Options() )
			->paginate( $request['per_page'], $request['page'] );

		if ( $request['search'] ) {
			$options->set_search( $request['search'] );
		}

		if ( $request['provider'] ) {
			$options->set_providers( $request['provider'] );
		}

		if ( $request['vulnerability'] ) {
			$options->set_vulnerabilities( $request['vulnerability'] );
		}

		if ( $request->has_param( 'paused' ) ) {
			$options->set_paused( $request['paused'] );
		}

		$rules        = $this->repository->get_rules( $options );
		$count_result = $this->repository->count_rules( $options );

		if ( ! $rules->is_success() ) {
			return $rules->as_rest_response();
		}

		$data = [];

		foreach ( $rules->get_data() as $rule ) {
			$data[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $rule, $request )
			);
		}

		$response = rest_ensure_response( $data );

		if ( $count_result->is_success() ) {
			\ITSEC_Lib_REST::paginate(
				$request,
				$response,
				$count_result->get_data(),
				$this->namespace . '/' . $this->rest_base
			);
		}

		return $response;
	}

	public function get_item( $request ) {
		$rule = $this->get_rule( $request );

		if ( is_wp_error( $rule ) ) {
			return $rule;
		}

		return $this->prepare_item_for_response( $rule, $request );
	}

	public function create_item( $request ) {
		$rule = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $rule ) ) {
			return $rule;
		}

		$persisted = $this->repository->persist( $rule );

		if ( ! $persisted->is_success() ) {
			return $persisted->as_rest_response();
		}

		$request['context'] = 'edit';

		$response = $this->prepare_item_for_response( $persisted->get_data(), $request );
		$response->set_status( \WP_Http::CREATED );
		$response->header( 'Location', rest_url( sprintf(
			'%s/%s/%d',
			$this->namespace,
			$this->rest_base,
			$persisted->get_data()->get_id()
		) ) );

		return $response;
	}

	public function update_item( $request ) {
		$rule = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $rule ) ) {
			return $rule;
		}

		$persisted = $this->repository->persist( $rule );

		if ( ! $persisted->is_success() ) {
			return $persisted->as_rest_response();
		}

		$request['context'] = 'edit';

		return $this->prepare_item_for_response( $rule, $request );
	}

	public function delete_item( $request ) {
		$rule = $this->get_rule( $request );

		if ( is_wp_error( $rule ) ) {
			return $rule;
		}

		$deleted = $this->repository->delete( $rule );

		if ( ! $deleted->is_success() ) {
			return $deleted->as_rest_response();
		}

		return new \WP_REST_Response( null, \WP_Http::NO_CONTENT );
	}

	/**
	 * Prepares a rule for response.
	 *
	 * @param Rule             $item
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = [
			'id'            => $item->get_id(),
			'provider'      => $item->get_provider(),
			'provider_ref'  => $item->get_provider_ref(),
			'name'          => $item->get_name(),
			'vulnerability' => $item->get_vulnerability(),
			'config'        => $item->get_config(),
			'created_at'    => \ITSEC_Lib::to_rest_date( $item->get_created_at() ),
			'paused_at'     => $item->get_paused_at() ? \ITSEC_Lib::to_rest_date( $item->get_paused_at() ) : null,
		];

		$data     = $this->filter_response_by_context( $data, $request['context'] );
		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		return $response;
	}

	/**
	 * Prepares a list of links for a rule.
	 *
	 * @param Rule $rule
	 *
	 * @return array[]
	 */
	protected function prepare_links( Rule $rule ): array {
		return [
			'self' => [
				'href' => rest_url( sprintf(
					'%s/%s/%d',
					$this->namespace,
					$this->rest_base,
					$rule->get_id()
				) ),
			],
		];
	}

	/**
	 * Prepares a firewall rule for storage.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return Rule|WP_Error
	 */
	protected function prepare_item_for_database( $request ) {
		if ( $request->get_method() === 'PUT' ) {
			$rule = $this->get_rule( $request );

			if ( is_wp_error( $rule ) ) {
				return $rule;
			}
		} else {
			$rule = Rule::create(
				'user',
				'',
				'',
				'',
				[]
			);
		}

		if ( $request['name'] ) {
			$rule->set_name( $request['name'] );
		}

		if ( $request['config'] ) {
			$rule->set_config( $request['config'] );
		}

		if ( $request->has_param( 'paused_at' ) ) {
			if ( $request['paused_at'] && ! $rule->is_paused() ) {
				$rule->pause();
			} elseif ( ! $request['paused_at'] ) {
				$rule->resume();
			}
		}

		return $rule;
	}

	/**
	 * Gets the requested rule for a request.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return Rule|\WP_Error
	 */
	protected function get_rule( \WP_REST_Request $request ) {
		$found = $this->repository->find( $request['id'] );

		if ( ! $found->is_success() ) {
			return $found->get_error();
		}

		if ( ! $found->get_data() ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Firewall rule not found.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		return $found->get_data();
	}

	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		$params['provider'] = [
			'type'  => 'array',
			'items' => [
				'type' => 'string',
			],
		];

		$params['vulnerability'] = [
			'type'  => 'array',
			'items' => [
				'type' => 'string',
			],
		];

		$params['paused'] = [
			'type' => 'boolean',
		];

		return $params;
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'type'       => 'object',
			'properties' => [
				'id'            => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view', 'edit', 'embed' ],
				],
				'provider'      => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'provider_ref'  => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit' ],
					'readonly' => true,
				],
				'name'          => [
					'type'      => 'string',
					'minLength' => 1,
					'required'  => true,
					'context'   => [ 'view', 'edit', 'embed' ],
				],
				'vulnerability' => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit' ],
					'readonly' => true,
				],
				'config'        => [
					'type'       => 'object',
					'properties' => [
						'rules'       => [
							'type'        => 'array',
							'items'       => [
								'type'       => 'object',
								'properties' => [
									'parameter' => [
										'type'     => [ 'string', 'array' ],
										'required' => true,
										'items'    => [
											'type' => 'string',
										],
									],
								],
							],
							'required'    => true,
							'minItems'    => 1,
							'uniqueItems' => true,
						],
						'type'        => [
							'type'     => 'string',
							'enum'     => [ 'BLOCK', 'LOG', 'REDIRECT', 'WHITELIST' ],
							'required' => true,
						],
						'type_params' => [
							'type'   => 'string',
							'format' => 'uri',
						],
					],
					'context'    => [ 'view', 'edit' ],
					'required'   => true,
				],
				'created_at'    => [
					'type'     => 'string',
					'format'   => 'date-time',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'paused_at'     => [
					'type'    => [ 'string', 'null' ],
					'format'  => 'date-time',
					'context' => [ 'view', 'edit', 'embed' ],
				],
			],
		];

		return $this->schema;
	}
}
