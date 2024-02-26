<?php

namespace iThemesSecurity\Lib\REST;

use ITSEC_Lib;
use ITSEC_Log;
use WP_REST_Request;

class Lockouts_Controller extends \WP_REST_Controller {
	protected $namespace = 'ithemes-security/v1';
	protected $rest_base = 'lockouts';

	public function __construct( \ITSEC_Lockout $lockout ) { $this->lockout = $lockout; }

	public function register_routes(): void {
		// get_all
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
				'args'                => $this->get_collection_params(),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)', [
			'methods'             => 'GET',
			'callback'            => [ $this, 'get_lockout' ],
			'permission_callback' => 'ITSEC_Core::current_user_can_manage',
			'args'                => [ 'id' => [ 'type' => 'integer' ] ],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)' . '/release-lockout', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'release_lockout' ],
			'permission_callback' => 'ITSEC_Core::current_user_can_manage',
			'args'                => [ 'id' => [ 'type' => 'integer' ] ],
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)' . '/ban-lockout', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'ban_lockout' ],
			'permission_callback' => 'ITSEC_Core::current_user_can_manage',
			'args'                => [ 'id' => [ 'type' => 'integer' ] ],
		] );
	}

	public function get_items( $request ) {
		$lockout_query = array(
			'limit'   => 100,
			'current' => true,
			'order'   => 'DESC',
			'orderby' => 'lockout_start',
		);

		if ( $request['search'] ) {
			$lockout_query['search'] = $request['search'];
		}

		$lockouts = $this->lockout->get_lockouts( 'all', $lockout_query );

		if ( is_wp_error( $lockouts ) ) {
			return $lockouts;
		}

		$data = [];

		foreach ( $lockouts as $lockout ) {
			$data[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $lockout, $request )
			);
		}

		return new \WP_REST_Response( $data );
	}

	public function get_lockout( $request ) {

		$lockout = $this->lockout->get_lockout( $request['id'] );

		if ( is_wp_error( $lockout ) ) {
			return $lockout;
		}

		return $this->prepare_item_for_response( $lockout, $request );
	}

	public function prepare_item_for_response( $item, $request ) {
		$modules = $this->lockout->get_lockout_modules();

		$data = array();

		foreach ( $item as $key => $value ) {
			$data[ str_replace( 'lockout_', '', $key ) ] = $value;
		}

		$data['id'] = (int) $data['id'];

		$data['active'] = (bool) $data['active'];

		foreach ( array( 'start', 'start_gmt', 'expire', 'expire_gmt' ) as $date_prop ) {
			$data[ $date_prop ] = ITSEC_Lib::to_rest_date( $data[ $date_prop ] );

			$data["{$date_prop}_relative"] = human_time_diff( strtotime( $data[ $date_prop ] ) );
		}

		if ( ! empty( $data['host'] ) ) {
			$data['label'] = $data['host'];
		} elseif ( ! empty( $data['username'] ) ) {
			$data['label'] = $data['username'];
		} elseif ( ! empty( $data['user'] ) ) {
			$user = get_userdata( $data['user'] );

			$data['label'] = $user ? $user->display_name : sprintf( __( 'Deleted User %d', 'better-wp-security' ), $data['user'] );
		} else {
			$data['label'] = __( 'Unknown', 'better-wp-security' );
		}

		$data['bannable'] = $this->lockout->can_create_ban_from_lockout( $data['id'] )->is_success();

		$data['description'] = isset( $modules[ $data['type'] ] ) ? $modules[ $data['type'] ]['reason'] : __( 'unknown reason.', 'better-wp-security' );


		if ( $request['context'] === 'edit' ) {

			if ( ! empty( $data['host'] ) ) {
				$entries = ITSEC_Log::get_entries( array(
					'init_timestamp' => $data['start_gmt'],
					'module'         => 'lockout',
					'code'           => "host-lockout::{$data['host']}",
				), 1, 1, 'timestamp', 'DESC', 'all' );
			} elseif ( ! empty( $data['user'] ) ) {
				$entries = ITSEC_Log::get_entries( array(
					'init_timestamp' => $data['start_gmt'],
					'module'         => 'lockout',
					'code'           => "user-lockout::{$data['user']}",
				), 1, 1, 'timestamp', 'DESC', 'all' );
			} elseif ( ! empty( $data['username'] ) ) {
				$entries = ITSEC_Log::get_entries( array(
					'init_timestamp' => $data['start_gmt'],
					'module'         => 'lockout',
					'code'           => "username-lockout::{$data['username']}",
				), 1, 1, 'timestamp', 'DESC', 'all' );
			} else {
				$entries = array();
			}

			if ( ! empty( $entries[0] ) ) {
				$lockout_log = array(
					'id'            => (int) $entries[0]['id'],
					'time'          => ITSEC_Lib::to_rest_date( $entries[0]['init_timestamp'] ),
					'time_relative' => human_time_diff( strtotime( $entries[0]['init_timestamp'] ) ),
					'remote_ip'     => $entries[0]['remote_ip'],
					'url'           => $entries[0]['url'],
					'data'          => $entries[0]['data'],
				);
			} else {
				$lockout_log = array();
			}

			$data['detail'] = array(
				'log'     => $lockout_log,
				'history' => array(),
			);

			switch ( $data['type'] ) {
				case 'recaptcha':
					$logs = ITSEC_Log::get_entries( array(
						'module'          => 'recaptcha',
						'code'            => 'failed-validation',
						'remote_ip'       => $data['host'],
						'__max_timestamp' => strtotime( $data['start_gmt'] ),
					), 100, 1, 'timestamp', 'DESC', 'all' );

					if ( is_array( $logs ) ) {
						foreach ( $logs as $log ) {
							if ( is_wp_error( $log['data'] ) ) {
								$label = $log['data']->get_error_code() === 'itsec-recaptcha-incorrect' ? __( 'Invalid CAPTCHA', 'better-wp-security' ) : __( 'Skipped CAPTCHA', 'better-wp-security' );
							} else {
								$label = __( 'Unknown', 'better-wp-security' );
							}

							$data['detail']['history'][] = array(
								'id'            => (int) $log['id'],
								'time'          => ITSEC_Lib::to_rest_date( $log['init_timestamp'] ),
								'time_relative' => human_time_diff( strtotime( $log['init_timestamp'] ) ),
								'url'           => $log['url'],
								'label'         => $label,
								'error'         => is_wp_error( $log['data'] ) ? array(
									'code'    => $log['data']->get_error_code(),
									'message' => $log['data']->get_error_message(),
								) : null,
							);
						}
					}
					break;
				case 'brute_force':
					$log_query = array(
						'module'          => 'brute_force',
						'__max_timestamp' => strtotime( $data['start_gmt'] ),
					);

					if ( ! empty( $data['host'] ) ) {
						$log_query['remote_ip'] = $data['host'];
					} elseif ( ! empty( $data['user'] ) ) {
						$log_query['code'] = "invalid-login::user-{$data['user']}";
					} elseif ( ! empty( $data['username'] ) ) {
						$log_query['code'] = "invalid-login::username-{$data['username']}";
					} else {
						break;
					}

					$logs = ITSEC_Log::get_entries( $log_query, 100, 1, 'timestamp', 'DESC', 'all' );

					if ( is_array( $logs ) ) {
						foreach ( $logs as $log ) {
							if ( ! empty( $data['host'] ) ) {
								$label = $log['data']['username'];
							} elseif ( ! empty( $data['username'] ) || ! empty( $data['user'] ) ) {
								$label = $log['remote_ip'];
							} else {
								$label = '';
							}

							$data['detail']['history'][] = array(
								'id'            => (int) $log['id'],
								'time'          => ITSEC_Lib::to_rest_date( $log['init_timestamp'] ),
								'time_relative' => human_time_diff( strtotime( $log['init_timestamp'] ) ),
								'url'           => $log['url'],
								'remote_ip'     => $log['remote_ip'],
								'data'          => array(
									'details'  => $log['data']['details'],
									'user'     => $log['data']['user'],
									'username' => $log['data']['username'],
									'user_id'  => $log['data']['user_id'],
								),
								'label'         => $label,
							);
						}
					}
					break;
			}
		}

		$response = new \WP_REST_Response( $data );
		$response->add_links( $this->prepare_links( $item ) );

		/**
		 * Filters the REST API response for Lockout items
		 *
		 * @param \WP_REST_Response $response The REST API response object.
		 * @param array             $item     The raw lockout item from the database.
		 * @param \WP_REST_Request  $request  The full API request object.
		 */
		return apply_filters( 'itsec_rest_prepare_lockout_for_response', $response, $item, $request );
	}

	protected function prepare_links( array $lockout ): array {
		$base = sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $lockout['lockout_id'] );

		$links = [
			'self'                                          => [
				'href'  => rest_url( $base ),
				'title' => __( 'Lockout Details', 'better-wp-security' ),
			],
			\ITSEC_Lib_REST::get_link_relation( 'release' ) => [
				'href'  => rest_url( $base ) . '/release-lockout',
				'title' => __( 'Release Lockout', 'better-wp-security' ),
			]
		];

		if ( $this->lockout->is_lockout_banning_available() ) {
			$links[ \ITSEC_Lib_REST::get_link_relation( 'ban' ) ] = [
				'href'  => rest_url( $base ) . '/ban-lockout',
				'title' => __( 'Ban Lockout', 'better-wp-security' ),
			];
		}

		return $links;
	}

	public function ban_lockout( $request ) {
		$banned_lockout = $this->lockout->get_lockout( $request['id'] );

		if ( ! $banned_lockout ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Sorry, that lockout was not found.', 'better-wp-security' ),
				[ 'status', \WP_Http::NOT_FOUND ]
			);
		}

		if ( ! $this->lockout->is_lockout_banning_available() ) {
			return new \WP_Error(
				'rest_action_forbidden',
				__( 'Sorry, banning lockouts is not available', 'better-wp-security' ),
				[ 'status', \WP_Http::FORBIDDEN ]
			);
		}

		if ( ! $this->lockout->can_create_ban_from_lockout( $banned_lockout['lockout_id'] ) ) {
			return new \WP_Error(
				'rest_action_forbidden',
				__( 'Sorry, this lockout could not be banned', 'better-wp-security' ),
				[ 'status', \WP_Http::FORBIDDEN ]
			);
		}

		$banResult = $this->lockout->persist_ban_from_lockout( $banned_lockout['lockout_id'] );

		if ( is_wp_error( $banResult ) ) {
			return new \WP_Error(
				'rest_action_failed',
				__( 'Sorry, banning that lockout has failed', 'better-wp-security' ),
				[ 'status', \WP_Http::INTERNAL_SERVER_ERROR ]
			);
		}

		return new \WP_REST_Response( $banResult );

	}

	public function release_lockout( $request ) {
		$released_lockout = $this->lockout->get_lockout( $request['id'] );

		if ( ! $released_lockout ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Sorry, that lockout was not found', 'better-wp-security' ),
				[ 'status', \WP_Http::NOT_FOUND ]
			);
		}

		$releaseResult = $this->lockout->release_lockout( $released_lockout['lockout_id'] );

		if ( is_wp_error( $releaseResult ) ) {
			return new \WP_Error(
				'rest_action_failed',
				__( 'Sorry, releasing that lockout has failed', 'better-wp-security' ),
				[ 'status', \WP_Http::INTERNAL_SERVER_ERROR ]
			);
		}

		return new \WP_REST_Response( $releaseResult );
	}

	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';
		$params['search']             = [
			'type' => 'string',
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
				'id'                  => [
					'type'     => 'integer',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'type'                => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'start'               => [
					'type'     => 'string',
					'format'   => 'date-time',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'expire'              => [
					'type'     => 'string',
					'format'   => 'date-time',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'expire_gmt'          => [
					'type'     => 'string',
					'format'   => 'date-time',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'host'                => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'user'                => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'username'            => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'active'              => [
					'type'     => 'boolean',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'start_relative'      => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'start_gmt_relative'  => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'expire_relative'     => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'expire_gmt_relative' => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'label'               => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'bannable'            => [
					'type'     => 'boolean',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'description'         => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ],
				],
				'context'             => [
					'type'     => 'string',
					'readonly' => true,
					'context'  => [ 'view', 'embed' ]
				],
				'detail'              => [
					'type'       => 'object',
					'properties' => [
						'log' => [
							'type'       => 'object',
							'properties' => [
								'id'            => [
									'type'     => 'integer',
									'readonly' => true,
									'context'  => [ 'embed' ]
								],
								'time'          => [
									'type'     => 'string',
									'format'   => 'date-time',
									'readonly' => true,
									'context'  => [ 'embed' ],
								],
								'time_relative' => [
									'type'     => 'string',
									'readonly' => true,
									'context'  => [ 'embed' ],
								],
								'remote_ip'     => [
									'type'     => 'string',
									'format'   => 'ip',
									'readonly' => true,
								],
								'url'           => [
									'type'     => 'string',
									'readonly' => true,
									'context'  => [ 'embed' ],
								],
								'data'          => [
									'type'       => 'object',
									'properties' => [
										'module'             => [
											'type'     => 'string',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'host'               => [
											'type'     => 'string',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'user_id'            => [
											'type'     => 'integer',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'username'           => [
											'type'     => 'string',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'module_details'     => [
											'type'       => 'object',
											'properties' => [
												'host'   => [
													'type'     => 'integer',
													'readonly' => true,
													'context'  => [ 'embed' ],
												],
												'label'  => [
													'type'     => 'string',
													'readonly' => true,
													'context'  => [ 'embed' ],
												],
												'period' => [
													'type'     => 'integer',
													'readonly' => true,
													'context'  => [ 'embed' ],
												],
												'reason' => [
													'type'     => 'string',
													'readonly' => true,
													'context'  => [ 'embed' ],
												],
											],
											'type'       => [
												'type'     => 'string',
												'readonly' => true,
												'context'  => [ 'embed' ],
											],
											'user'       => [
												'type'     => 'integer',
												'readonly' => true,
												'context'  => [ 'embed' ]
											]
										],
										'whitelisted'        => [
											'type'     => 'boolean',
											'readonly' => true,
											'context'  => [ 'embed' ]
										],
										'blacklisted'        => [
											'type'     => 'boolean',
											'readonly' => true,
											'context'  => [ 'embed' ]
										],
										'lockout_type'       => [
											'type'     => 'string',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'lockout_start'      => [
											'type'     => 'datetime',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'lockout_start_gmt'  => [
											'type'     => 'datetime',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'lockout_context'    => [
											'type'     => 'string',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'lockout_expire'     => [
											'type'     => 'datetime',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'lockout_expire_gmt' => [
											'type'     => 'datetime',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
										'lockout_username'   => [
											'type'     => 'string',
											'readonly' => true,
											'context'  => [ 'embed' ],
										],
									]
								],
							]
						]
					]
				],
			]
		];
	}
}
