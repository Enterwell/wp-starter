<?php

final class ITSEC_Admin_Notice_Context {

	const AJAX = 'ajax';
	const ADMIN_ACTION = 'admin-action';
	const FRONT = 'front';

	/** @var WP_User */
	private $user;

	/** @var string */
	private $screen_id;

	/**
	 * ITSEC_Admin_Notice_Context constructor.
	 *
	 * @param WP_User $user
	 * @param string  $screen_id
	 */
	public function __construct( WP_User $user, $screen_id ) {
		$this->user      = $user;
		$this->screen_id = $screen_id;
	}

	/**
	 * Create the context from the globally defined state.
	 *
	 * @return ITSEC_Admin_Notice_Context
	 * @throws Exception
	 */
	public static function from_global_state() {
		if ( function_exists( 'get_current_screen' ) ) {
			if ( ! $screen = get_current_screen() ) {
				throw new Exception( 'Cannot instantiate a notice context from global state before the WP_Screen object is available.' );
			}

			$screen_id = $screen->id;
		} else {
			$screen_id = self::FRONT;
		}

		return new self( wp_get_current_user(), $screen_id );
	}

	/**
	 * Get the user the notice is being interacted with.
	 *
	 * @return WP_User
	 */
	public function get_user() {
		return $this->user;
	}

	/**
	 * Get the ID of the screen being displayed.
	 *
	 * @return string
	 */
	public function get_screen_id() {
		return $this->screen_id;
	}
}
