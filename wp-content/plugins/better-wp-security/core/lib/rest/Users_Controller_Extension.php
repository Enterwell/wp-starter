<?php

namespace iThemesSecurity\Lib\REST;

use iThemesSecurity\Contracts\Runnable;

class Users_Controller_Extension implements Runnable {

	public function run() {
		add_filter( 'rest_user_collection_params', [ $this, 'register_collection_params' ] );
		add_filter( 'rest_user_query', [ $this, 'apply_collection_params' ], 10, 2 );
		add_filter( 'rest_prepare_user', [ $this, 'add_user_links' ], 10, 2 );
		$this->register_fields();
	}

	/**
	 * Registers the custom collection params with the server.
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function register_collection_params( $params ) {
		$params['solid_user_groups'] = [
			'type'  => 'array',
			'items' => [
				'type'   => 'string',
				'format' => 'uuid',
			],
		];

		$params['solid_last_seen'] = [
			'type'       => 'object',
			'properties' => [
				'after'  => [
					'type'   => 'string',
					'format' => 'date-time',
				],
				'before' => [
					'type'   => 'string',
					'format' => 'date-time',
				],
			],
		];

		$params['solid_password_changed'] = [
			'type'       => 'object',
			'properties' => [
				'after'  => [
					'type'   => 'string',
					'format' => 'date-time',
				],
				'before' => [
					'type'   => 'string',
					'format' => 'date-time',
				],
			],
		];

		$params['solid_password_strength'] = [
			'type'  => 'array',
			'items' => [
				'type'      => 'integer',
				'enum'      => [ 0, 1, 2, 3, 4 ],
				'enumNames' => [
					_x( 'Very Weak', 'password strength', 'better-wp-security' ),
					_x( 'Weak', 'password strength', 'better-wp-security' ),
					_x( 'Medium', 'password strength', 'better-wp-security' ),
					_x( 'Strong', 'password strength', 'better-wp-security' ),
				]
			],
		];

		if ( \ITSEC_Modules::is_active( 'two-factor' ) ) {
			$enum      = [ 'enabled', 'not-enabled' ];
			$enumNames = [
				__( 'Configured Two-Factor', 'better-wp-security' ),
				__( 'Not Configured Two-Factor', 'better-wp-security' ),
			];

			if ( \ITSEC_Core::get_install_type() === 'pro' && \ITSEC_Modules::get_setting( 'two-factor', 'protect_user_group' ) ) {
				$enum[]      = 'enforced-not-configured';
				$enumNames[] = __( 'Two-Factor Enforced', 'better-wp-security' );
			}

			$params['solid_2fa'] = [
				'type'      => 'string',
				'enum'      => $enum,
				'enumNames' => $enumNames,
			];
		}

		return $params;
	}

	/**
	 * Applies the custom collection params to the query.
	 *
	 * @param array            $params
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 */
	public function apply_collection_params( $params, $request ) {
		if ( ! \ITSEC_Core::current_user_can_manage() ) {
			return $params;
		}

		if ( $request['solid_user_groups'] ) {
			$params['solid_user_groups'] = $request['solid_user_groups'];
		}

		if ( $request['solid_password_strength'] ) {
			$params['solid_password_strength'] = $request['solid_password_strength'];
		}

		if ( $request['solid_last_seen'] ) {
			$last_seen = $request['solid_last_seen'];

			if ( isset( $last_seen['after'] ) ) {
				$last_seen['after'] = rest_parse_date( $last_seen['after'] );
			}

			if ( isset( $last_seen['before'] ) ) {
				$last_seen['before'] = rest_parse_date( $last_seen['before'] );
			}

			$params['solid_last_seen'] = $last_seen;
		}

		if ( $request['solid_password_changed'] ) {
			$password_changed = $request['solid_password_changed'];

			if ( isset( $password_changed['after'] ) ) {
				$password_changed['after'] = rest_parse_date( $password_changed['after'] );
			}

			if ( isset( $password_changed['before'] ) ) {
				$password_changed['before'] = rest_parse_date( $password_changed['before'] );
			}

			$params['solid_password_changed'] = $password_changed;
		}

		if ( $request['solid_2fa'] ) {
			$enforced = \ITSEC_Modules::get_setting( 'two-factor', 'protect_user_group' );

			if ( $request['solid_2fa'] === 'enforced-not-configured' ) {
				$params['solid_2fa'] = 'not-enabled';

				if ( $enforced ) {
					$params['solid_user_groups'] = array_merge(
						$enforced,
						$params['solid_user_groups'] ?? [],
					);
				}
			} elseif ( $request['solid_2fa'] === 'not-enabled' ) {
				$params['solid_2fa'] = $request['solid_2fa'];

				if ( $enforced ) {
					$params['solid_user_groups__not_in'] = array_merge(
						$enforced,
						$params['solid_user_groups__not_in'] ?? [],
					);
				}
			} else {
				$params['solid_2fa'] = $request['solid_2fa'];
			}
		}

		return $params;
	}

