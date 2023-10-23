<?php

namespace iThemesSecurity\Modules\Strong_Passwords\REST;

use iThemesSecurity\User_Groups\Match_Target;
use iThemesSecurity\User_Groups\Matcher;

class Scan extends \WP_REST_Controller {

	/** @var Matcher */
	protected $matcher;

	protected $namespace = 'ithemes-security/v1';
	protected $rest_base = 'strong-passwords/scan';

	public function __construct( Matcher $matcher ) {
		$this->matcher = $matcher;
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			'callback'            => [ $this, 'get_items' ],
			'permission_callback' => 'ITSEC_Core::current_user_can_manage',
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)/mute', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'mute_issue' ],
			'permission_callback' => 'ITSEC_Core::current_user_can_manage',
		] );
		register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)/unmute', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'unmute_issue' ],
			'permission_callback' => 'ITSEC_Core::current_user_can_manage',
		] );
	}

	/**
	 * Runs a scan for users that should be using Two-Factor.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		if ( $this->is_strong_passwords_enforced_for_administrators() ) {
			return new \WP_REST_Response( [] );
		}

		$users  = \ITSEC_Lib_Canonical_Roles::get_users_with_canonical_role( 'administrator' );
		$issues = [];

		foreach ( $users as $user ) {
			$strength = get_user_meta( $user->ID, \ITSEC_Strong_Passwords::STRENGTH_KEY, true );

			if ( (int) $strength === 4 ) {
				continue;
			}

			$issues[] = $this->prepare_response_for_collection(
				$this->prepare_item_for_response( $user, $request )
			);
		}

		return new \WP_REST_Response( $issues );
	}

	public function mute_issue( \WP_REST_Request $request ) {
		if ( ! $user = get_userdata( $request['id'] ) ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Issue not found.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		update_user_meta( $user->ID, '_solid_password_ignore', [
			'user' => get_current_user_id(),
			'time' => \ITSEC_Core::get_current_time_gmt(),
		] );

		return $this->prepare_item_for_response( $user, $request );
	}

	public function unmute_issue( \WP_REST_Request $request ) {
		if ( ! $user = get_userdata( $request['id'] ) ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Issue not found.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		delete_user_meta( $user->ID, '_solid_password_ignore' );

		return $this->prepare_item_for_response( $user, $request );
	}

	/**
	 * Prepares a user for response.
	 *
	 * @param \WP_User         $item
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		$strength = get_user_meta( $item->ID, \ITSEC_Strong_Passwords::STRENGTH_KEY, true );

		$response = new \WP_REST_Response( [
			'id'          => $item->ID,
			'component'   => 'passwords',
			'title'       => $item->display_name,
			'description' => sprintf(
				$strength === ''
					? __( '%s has administrator capabilities and an unknown password strength.', 'better-wp-security' )
					/* translators: 1. User display name. */
					: __( '%s has administrator capabilities, but does not have a strong password.', 'better-wp-security' ),
				$item->display_name
			),
			'severity'    => $strength === '' ? 'medium' : 'high',
			'muted'       => (bool) get_user_meta( $item->ID, '_solid_password_ignore', true ),
			'meta'        => [
				'strength' => $strength === '' ? null : (int) $strength,
			]
		] );

		if ( $response->get_data()['muted'] ) {
			$response->add_link(
				\ITSEC_Lib_REST::get_link_relation( 'unmute-issue' ),
				rest_url( sprintf( '%s/%s/%d/unmute', $this->namespace, $this->rest_base, $item->ID ) ),
				[
					'title' => __( 'Unmute Issue', 'better-wp-security' )
				]
			);
		} else {
			$response->add_link(
				\ITSEC_Lib_REST::get_link_relation( 'mute-issue' ),
				rest_url( sprintf( '%s/%s/%d/mute', $this->namespace, $this->rest_base, $item->ID ) ),
				[
					'title' => __( 'Mute Issue', 'better-wp-security' )
				]
			);
		}

		return $response;
	}

	private function is_strong_passwords_enforced_for_administrators(): bool {
		$roles = \ITSEC_Lib_Canonical_Roles::get_real_roles_for_canonical( 'administrator' );

		$settings = \ITSEC_Lib_Password_Requirements::get_requirement_settings( 'strength' );

		if ( empty( $settings['group'] ) ) {
			return false;
		}

		foreach ( $roles as $role ) {
			if ( ! $this->matcher->matches( Match_Target::for_role( $role ), $settings['group'] ) ) {
				return false;
			}
		}

		return true;
	}
}
