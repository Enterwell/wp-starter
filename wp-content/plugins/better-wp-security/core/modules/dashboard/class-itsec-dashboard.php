<?php

use iThemesSecurity\Contracts\Import_Export_Source;
use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Import_Export\Export\Export;
use iThemesSecurity\Import_Export\Import\Import_Context;
use iThemesSecurity\Import_Export\Import\Transformation;
use iThemesSecurity\Lib\Result;
use iThemesSecurity\User_Groups;

/**
 * Class ITSEC_Dashboard
 */
class ITSEC_Dashboard implements Runnable, Import_Export_Source {

	const CPT_DASHBOARD = 'itsec-dashboard';
	const META_SHARE_USER = '_itsec_dashboard_share_user';
	const META_SHARE_ROLE = '_itsec_dashboard_share_role';

	const CPT_CARD = 'itsec-dash-card';
	const META_CARD = '_itsec_dashboard_card';
	const META_CARD_SETTINGS = '_itsec_dashboard_card_settings';
	const META_CARD_POSITION = '_itsec_dashboard_card_position';
	const META_CARD_SIZE = '_itsec_dashboard_card_size';

	const META_PRIMARY = '_itsec_primary_dashboard';

	/** @var User_Groups\Matcher */
	private $matcher;

	/**
	 * ITSEC_Dashboard constructor.
	 *
	 * @param User_Groups\Matcher $matcher
	 */
	public function __construct( User_Groups\Matcher $matcher ) { $this->matcher = $matcher; }

	/**
	 * Run the dashboard module.
	 */
	public function run() {
		add_action( 'init', array( $this, 'register_data_storage' ) );
		add_action( 'itsec_scheduled_dashboard-consolidate-events', array( $this, 'run_consolidate_events' ) );
		add_action( 'after_delete_post', array( $this, 'after_delete_post' ), 10, 2 );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
		add_action( 'itsec_log_add', array( $this, 'log_add' ) );

		require_once( dirname( __FILE__ ) . '/class-itsec-dashboard-rest.php' );
		$rest = new ITSEC_Dashboard_REST();
		$rest->run();
	}

	/**
	 * Register the Custom Post Types and Metadata.
	 */
	public function register_data_storage() {
		register_post_type( self::CPT_DASHBOARD, array(
			'public'       => false,
			'hierarchical' => true,
			'supports'     => array( 'title' ),
		) );

		register_post_meta( self::CPT_DASHBOARD, self::META_SHARE_USER, array(
			'type'              => 'integer',
			'single'            => false,
			'sanitize_callback' => 'absint'
		) );

		register_post_meta( self::CPT_DASHBOARD, self::META_SHARE_ROLE, array(
			'type'              => 'string',
			'single'            => false,
			'sanitize_callback' => array( __CLASS__, '_sanitize_role' )
		) );

		register_post_type( self::CPT_CARD, array(
			'public'   => false,
			'supports' => array(),
		) );

		register_post_meta( self::CPT_CARD, self::META_CARD, array(
			'type'              => 'string',
			'single'            => true,
			'sanitize_callback' => array( __CLASS__, '_sanitize_card' ),
		) );

		register_post_meta( self::CPT_CARD, self::META_CARD_SETTINGS, array(
			'type'              => 'object',
			'single'            => true,
			'sanitize_callback' => array( __CLASS__, '_sanitize_settings' ),
		) );

		register_post_meta( self::CPT_CARD, self::META_CARD_POSITION, array(
			'type'              => 'object',
			'single'            => true,
			'sanitize_callback' => array( __CLASS__, '_sanitize_position' ),
		) );

		register_post_meta( self::CPT_CARD, self::META_CARD_SIZE, array(
			'type'              => 'object',
			'single'            => true,
			'sanitize_callback' => array( __CLASS__, '_sanitize_size' ),
		) );

		register_meta( 'user', self::META_PRIMARY, array(
			'type'              => 'integer',
			'single'            => true,
			'sanitize_callback' => 'absint',
			'auth_callback'     => array( __CLASS__, '_auth_primary' ),
			'show_in_rest'      => array(
				'schema' => array(
					'type'    => 'integer',
					'context' => array( 'edit' ),
				)
			),
		) );
	}

	/**
	 * Delete all cards when a dashboard is deleted.
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function after_delete_post( $post_id, $post ) {
		if ( $post->post_type !== self::CPT_DASHBOARD ) {
			return;
		}

		delete_metadata( 'user', 0, self::META_PRIMARY, $post_id, true );

		foreach ( ITSEC_Dashboard_Util::get_dashboard_cards( $post_id ) as $post ) {
			wp_delete_post( $post->ID );
		}
	}

	/**
	 * Sanitize the "role" metadata.
	 *
	 * @param string $role
	 *
	 * @return string
	 */
	public static function _sanitize_role( $role ) {
		return array_key_exists( $role, wp_roles()->roles ) ? $role : '';
	}

