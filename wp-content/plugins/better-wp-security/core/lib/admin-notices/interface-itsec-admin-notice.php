<?php

interface ITSEC_Admin_Notice {

	const S_ERROR = 'error';
	const S_WARN = 'warning';
	const S_INFO = 'info';
	const S_SUCCESS = 'success';

	const M_CREATED_AT = 'created_at';

	/**
	 * Get a unique id for this notice.
	 *
	 * @return string|int
	 */
	public function get_id();

	/**
	 * Get the title or headline for a notice.
	 *
	 * May be an empty string.
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Get the message to display in the notice.
	 *
	 * May contain HTML.
	 *
	 * @return string
	 */
	public function get_message();

	/**
	 * Get meta information.
	 *
	 * Expected format:
	 *  Array of the following keyed by a unique slug.
	 *      - string $label
	 *      - string $value
	 *      - string $formatted
	 *
	 * @return array[]
	 */
	public function get_meta();

	/**
	 * Get the severity level of the notice.
	 *
	 * @return string
	 */
	public function get_severity();

	/**
	 * Should the notice be displayed to the given user.
	 *
	 * @param ITSEC_Admin_Notice_Context $context
	 *
	 * @return bool
	 */
	public function show_for_context( ITSEC_Admin_Notice_Context $context );

	/**
	 * Get a list of all the available actions that can be taken on this notice.
	 *
	 * @return ITSEC_Admin_Notice_Action[]
	 */
	public function get_actions();
}
