<?php

class ITSEC_Admin_Notice_Globally_Dismissible implements ITSEC_Admin_Notice {
	/** @var ITSEC_Admin_Notice */
	private $notice;

	/**
	 * ITSEC_Admin_Notice_Dismissible constructor.
	 *
	 * @param ITSEC_Admin_Notice $notice
	 */
	public function __construct( ITSEC_Admin_Notice $notice ) { $this->notice = $notice; }

	public function get_id() {
		return $this->notice->get_id();
	}

	public function get_title() {
		return $this->notice->get_title();
	}

	public function get_message() {
		return $this->notice->get_message();
	}

	public function get_severity() {
		return $this->notice->get_severity();
	}

	public function get_meta() {
		return $this->notice->get_meta();
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
		$dismissed = $this->get_storage();

		if ( in_array( $this->get_id(), $dismissed, true ) ) {
			return false;
		}

		return $this->notice->show_for_context( $context );
	}

	public function get_actions() {
		return array_merge( $this->notice->get_actions(), array(
			'dismiss' => new ITSEC_Admin_Notice_Action_Callback(
				ITSEC_Admin_Notice_Action::S_CLOSE,
				esc_html__( 'Dismiss', 'better-wp-security' ),
				array( $this, '_handle_dismiss' )
			),
		) );
	}

	public function _handle_dismiss( WP_User $user, array $data ) {
		$dismissed   = $this->get_storage();
		$dismissed[] = $this->get_id();
		$this->save_storage( $dismissed );

		return null;
	}

	private function get_storage() {
		$dismissed = get_site_option( 'itsec_dismissed_notices', array() );

		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}

		return $dismissed;
	}

	private function save_storage( $storage ) {
		update_site_option( 'itsec_dismissed_notices', $storage );
	}
}
