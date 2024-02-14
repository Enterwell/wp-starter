<?php

namespace iThemesSecurity\TwoFactor\REST;

use iThemesSecurity\User_Groups\Match_Target;
use iThemesSecurity\User_Groups\Matcher;

class Scan extends \WP_REST_Controller {

	/** @var Matcher */
	protected $matcher;

	/** @var \ITSEC_Two_Factor */
	protected $two_factor;

	protected $namespace = 'ithemes-security/v1';
	protected $rest_base = 'two-factor/scan';

	public function __construct( Matcher $matcher, \ITSEC_Two_Factor $two_factor ) {
		$this->matcher    = $matcher;
		$this->two_factor = $two_factor;
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

		if ( \ITSEC_Modules::is_active( 'user-security-check' ) ) {
			register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)/remind', [
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'remind_user' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
			] );
		}
	}

	/**
	 * Runs a scan for users that should be using Two-Factor.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function get_items( $request ) {
		if ( $this->is_2fa_enforced_for_administrators() ) {
			return new \WP_REST_Response( [] );
		}

		$users  = \ITSEC_Lib_Canonical_Roles::get_users_with_canonical_role( 'administrator' );
		$issues = [];

		foreach ( $users as $user ) {
			if ( $this->two_factor->get_primary_provider_for_user( $user->ID ) ) {
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

		update_user_meta( $user->ID, '_solid_2fa_ignore', [
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

		delete_user_meta( $user->ID, '_solid_2fa_ignore' );

		return $this->prepare_item_for_response( $user, $request );
	}

	public function remind_user( \WP_REST_Request $request ) {
		if ( ! $user = get_userdata( $request['id'] ) ) {
			return new \WP_Error(
				'rest_not_found',
				__( 'Issue not found.', 'better-wp-security' ),
				[ 'status' => \WP_Http::NOT_FOUND ]
			);
		}

		$sent = \ITSEC_Two_Factor::get_instance()->send_setup_reminder( $user, wp_get_current_user() );

		if ( is_wp_error( $sent ) ) {
			return $sent;
		}

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
		$response = new \WP_REST_Response( [
			'id'          => $item->ID,
			'component'   => 'two-factor',
			'title'       => $item->display_name,
			'description' => sprintf(
			/* translators: 1. User display name. */
				__( '%s has administrator capabilities, but does not have Two-Factor enabled.', 'better-wp-security' ),
				$item->display_name
			),
			'severity'    => 'medium',
			'muted'       => (bool) get_user_meta( $item->ID, '_solid_2fa_ignore', true ),
		] );

		$response->add_link(
			\ITSEC_Lib_REST::get_link_relation( 'send-2fa-reminder' ),
			rest_url( sprintf( '%s/%s/%d/remind', $this->namespace, $this->rest_base, $item->ID ) ),
			[
				'title'     => __( 'Remind User', 'better-wp-security' ),
				'snackbar'  => __( 'Email sent', 'better-wp-security' ),
			]
		);

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

	private function is_2fa_enforced_for_administrators(): bool {
		$roles = \ITSEC_Lib_Canonical_Roles::get_real_roles_for_canonical( 'administrator' );

		$protect = \ITSEC_Modules::get_setting( 'two-factor', 'protect_user_group' );

		if ( ! $protect ) {
			return false;
		}

		foreach ( $roles as $role ) {
			if ( ! $this->matcher->matches( Match_Target::for_role( $role ), $protect ) ) {
				return false;
			}
		}

		return true;
	}
}