	/**
	 * Sanitize the "card" metadata.
	 *
	 * @param string $card
	 *
	 * @return string
	 */
	public static function _sanitize_card( $card ) {
		return (string) preg_replace( '/[^\w_-]/', '', $card );
	}

	/**
	 * Sanitize the "settings" metadata.
	 *
	 * @param mixed $settings
	 *
	 * @return array
	 */
	public static function _sanitize_settings( $settings ) {
		return is_array( $settings ) ? $settings : array();
	}

	/**
	 * Sanitize the "position" metadata.
	 *
	 * @param mixed $position
	 *
	 * @return array
	 */
	public static function _sanitize_position( $position ) {

		$sanitized = array();

		if ( ! is_array( $position ) ) {
			return $sanitized;
		}

		require_once( dirname( __FILE__ ) . '/class-itsec-dashboard-util.php' );

		foreach ( $position as $breakpoint => $entry ) {
			if ( ! in_array( $breakpoint, ITSEC_Dashboard_Util::$breakpoints, true ) ) {
				continue;
			}

			$sanitized[ $breakpoint ] = self::_sanitize_position_entry( $entry );
		}

		return $sanitized;
	}

	/**
	 * Sanitize a single position value for a breakpoint.
	 *
	 * @param array|mixed $position
	 *
	 * @return array
	 */
	private static function _sanitize_position_entry( $position ) {
		if ( ! is_array( $position ) || ! isset( $position['x'], $position['y'] ) ) {
			return array();
		}

		return array(
			'x' => absint( $position['x'] ),
			'y' => absint( $position['y'] ),
		);
	}

	/**
	 * Sanitize the "size" metadata.
	 *
	 * @param mixed $size
	 *
	 * @return array
	 */
	public static function _sanitize_size( $size ) {

		$sanitized = array();

		if ( ! is_array( $size ) ) {
			return $sanitized;
		}

		require_once( dirname( __FILE__ ) . '/class-itsec-dashboard-util.php' );

		foreach ( $size as $breakpoint => $entry ) {
			if ( ! in_array( $breakpoint, ITSEC_Dashboard_Util::$breakpoints, true ) ) {
				continue;
			}

			$sanitized[ $breakpoint ] = self::_sanitize_size_entry( $entry );
		}

		return $sanitized;
	}

	/**
	 * Sanitize a single size value for a breakpoint.
	 *
	 * @param array|mixed $size
	 *
	 * @return array
	 */
	private static function _sanitize_size_entry( $size ) {
		if ( ! is_array( $size ) || ! isset( $size['w'], $size['h'] ) ) {
			return array();
		}

		return array(
			'w' => absint( $size['w'] ),
			'h' => absint( $size['h'] ),
		);
	}

	/**
	 * Authorization callback to check if a user can set the primary dashboard meta key.
	 *
	 * @param bool   $allowed
	 * @param string $meta_key
	 * @param int    $user_id
	 *
	 * @return bool
	 */
	public static function _auth_primary( $allowed, $meta_key, $user_id ) {
		return current_user_can( 'edit_user', $user_id );
	}

	/**
	 * Consolidate events on a daily schedule.
	 */
	public function run_consolidate_events() {
		require_once( dirname( __FILE__ ) . '/class-itsec-dashboard-util.php' );
		ITSEC_Dashboard_Util::consolidate_events();
	}