	/**
	 * Adds links to the user object.
	 *
	 * @param \WP_REST_Response $response
	 * @param \WP_User          $user
	 *
	 * @return \WP_REST_Response
	 */
	public function add_user_links( \WP_REST_Response $response, \WP_User $user ): \WP_REST_Response {
		if (
			\ITSEC_Core::current_user_can_manage() &&
			\ITSEC_Lib_Fingerprinting::is_current_fingerprint_safe() &&
			\ITSEC_Lib_Fingerprinting::applies_to_user( $user )
		) {
			$response->add_link(
				\ITSEC_Lib_REST::get_link_relation( 'trusted-devices' ),
				rest_url( '/ithemes-security/v1/trusted-devices/' . $user->ID ),
			);
		}

		return $response;
	}

	/**
	 * Registers the REST API fields.
	 *
	 * @return void
	 */
	public function register_fields() {
		register_rest_field( 'user', 'solid_password_strength', [
			'get_callback' => function ( $data ) {
				if ( ! \ITSEC_Core::current_user_can_manage() ) {
					return null;
				}

				$strength = get_user_meta( $data['id'], \ITSEC_Strong_Passwords::STRENGTH_KEY, true );

				if ( $strength === '' ) {
					return null;
				}

				return (int) $strength;
			},
			'schema'       => [
				'type'     => [ 'integer', 'null' ],
				'enum'     => [ 0, 1, 2, 3, 4 ],
				'context'  => [ 'edit' ],
				'readonly' => true,
			],
		] );
		register_rest_field( 'user', 'solid_last_seen', [
			'get_callback' => function ( $data ) {
				$last_seen = \ITSEC_Lib_User_Activity::get_instance()->get_last_seen( $data['id'] );

				if ( ! $last_seen ) {
					return null;
				}

				return gmdate( 'Y-m-d\TH:i:s', $last_seen );
			},
			'schema'       => [
				'type'     => [ 'string', 'null' ],
				'format'   => 'date-time',
				'context'  => [ 'edit' ],
				'readonly' => true,
			],
		] );
		register_rest_field( 'user', 'solid_password_changed', [
			'get_callback' => function ( $data ) {
				$last_changed = \ITSEC_Lib_Password_Requirements::password_last_changed( $data['id'] );

				if ( ! $last_changed ) {
					return null;
				}

				return gmdate( 'Y-m-d\TH:i:s', $last_changed );
			},
			'schema'       => [
				'type'     => [ 'string', 'null' ],
				'format'   => 'date-time',
				'context'  => [ 'edit' ],
				'readonly' => true,
			],
		] );
		register_rest_field( 'user', 'solid_2fa', [
			'get_callback' => function ( $data ) {
				if ( ! \ITSEC_Modules::is_active( 'two-factor' ) ) {
					return null;
				}

				$user = get_userdata( $data['id'] );

				if ( ! $user ) {
					return null;
				}

				if (
					! \ITSEC_Core::current_user_can_manage() &&
					$user->ID !== get_current_user_id()
				) {
					return null;
				}

				$two_factor = \ITSEC_Two_Factor::get_instance();

				if ( $two_factor->get_available_providers_for_user( $user, false ) ) {
					return 'enabled';
				} elseif ( $two_factor->get_available_providers_for_user( $user, true ) ) {
					return 'enforced-not-configured';
				} elseif ( ! $two_factor->get_allowed_provider_instances_for_user( $user ) ) {
					return 'not-available';
				}

				return 'not-enabled';

			},
			'schema'       => [
				'type'      => [ 'string', 'null' ],
				'enum'      => [ 'enabled', 'not-enabled', 'enforced-not-configured', 'not-available' ],
				'enumNames' => [
					__( 'Configured Two-Factor', 'better-wp-security' ),
					__( 'Not Configured Two-Factor', 'better-wp-security' ),
					__( 'Two-Factor Enforced', 'better-wp-security' ),
					__( 'Two-Factor Not Available', 'better-wp-security' ),
				],
				'context'   => [ 'edit' ],
				'readonly'  => true,
			],
		] );
		register_rest_field( 'user', 'solid_edit_user_link', [
			'get_callback' => function ( $data ) {

				if ( ! \ITSEC_Core::current_user_can_manage() ) {
					return null;
				}

				$user = get_userdata( $data['id'] );

				if ( ! $user ) {
					return null;
				}

				return get_edit_user_link( $data['id'] );

			},
			'schema'       => [
				'type'     => [ 'string', 'null' ],
				'format'   => 'uri',
				'context'  => [ 'edit' ],
				'readonly' => true,
			]
		] );
	}
}
