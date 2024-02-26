<?php

namespace iThemesSecurity\Site_Scanner\REST;

use iThemesSecurity\Site_Scanner\Repository\Vulnerabilities_Options;
use iThemesSecurity\Site_Scanner\Repository\Vulnerabilities_Repository;
use iThemesSecurity\Site_Scanner\Vulnerability;
use iThemesSecurity\Site_Scanner\Vulnerability_Fixer;

class Vulnerabilities extends \WP_REST_Controller {

	protected $namespace = 'ithemes-security/v1';
	protected $rest_base = 'site-scanner/vulnerabilities';
	private $repository;
	private $fixer;

	public function __construct(
		Vulnerabilities_Repository $repository,
		Vulnerability_Fixer $fixer
	) {
		$this->repository = $repository;
		$this->fixer      = $fixer;
	}

	public function register_routes(): void {
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
				'args'                => [
					'id'      => [
						'type' => 'string',
					],
					'context' => $this->get_context_param( [ 'default' => 'view' ] ),
				],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\w-]+)/mute', [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'mute_issue' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
				'args'                => [
					'id' => [
						'type' => 'string',
					],
				],
			],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\w-]+)/unmute', [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'unmute_issue' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
				'args'                => [
					'id' => [
						'type' => 'string',
					],
				],
			],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\w-]+)/fix', [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'fix_issue' ],
				'permission_callback' => [ $this, 'fix_issue_permissions_check' ],
				'args'                => [
					'id' => [
						'type' => 'string',
					],
				],
			],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\w-]+)/deactivate-software', [
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'deactivate_software' ],
				'permission_callback' => [ $this, 'deactivate_software_permissions_check' ],
				'args'                => [
					'id' => [
						'type' => 'string',
					],
				],
			],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_items( $request ) {
		$options = ( new Vulnerabilities_Options() )
			->paginate( $request['per_page'], $request['page'] );

		if ( $request['resolution'] ) {
			$resolution = array_map( function ( $resolution ) {
				if ( $resolution === 'unresolved' ) {
					return '';
				}

				return $resolution;
			}, $request['resolution'] );

			$options->set_resolutions( $resolution );
		}

		if ( $request['software_type'] ) {
			$options->set_types( $request['software_type'] );
		}

		if ( $request['first_seen_after'] ) {
			$options->set_first_seen_after( new \DateTimeImmutable( $request['first_seen_after'], new \DateTimeZone( 'UTC' ) ) );
		}

		if ( $request['first_seen_before'] ) {
			$options->set_first_seen_before( new \DateTimeImmutable( $request['first_seen_before'], new \DateTimeZone( 'UTC' ) ) );
		}

		if ( $request['last_seen_after'] ) {
			$options->set_last_seen_after( new \DateTimeImmutable( $request['last_seen_after'], new \DateTimeZone( 'UTC' ) ) );
		}

		if ( $request['last_seen_before'] ) {
			$options->set_last_seen_before( new \DateTimeImmutable( $request['last_seen_before'], new \DateTimeZone( 'UTC' ) ) );
		}

		$vulnerabilities = $this->repository->get_vulnerabilities( $options );
		$count_result    = $this->repository->count_vulnerabilities( $options );

		if ( ! $vulnerabilities->is_success() ) {
			return $vulnerabilities->as_rest_response();
		}

		$patchable_ids = [];

		if ( $request['patchable'] ) {
			$vulnerability_ids = array_map( function ( Vulnerability $vulnerability ) {
				return $vulnerability->get_id();
			}, $vulnerabilities->get_data() );

			$patchable = \ITSEC_Site_Scanner_API::get_available_firewall_rules( $vulnerability_ids );

			if ( ! $patchable->is_success() ) {
				return $patchable->as_rest_response();
			}

			$patchable_ids = $patchable->get_data();
		}

		$data = [];

		foreach ( $vulnerabilities->get_data() as $vulnerability ) {
			if ( $request['patchable'] === true && ! in_array( $vulnerability->get_id(), $patchable_ids, true ) ) {
				continue;
			} elseif ( $request['patchable'] === false && in_array( $vulnerability->get_id(), $patchable_ids, true ) ) {
				continue;
			}

			$data[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $vulnerability, $request )
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

	public function get_item_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_item( $request ) {
		$vulnerability = $this->get_vulnerability( $request );

		if ( is_wp_error( $vulnerability ) ) {
			return $vulnerability;
		}

		return $this->prepare_item_for_response( $vulnerability, $request );
	}

	public function mute_issue( \WP_REST_Request $request ) {
		$vulnerability = $this->get_vulnerability( $request );

		if ( is_wp_error( $vulnerability ) ) {
			return $vulnerability;
		}

		$vulnerability->muted();
		$saved = $this->repository->persist( $vulnerability );

		if ( $saved->is_success() ) {
			return $this->prepare_item_for_response( $vulnerability, $request );
		}

		return $saved->as_rest_response();
	}

	public function unmute_issue( \WP_REST_Request $request ) {
		$vulnerability = $this->get_vulnerability( $request );

		if ( is_wp_error( $vulnerability ) ) {
			return $vulnerability;
		}

		if ( ! $vulnerability->is_muted() ) {
			return $this->prepare_item_for_response( $vulnerability, $request );
		}

		$vulnerability->unmute();
		$saved = $this->repository->persist( $vulnerability );

		if ( $saved->is_success() ) {
			return $this->prepare_item_for_response( $vulnerability, $request );
		}

		return $saved->as_rest_response();
	}

	public function fix_issue_permissions_check( \WP_REST_Request $request ): bool {
		if ( ! \ITSEC_Core::current_user_can_manage() ) {
			return false;
		}

		$vulnerability = $this->get_vulnerability( $request );

		if ( is_wp_error( $vulnerability ) ) {
			return false;
		}

		if ( ! $this->fixer->can_user_fix( wp_get_current_user(), $vulnerability->as_issue() ) ) {
			return false;
		}

		return true;
	}

	public function fix_issue( \WP_REST_Request $request ) {
		$vulnerability = $this->get_vulnerability( $request );

		if ( is_wp_error( $vulnerability ) ) {
			return $vulnerability;
		}

		$fixed = $this->fixer->fix( $vulnerability->as_issue() );

		if ( is_wp_error( $fixed ) ) {
			return $fixed;
		}

		if ( \ITSEC_Modules::is_active( 'version-management' ) ) {
			$vulnerability = $this->get_vulnerability( $request );
		} else {
			$vulnerability->updated( false, wp_get_current_user() );
			$this->repository->persist( $vulnerability );
		}

		return $this->prepare_item_for_response( $vulnerability, $request );
	}

	public function deactivate_software_permissions_check( \WP_REST_Request $request ): bool {
		if ( ! \ITSEC_Core::current_user_can_manage() ) {
			return false;
		}

		$vulnerability = $this->get_vulnerability( $request );

		if ( is_wp_error( $vulnerability ) ) {
			return false;
		}

		if ( $vulnerability->get_software_type() !== Vulnerability::T_PLUGIN ) {
			return false;
		}

		if ( ! current_user_can( 'deactivate_plugin', $vulnerability->get_plugin_file() ) ) {
			return false;
		}

		return true;
	}

	public function deactivate_software( \WP_REST_Request $request ) {
		$vulnerability = $this->get_vulnerability( $request );

		if ( is_wp_error( $vulnerability ) ) {
			return $vulnerability;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		deactivate_plugins( $vulnerability->get_plugin_file() );
		$fresh = $this->get_vulnerability( $request );

		return $this->prepare_item_for_response( $fresh, $request );
	}

	protected function get_vulnerability( \WP_REST_Request $request ) {
		$found = $this->repository->find( $request['id'] );

		if ( ! $found->is_success() ) {
			return $found->get_error();
		}

		if ( ! $found->get_data() ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Vulnerability not found.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		return $found->get_data();
	}

	/**
	 * Prepares a vulnerability for output.
	 *
	 * @param Vulnerability    $item
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$data     = [
			'id'              => $item->get_id(),
			'software'        => [
				'type'  => [
					'slug'  => $item->get_software_type(),
					'label' => $item->get_software_type_label(),
				],
				'slug'  => $item->get_software_slug(),
				'label' => $item->get_software_label(),
			],
			'details'         => $item->get_details(),
			'first_seen'      => $item->get_first_seen()->format( 'Y-m-d\TH:i:s' ),
			'first_seen_diff' => human_time_diff( $item->get_first_seen()->getTimestamp() ),
			'last_seen'       => $item->get_last_seen()->format( 'Y-m-d\TH:i:s' ),
			'last_seen_diff'  => human_time_diff( $item->get_last_seen()->getTimestamp() ),
			'resolved_at'     => $item->get_resolved_at()
				? $item->get_resolved_at()->format( 'Y-m-d\TH:i:s' )
				: null,
			'resolution'      => [
				'slug'        => $item->get_resolution(),
				'label'       => $item->get_resolution_label(),
				'description' => $item->get_resolution_description(),
			],
			'resolved_by'     => $item->get_resolved_by()
				? [
					'id'   => $item->get_resolved_by()->ID,
					'name' => $item->get_resolved_by()->display_name,
				]
				: null,
		];
		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		return $response;
	}

	protected function prepare_links( Vulnerability $vulnerability ): array {
		$base = sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $vulnerability->get_id() );

		$links = [
			'self'       => [
				'href' => rest_url( $base ),
			],
			'collection' => [
				'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
			],
		];

		$issue = $vulnerability->as_issue();

		if (
			! $vulnerability->is_updated() &&
			$this->fixer->is_fixable( $issue ) &&
			$this->fixer->can_user_fix( wp_get_current_user(), $issue )
		) {
			$links[ \ITSEC_Lib_REST::get_link_relation( 'fix-vulnerability' ) ] = [
				'href'  => rest_url( $base . '/fix' ),
				'title' => $this->fixer->get_fix_label( $issue ),
			];
		}

		if (
			! $vulnerability->is_updated() &&
			$vulnerability->is_software_active() &&
			Vulnerability::T_PLUGIN === $vulnerability->get_software_type() &&
			current_user_can( 'deactivate_plugin', $vulnerability->get_plugin_file() )
		) {
			$links[ \ITSEC_Lib_REST::get_link_relation( 'deactivate-vulnerable-software' ) ] = [
				'href'          => rest_url( $base . '/deactivate-software' ),
				'title'         => __( 'Deactivate Plugin', 'better-wp-security' ),
				'isDestructive' => true,
			];
		}

		if ( ! $vulnerability->is_resolved() || $vulnerability->is_deactivated() ) {
			$links[ \ITSEC_Lib_REST::get_link_relation( 'mute-vulnerability' ) ] = [
				'href'  => rest_url( $base . '/mute' ),
				'title' => __( 'Mute Issue', 'better-wp-security' ),
			];
		}

		if ( $vulnerability->is_muted() ) {
			$links[ \ITSEC_Lib_REST::get_link_relation( 'unmute-vulnerability' ) ] = [
				'href'  => rest_url( $base . '/unmute' ),
				'title' => __( 'Unmute Issue', 'better-wp-security' ),
			];
		}

		return $links;
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'type'       => 'object',
			'properties' => [
				'id'          => [
					'type'    => 'string',
					'context' => [ 'view', 'edit' ],
				],
				'software'    => [
					'type'       => 'object',
					'properties' => [
						'type'  => [
							'type'       => 'object',
							'properties' => [
								'slug'  => [
									'type' => 'string',
								],
								'label' => [
									'type' => 'string',
								],
							],
						],
						'slug'  => [
							'type' => 'string',
						],
						'label' => [
							'type' => 'string',
						],
					],
					'context'    => [ 'view', 'edit' ],
				],
				'details'     => [
					'type'    => 'object',
					'context' => [ 'view', 'edit' ],
				],
				'first_seen'  => [
					'type'    => 'string',
					'format'  => 'date-time',
					'context' => [ 'view', 'edit' ],
				],
				'last_seen'   => [
					'type'    => 'string',
					'format'  => 'date-time',
					'context' => [ 'view', 'edit' ],
				],
				'resolved_at' => [
					'type'    => [ 'string', 'null' ],
					'format'  => 'date-time',
					'context' => [ 'view', 'edit' ],
				],
				'resolution'  => [
					'type'       => 'object',
					'properties' => [
						'slug'        => [
							'type' => 'string',
						],
						'label'       => [
							'type' => 'string',
						],
						'description' => [
							'type' => 'string',
						],
					],
					'context'    => [ 'view', 'edit' ],
				],
				'resolved_by' => [
					'type'       => [ 'object', 'null' ],
					'properties' => [
						'id'   => [
							'type' => 'integer',
						],
						'name' => [
							'type'     => 'string',
							'readonly' => true,
						],
					],
					'context'    => [ 'view', 'edit' ],
				]
			]
		];

		return $this->schema;
	}

	public function get_collection_params() {
		$params = parent::get_collection_params();
		unset( $params['search'] );
		$params['context']['default'] = 'view';

		$params['resolution'] = [
			'type'  => 'array',
			'items' => [
				'type' => 'string',
				'enum' => [
					'',
					'unresolved',
					Vulnerability::R_PATCHED,
					Vulnerability::R_MUTED,
					Vulnerability::R_DEACTIVATED,
					Vulnerability::R_DELETED,
					Vulnerability::R_UPDATED,
					Vulnerability::R_AUTO_UPDATED,
				],
			],
		];

		$params['software_type'] = [
			'type'  => 'array',
			'items' => [
				'type' => 'string',
				'enum' => [
					Vulnerability::T_PLUGIN,
					Vulnerability::T_THEME,
					Vulnerability::T_WORDPRESS
				],
			]
		];

		$params['patchable'] = [
			'type' => 'boolean',
		];

		$params['first_seen_after'] = [
			'type'   => 'string',
			'format' => 'date-time',
		];

		$params['first_seen_before'] = [
			'type'   => 'string',
			'format' => 'date-time',
		];

		$params['last_seen_after'] = [
			'type'   => 'string',
			'format' => 'date-time',
		];

		$params['last_seen_before'] = [
			'type'   => 'string',
			'format' => 'date-time',
		];

		return $params;
	}
}