	/**
	 * Handle custom capabilities for the dashboard.
	 *
	 * @param array  $caps
	 * @param string $cap
	 * @param int    $user_id
	 * @param array  $args
	 *
	 * @return array
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {

		$user_id = (int) $user_id;

		switch ( $cap ) {
			case 'itsec_dashboard_access':
			case 'itsec_dashboard_menu':
				if ( user_can( $user_id, 'itsec_create_dashboards' ) ) {
					return array();
				}

				require_once( dirname( __FILE__ ) . '/class-itsec-dashboard-util.php' );

				if ( ITSEC_Dashboard_Util::get_shared_dashboards( $user_id, 'ids' ) ) {
					return array();
				}

				return array( 'do_not_allow' );
			case 'itsec_view_dashboard':
				if ( empty( $args[0] ) || ! ( $post = get_post( $args[0] ) ) || self::CPT_DASHBOARD !== $post->post_type ) {
					return array( 'do_not_allow' );
				}

				if ( $user_id === (int) $post->post_author && user_can( $user_id, 'itsec_create_dashboards' ) ) {
					return array();
				}

				$uids = get_post_meta( $post->ID, self::META_SHARE_USER );

				if ( in_array( $user_id, $uids, false ) ) {
					return array();
				}

				$user = get_userdata( $user_id );

				foreach ( get_post_meta( $post->ID, self::META_SHARE_ROLE ) as $role ) {
					if ( in_array( $role, $user->roles, true ) ) {
						return array();
					}
				}

				return array( 'do_not_allow' );
			case 'itsec_edit_dashboard':
				if ( empty( $args[0] ) || ! ( $post = get_post( $args[0] ) ) || self::CPT_DASHBOARD !== $post->post_type ) {
					return array( 'do_not_allow' );
				}

				if ( $user_id === (int) $post->post_author && user_can( $user_id, 'itsec_create_dashboards' ) ) {
					return array();
				}

				return array( 'do_not_allow' );
			case 'itsec_create_dashboards':
				if ( ! $user = get_userdata( $user_id ) ) {
					return array( 'do_not_allow' );
				}

				if ( user_can( $user_id, ITSEC_Core::get_required_cap() ) ) {
					return array();
				}

				$group = ITSEC_Modules::get_setting( 'dashboard', 'group' );

				if ( ! $this->matcher->matches( User_Groups\Match_Target::for_user( $user ), $group ) ) {
					return array( 'do_not_allow' );
				}

				return array();
		}

		return $caps;
	}

	/**
	 * Create an event for certain log items.
	 *
	 * @param array $data
	 */
	public function log_add( $data ) {
		list( $code ) = array_pad( explode( '::', $data['code'] ), 2, '' );

		switch ( $data['module'] ) {
			case 'brute_force':
				switch ( $code ) {
					case 'auto-ban-admin-username':
					case 'invalid-login':
						ITSEC_Dashboard_Util::record_event( 'local-brute-force' );
						break;
				}
				break;
			case 'ipcheck':
				switch ( $code ) {
					case 'failed-login-by-blocked-ip':
					case 'successful-login-by-blocked-ip':
						ITSEC_Dashboard_Util::record_event( 'network-brute-force' );
						break;
				}
				break;
			case 'lockout':
				switch ( $code ) {
					case 'host-triggered-blacklist':
						// blacklist-four_oh_four, blacklist-brute_force, blacklist-brute_force_admin_user, blacklist-recaptcha
						ITSEC_Dashboard_Util::record_event( 'blacklist-' . $data['data']['module'] );
						break;
					case 'host-lockout':
						ITSEC_Dashboard_Util::record_event( 'lockout-host' );
						break;
					case 'user-lockout':
						ITSEC_Dashboard_Util::record_event( 'lockout-user' );
						break;
					case 'username-lockout':
						ITSEC_Dashboard_Util::record_event( 'lockout-username' );
						break;
				}
				break;
			case 'firewall':
				if ( $data['type'] === 'action' ) {
					ITSEC_Dashboard_Util::record_event( 'firewall-block' );
				}
				break;
		}
	}

	public function get_export_slug(): string {
		return 'dashboard';
	}

	public function get_export_title(): string {
		return __( 'Security Dashboard', 'better-wp-security' );
	}

	public function get_export_description(): string {
		return __( 'Security dashboards and cards.', 'better-wp-security' );
	}

	public function get_export_options_schema(): array {
		return [];
	}

	public function get_export_schema(): array {
		return [
			'type'  => 'array',
			'items' => [
				'type'       => 'object',
				'properties' => [
					'id'         => [
						'type' => 'integer',
					],
					'created_by' => [
						'$ref' => '#/definitions/user',
					],
					'created_at' => [
						'type'   => 'string',
						'format' => 'date-time',
					],
					'label'      => [
						'type' => 'string',
					],
					'layout'     => [
						'type' => 'object',
					],
					'shares'     => [
						'type'       => 'object',
						'properties' => [
							'users' => [
								'type'  => 'array',
								'items' => [
									'$ref' => '#/definitions/user',
								],
							],
							'roles' => [
								'type'  => 'array',
								'items' => [
									'$ref' => '#/definitions/role'
								],
							],
						],
					],
					'cards'      => [
						'type'       => 'array',
						'properties' => [
							'id'       => [
								'type' => 'integer',
							],
							'type'     => [
								'type' => 'string',
							],
							'size'     => [
								'type' => 'object',
							],
							'position' => [
								'type' => 'object',
							],
							'settings' => [
								'type' => 'object',
							],
						],
					],
				],
			],
		];
	}

