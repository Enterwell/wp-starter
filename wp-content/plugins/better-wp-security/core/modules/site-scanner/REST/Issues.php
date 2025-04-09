<?php

namespace iThemesSecurity\Site_Scanner\REST;

use iThemesSecurity\Site_Scanner\Factory;
use iThemesSecurity\Site_Scanner\Fixer;
use iThemesSecurity\Site_Scanner\Issue;
use iThemesSecurity\Site_Scanner\Repository\Scans_Repository;
use iThemesSecurity\Site_Scanner\Scan;
use iThemesSecurity\Site_Scanner\Status;

class Issues extends \WP_REST_Controller {

	/** @var Scans_Repository */
	private $repository;

	/** @var Fixer */
	private $fixer;

	/** @var string */
	private $parent_base;

	/**
	 * Issues constructor.
	 *
	 * @param Scans_Repository $repository
	 * @param Fixer            $fixer
	 */
	public function __construct( Scans_Repository $repository, Fixer $fixer ) {
		$this->repository  = $repository;
		$this->fixer       = $fixer;
		$this->namespace   = 'ithemes-security/v1';
		$this->parent_base = 'site-scanner/scans';
		$this->rest_base   = 'issues';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->parent_base . '/(?P<scan_id>\d+)/' . $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => array_merge( $this->get_collection_params(), [
					'scan_id' => [
						'type' => 'integer',
					],
				] ),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		register_rest_route( $this->namespace, $this->parent_base . '/(?P<scan_id>\d+)/' . $this->rest_base . '/(?P<issue_id>[\w_-]+)', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_item' ],
				'permission_callback' => [ $this, 'get_item_permissions_check' ],
				'args'                => [
					'scan_id'  => [
						'type' => 'integer',
					],
					'issue_id' => [
						'type' => 'string',
					],
					'context'  => $this->get_context_param( [ 'default' => 'view' ] ),
				],
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );

		register_rest_route( $this->namespace, $this->parent_base . '/(?P<scan_id>\d+)/' . $this->rest_base . '/(?P<issue_id>[\w_-]+)/fix', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'fix_issue' ],
			'permission_callback' => [ $this, 'fix_issue_permissions_check' ],
			'args'                => [
				'scan_id'  => [
					'type' => 'integer',
				],
				'issue_id' => [
					'type' => 'string',
				],
			],
		] );
	}

	public function get_items_permissions_check( $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you cannot view site scan issues.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		return true;
	}

	public function get_items( $request ) {
		$scan = $this->repository->get_scan( $request['scan_id'] );

		if ( is_wp_error( $scan ) ) {
			$scan->add_data( [ 'status' => \WP_Http::NOT_FOUND ] );

			return $scan;
		}

		$issues = [];

		foreach ( $scan->get_entries() as $entry ) {
			if ( $request['entry'] && ! in_array( $entry->get_slug(), $request['entry'], true ) ) {
				continue;
			}

			foreach ( $entry->get_issues() as $issue ) {
				$issues[] = $this->prepare_response_for_collection( $this->prepare_item_for_response( $issue, $request, $scan ) );
			}
		}

		return rest_ensure_response( $issues );
	}

	public function get_item_permissions_check( $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_view', __( 'Sorry, you cannot view site scan issues.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		return true;
	}

	public function get_item( $request ) {
		$issue = $this->find_issue( $request );

		if ( is_wp_error( $issue ) ) {
			return $issue;
		}

		list( $scan, $issue ) = $issue;

		return $this->prepare_item_for_response( $issue, $request, $scan );
	}

	/**
	 * Check if the user has permission to fix the issue.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_User|\WP_Error
	 */
	public function fix_issue_permissions_check( \WP_REST_Request $request ) {
		$valid = $this->has_permission( $request );

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		if ( ! $valid instanceof \WP_User || ! user_can( $valid, \ITSEC_Core::get_required_cap() ) ) {
			return new \WP_Error( 'rest_cannot_fix', __( 'Sorry, you cannot fix site scan issues.', 'better-wp-security' ), [ 'status' => \ITSEC_Lib_REST::auth_code_required( $valid ) ] );
		}

		$issue = $this->find_issue( $request );

		if ( is_wp_error( $issue ) ) {
			return $issue;
		}

		list( , $issue ) = $issue;

		if ( ! $this->fixer->is_fixable( $issue ) ) {
			return new \WP_Error( 'itsec_site_scanner_fixer_not_fixable', __( 'This issue cannot be automatically fixed.', 'better-wp-security' ), array( 'status' => 404 ) );
		}

		$can_fix = $this->fixer->can_user_fix( $valid, $issue );

		if ( is_wp_error( $can_fix ) ) {
			$can_fix->add_data( [ 'status' => 403 ] );
		}

		return $can_fix;
	}

	/**
	 * Fixes the issue.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function fix_issue( \WP_REST_Request $request ) {
		$issue = $this->find_issue( $request );

		if ( is_wp_error( $issue ) ) {
			return $issue;
		}

		list( , $issue ) = $issue;

		$fixed = $this->fixer->fix( $issue );

		if ( is_wp_error( $fixed ) ) {
			return $fixed;
		}

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Find the requested Issue object.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return array|\WP_Error The issue object if found, or a WP_Error instance.
	 */
	protected function find_issue( \WP_REST_Request $request ) {
		$scan = $this->repository->get_scan( $request['scan_id'] );

		if ( is_wp_error( $scan ) ) {
			$scan->add_data( [ 'status' => \WP_Http::NOT_FOUND ] );

			return $scan;
		}

		$issue = $scan->find_issue( $request['issue_id'] );

		if ( ! $issue ) {
			return new \WP_Error( 'itsec_site_scanner_scan_issue_not_found', __( 'No issue was found with that id.', 'better-wp-security' ), [ 'status' => \WP_Http::NOT_FOUND ] );
		}

		return [ $scan, $issue ];
	}

	/**
	 * Prepares the issue for response.
	 *
	 * @param Issue            $item
	 * @param \WP_REST_Request $request
	 * @param Scan             $scan
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request, Scan $scan = null ) {
		if ( ! $scan ) {
			return rest_ensure_response( null );
		}

		$data = [
			'id'          => $item->get_id(),
			'link'        => $item->get_link(),
			'description' => $item->get_description(),
			'status'      => $item->get_status(),
			'entry'       => $item->get_entry(),
			'meta'        => $item->get_meta(),
		];
		$data = $this->filter_response_by_context( $data, $request['context'] );

		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $scan, $item ) );

		return $response;
	}

	/**
	 * Prepares the list of links for an issue.
	 *
	 * @param Scan  $scan
	 * @param Issue $issue
	 *
	 * @return array
	 */
	protected function prepare_links( Scan $scan, Issue $issue ) {
		$base = sprintf( '%s/%s/%d/%s/%s', $this->namespace, $this->parent_base, $scan->get_id(), $this->rest_base, $issue->get_id() );

		$links = [
			'self'       => [
				'href' => rest_url( $base ),
			],
			'collection' => [
				'href' => rest_url( sprintf( '%s/%s/%d/%s', $this->namespace, $this->parent_base, $scan->get_id(), $this->rest_base ) ),
			],
			'up'         => [
				'href' => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->parent_base, $scan->get_id() ) ),
			],
		];

		if ( $this->fixer->is_fixable( $issue ) && $this->fixer->can_user_fix( wp_get_current_user(), $issue ) ) {
			$links[ \ITSEC_Lib_REST::get_link_relation( 'site-scan-fix-issue' ) ] = [
				'href'  => rest_url( $base . '/fix' ),
				'title' => $this->fixer->get_fix_label( $issue ),
			];
		}

		return $links;
	}

	/**
	 * Checks if the current request has permission to interact with scans.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_User|\WP_Error|false
	 */
	protected function has_permission( \WP_REST_Request $request ) {
		if ( is_user_logged_in() ) {
			return wp_get_current_user();
		}

		if ( $token = \ITSEC_Lib_REST::get_token_from_auth_header( $request->get_header( 'Authorization' ) ) ) {
			$valid = \ITSEC_Site_Scanner_Util::validate_scan_auth_token( $token );

			if ( is_wp_error( $valid ) ) {
				$valid->add_data( [ 'status' => 401 ] );
			}

			return $valid;
		}

		return false;
	}

	public function get_item_schema() {
		if ( ! $this->schema ) {
			$this->schema = [
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'type'       => 'object',
				'properties' => [
					'id'          => [
						'type'    => 'string',
						'context' => [ 'view', 'embed' ],
					],
					'link'        => [
						'type'    => 'string',
						'format'  => 'uri',
						'context' => [ 'view', 'embed' ],
					],
					'description' => [
						'type'    => 'string',
						'context' => [ 'view', 'embed' ],
					],
					'status'      => [
						'type'    => 'string',
						'enum'    => Status::ALL,
						'context' => [ 'view', 'embed' ],
					],
					'entry'       => [
						'type'    => 'string',
						'context' => [ 'view', 'embed' ],
						'enum'    => Factory::ENTRIES,
					],
					'meta'        => [
						'type'                 => 'object',
						'properties'           => [],
						'additionalProperties' => true,
						'context'              => [ 'view' ],
					],
				],
			];
		}

		return $this->schema;
	}

	public function get_collection_params() {
		return [
			'context' => $this->get_context_param( [ 'default' => 'view' ] ),
			'entry'   => [
				'type'  => 'array',
				'items' => [
					'type' => 'string',
					'enum' => Factory::ENTRIES,
				],
			]
		];
	}
}
