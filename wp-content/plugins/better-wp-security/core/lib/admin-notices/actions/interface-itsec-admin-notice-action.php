<?php

interface ITSEC_Admin_Notice_Action {

	const S_LINK = 'link';
	const S_BUTTON = 'button';
	const S_PRIMARY = 'primary';
	const S_CLOSE = 'close';

	/**
	 * Handle the action.
	 *
	 * @param WP_User $user
	 * @param array   $data
	 *
	 * @return WP_Error|null
	 */
	public function handle( WP_User $user, array $data );

	/**
	 * Get the title of this action.
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Get the action presentation style.
	 *
	 * @return string
	 */
	public function get_style();

	/**
	 * Get the URI that should be navigated to when triggering this action.
	 *
	 * @return string URI or empty string
	 */
	public function get_uri();
}