	public function get_transformations(): array {
		return [
			new class implements Transformation {
				public function transform( Export $export, Import_Context $context ): Export {
					$data = $export->get_data( 'dashboard' );

					foreach ( $data as &$dashboard ) {
						$dashboard['created_by']      = Export::format_user( $context->get_mapped_user( $dashboard['created_by'] ) );
						$dashboard['shares']['users'] = $context->map_user_list( $dashboard['shares']['users'] );
						$dashboard['shares']['roles'] = $context->map_role_list( $dashboard['shares']['roles'] );
					}

					return $export->with_data( 'dashboard', $data );
				}

				public function get_user_paths(): array {
					return [ '*.created_by', '*.shares.users' ];
				}

				public function get_role_paths(): array {
					return [ '*.shares.roles' ];
				}
			}
		];
	}

	public function export( $options ): Result {
		$dashboards = new \WP_Query( [
			'post_type'      => self::CPT_DASHBOARD,
			'no_found_rows'  => true,
			'posts_per_page' => - 1,
		] );
		$cards      = new \WP_Query( [
			'post_type'      => self::CPT_CARD,
			'no_found_rows'  => true,
			'posts_per_page' => - 1,
		] );

		$cards_by_dashboard = [];

		foreach ( $cards->posts as $post ) {
			$cards_by_dashboard[ $post->post_parent ][] = [
				'id'       => $post->ID,
				'type'     => get_post_meta( $post->ID, self::META_CARD, true ),
				'size'     => get_post_meta( $post->ID, self::META_CARD_SIZE, true ),
				'position' => get_post_meta( $post->ID, self::META_CARD_POSITION, true ),
				'settings' => get_post_meta( $post->ID, self::META_CARD_SETTINGS, true ),
			];
		}

		return Result::success( array_map( static function ( \WP_Post $dashboard ) use ( $cards_by_dashboard ) {
			$user = get_userdata( $dashboard->post_author ) ?: null;

			return [
				'id'         => $dashboard->ID,
				'created_by' => Export::format_user( $user ),
				'created_at' => \ITSEC_Lib::to_rest_date( $dashboard->post_date_gmt ),
				'label'      => get_the_title( $dashboard ),
				'cards'      => $cards_by_dashboard[ $dashboard->ID ] ?? [],
				'shares'     => [
					'users' => array_map( static function ( $user_id ) {
						return Export::format_user( get_userdata( $user_id ) ?: null );
					}, get_post_meta( $dashboard->ID, self::META_SHARE_USER ) ),
					'roles' => array_map( [ Export::class, 'format_role' ], get_post_meta( $dashboard->ID, self::META_SHARE_ROLE ) ),
				],
			];
		}, $dashboards->posts ) );
	}

	public function import( Export $from, Import_Context $context ): Result {
		if ( ! $dashboards = $from->get_data( $this->get_export_slug() ) ) {
			return Result::success();
		}

		$query = new WP_Query( [
			'post_type'      => self::CPT_DASHBOARD,
			'posts_per_page' => 500,
		] );

		foreach ( $query->posts as $post ) {
			wp_delete_post( $post->ID, true );
		}

		$result = Result::success();

		foreach ( $dashboards as $dashboard ) {
			$author = $context->get_mapped_user( $dashboard['created_by'] );

			if ( ! $author && ITSEC_Core::current_user_can_manage() ) {
				$author = wp_get_current_user();
			}

			if ( ! $author ) {
				continue;
			}

			$post_id = wp_insert_post( [
				'post_type'   => self::CPT_DASHBOARD,
				'post_author' => $author->ID,
				'post_status' => 'publish',
				'post_title'  => $dashboard['label'],
			], true );

			if ( is_wp_error( $post_id ) ) {
				$result->add_warning_message( sprintf(
					__( 'Could not create "%1$s" dashboard for "%2$s": %3$s', 'better-wp-security' ),
					$dashboard['label'],
					$author->display_name,
					$post_id->get_error_message()
				) );

				continue;
			}

			foreach ( $dashboard['shares']['users'] as $user ) {
				if ( $mapped = $context->get_mapped_user( $user ) ) {
					add_post_meta( $post_id, self::META_SHARE_USER, $mapped->ID );
				}
			}

			foreach ( $dashboard['shares']['roles'] as $role ) {
				if ( $mapped = $context->get_mapped_role( $role['slug'] ) ) {
					add_post_meta( $post_id, self::META_SHARE_ROLE, $mapped );
				}
			}

			foreach ( $dashboard['cards'] as $card ) {
				wp_insert_post( [
					'post_type'   => self::CPT_CARD,
					'post_author' => $author->ID,
					'post_parent' => $post_id,
					'post_status' => 'publish',
					'meta_input'  => array_filter( [
						self::META_CARD          => $card['type'],
						self::META_CARD_SIZE     => $card['size'],
						self::META_CARD_POSITION => $card['position'],
						self::META_CARD_SETTINGS => $card['settings'],
					] ),
				] );
			}
		}

		return $result;
	}
}
