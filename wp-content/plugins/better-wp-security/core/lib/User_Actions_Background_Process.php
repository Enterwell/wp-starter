<?php

namespace iThemesSecurity\Lib;

class User_Actions_Background_Process extends Background_Process {

	private const STORAGE = 'solid_security_user_actions';

	private $id;
	private $user;
	private $actions;
	private $query;

	public function __construct( string $id, \WP_User $user, array $actions, ?array $query ) {
		$this->id      = $id;
		$this->action  = 'user_action_' . $id;
		$this->user    = $user;
		$this->actions = $actions;
		$this->query   = $query;

		parent::__construct();

		add_action( 'itsec_scheduled_' . $this->action . '_queue_items', [ $this, 'on_queue_items' ] );
	}

	protected function task( $item ) {
		$user = get_userdata( $item );

		if ( ! $user || get_user_meta( $item, '_solid_user_action_' . $this->id ) ) {
			return false;
		}

		$actioned_by = $this->user;

		foreach ( $this->actions as $action => $args ) {
			switch ( $action ) {
				case 'force-logout':
					\WP_Session_Tokens::get_instance( $item )->destroy_all();
					break;
				case 'reset-password':
					wp_set_password( wp_generate_password( 24 ), $user->ID );
					retrieve_password( $user->user_login );
					break;
				case 'delete':
					wp_delete_user( $item );
					break;
				default:
					/**
					 * Fires when a user action should be processed.
					 *
					 * The dynamic portion of this hook `$action` refers to the action
					 * slug that should be applied.
					 *
					 * @param \WP_User $user
					 * @param \WP_User $actioned_by
					 * @param mixed    $args
					 */
					do_action( "itsec_user_action_{$action}", $user, $actioned_by, $args );
					break;
			}
		}

		update_user_meta( $item, '_solid_user_action_' . $this->id, time() );

		return false;
	}

	protected function completed() {
		parent::completed();

		if ( \ITSEC_Core::get_scheduler()->is_single_scheduled( $this->action . '_queue_items', null ) ) {
			$this->pause();

			return;
		}

		$processes = get_site_option( self::STORAGE );

		if ( is_array( $processes ) ) {
			unset( $processes[ $this->id ] );
		}

		if ( ! $processes ) {
			delete_site_option( self::STORAGE );
		} else {
			update_site_option( self::STORAGE, $processes );
		}

		delete_metadata( 'user', 0, '_solid_user_action_' . $this->id, '', true );
	}

	public function on_queue_items( \ITSEC_Job $job ) {
		$this->push_queried_items( $job->get_data()['page'] );
	}

	public function push_queried_items( int $page ): void {
		$request = new \WP_REST_Request( 'GET', '/wp/v2/users' );
		$request->set_query_params( array_merge(
			$this->query,
			[
				'order'    => 'asc',
				'orderby'  => 'id',
				'_fields'  => 'id',
				'per_page' => 100,
				'page'     => $page,
			]
		) );
		$current_user_id = get_current_user_id();
		wp_set_current_user( $this->user->ID );
		$response = rest_do_request( $request );
		wp_set_current_user( $current_user_id );

		if ( $response->is_error() ) {
			return;
		}

		if ( ( $response->headers['X-WP-TotalPages'] ?? 0 ) > $page ) {
			\ITSEC_Core::get_scheduler()->schedule_once(
				time() + 30,
				$this->action . '_queue_items',
				[ 'page' => $page + 1 ]
			);
		}

		foreach ( $response->get_data() as $user ) {
			$this->push_to_queue( $user['id'] );
		}

		$this->save();

		if ( $this->is_paused() ) {
			$this->resume();
		}
	}

	public static function create( \WP_User $user, array $actions, ?array $query ): self {
		$processes = get_site_option( self::STORAGE );

		if ( ! is_array( $processes ) ) {
			$processes = [];
		}

		$id = wp_generate_password( 6, false );

		$processes[ $id ] = [
			'user'    => $user->ID,
			'actions' => $actions,
			'query'   => $query,
		];

		update_site_option( self::STORAGE, $processes );

		return new self( $id, $user, $actions, $query );
	}

	public static function run_processes() {
		$processes = get_site_option( self::STORAGE );

		if ( ! is_array( $processes ) ) {
			return;
		}

		foreach ( $processes as $process_id => $data ) {
			if ( ! isset( $data['actions'], $data['user'] ) || ! is_array( $data['actions'] ) ) {
				continue;
			}

			$user = get_userdata( $data['user'] );

			if ( ! $user ) {
				continue;
			}

			new self( $process_id, $user, $data['actions'], $data['query'] );
		}
	}
}
