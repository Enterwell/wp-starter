<?php

namespace iThemesSecurity\Lib\REST;

use iThemesSecurity\Lib\Tools\Tool;
use iThemesSecurity\Lib\Tools\Tools_Registry;
use iThemesSecurity\Lib\Tools\Tools_Runner;

final class Tools_Controller extends \WP_REST_Controller {

	/** @var Tools_Registry */
	private $registry;

	/** @var Tools_Runner */
	private $runner;

	/**
	 * Tools_Controller constructor.
	 *
	 * @param Tools_Registry $registry
	 * @param Tools_Runner   $runner
	 */
	public function __construct( Tools_Registry $registry, Tools_Runner $runner ) {
		$this->registry = $registry;
		$this->runner   = $runner;

		$this->namespace = 'ithemes-security/v1';
		$this->rest_base = 'tools';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		register_rest_route( $this->namespace, $this->rest_base . '/(?P<tool>[\w\-]+)', [
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
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'run_tool' ],
				'permission_callback' => [ $this, 'run_tool_permissions_check' ],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_items( $request ) {
		$items = [];

		foreach ( $this->registry->get_tools() as $tool ) {
			$items[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $tool, $request )
			);
		}

		return rest_ensure_response( $items );
	}

	public function get_item_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_item( $request ) {
		$slug = $request['tool'];

		if ( ! $this->registry->is_registered( $slug ) ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Tool not found.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$tool = $this->registry->get_tool( $slug );

		return $this->prepare_item_for_response( $tool, $request );
	}

	public function update_item_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function update_item( $request ) {
		$slug = $request['tool'];

		if ( ! $this->registry->is_registered( $slug ) ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Tool not found.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$tool = $this->registry->get_tool( $slug );

		if ( $request->has_param( 'enabled' ) ) {
			if ( $request['enabled'] ) {
				$toggled = $this->runner->enable_tool( $tool );
			} else {
				$toggled = $this->runner->disable_tool( $tool );
			}

			if ( is_wp_error( $toggled ) ) {
				return $toggled;
			}
		}

		return $this->prepare_item_for_response( $tool, $request );
	}

	public function run_tool( \WP_REST_Request $request ) {
		$slug = $request['tool'];

		if ( ! $this->registry->is_registered( $slug ) ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Tool not found.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$tool = $this->registry->get_tool( $slug );
		$form = $request->get_json_params() ?: $request->get_body_params();

		return $this->runner->run_tool( $tool, $form )->as_rest_response();
	}

	public function run_tool_permissions_check( \WP_REST_Request $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	/**
	 * Prepares a tool for the REST API response.
	 *
	 * @param Tool             $item
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data = [
			'slug'        => $item->get_slug(),
			'module'      => $item->get_module(),
			'title'       => $item->get_title(),
			'description' => $item->get_description(),
			'help'        => $item->get_help(),
			'keywords'    => $item->get_keywords(),
			'available'   => $item->is_available(),
			'condition'   => $item->get_condition() ?: null,
			'toggleable'  => $item->is_toggleable(),
			'schedule'    => $item->get_schedule(),
			'form'        => $item->get_form() ?: null,
		];

		if ( $item->is_toggleable() ) {
			$data['enabled'] = $this->runner->is_enabled( $item );
		}

		$response = new \WP_REST_Response( $data );
		$response->add_link( 'self', rest_url( sprintf(
			'%s/%s/%s',
			$this->namespace,
			$this->rest_base,
			$item->get_slug()
		) ) );

		return $response;
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'title'      => 'ithemes-security-tool',
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'type'       => 'object',
			'properties' => [
				'slug'        => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'module'      => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'title'       => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'description' => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'help'        => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'keywords'    => [
					'type'     => 'array',
					'items'    => [
						'type' => 'string',
					],
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'available'   => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'condition'   => [
					'type'     => [ 'object', 'null' ],
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'toggleable'  => [
					'type'     => 'boolean',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'enabled'     => [
					'type'    => 'boolean',
					'context' => [ 'view', 'edit', 'embed' ],
				],
				'schedule'    => [
					'type'     => 'string',
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
				'form'        => [
					'type'     => [ 'object', 'null' ],
					'context'  => [ 'view', 'edit', 'embed' ],
					'readonly' => true,
				],
			],
		];

		return $this->schema;
	}
}
