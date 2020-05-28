<?php

class ITSEC_Admin_Notice_Remind_Me implements ITSEC_Admin_Notice {
	/** @var ITSEC_Admin_Notice */
	private $notice;

	/** @var int */
	private $ttl;

	/**
	 * ITSEC_Admin_Notice_Remind_Me constructor.
	 *
	 * @param ITSEC_Admin_Notice $notice
	 * @param int                $ttl
	 */
	public function __construct( ITSEC_Admin_Notice $notice, $ttl ) {
		$this->notice = $notice;
		$this->ttl    = $ttl;
	}

	public function get_id() {
		return $this->notice->get_id();
	}

	public function get_title() {
		return $this->notice->get_title();
	}

	public function get_message() {
		return $this->notice->get_message();
	}

	public function get_meta() {
		return $this->notice->get_meta();
	}

	public function get_severity() {
		return $this->notice->get_severity();
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
		$storage = $this->get_storage();

		if ( isset( $storage[ $this->get_id() ] ) && $storage[ $this->get_id() ] + $this->ttl > ITSEC_Core::get_current_time_gmt() ) {
			return false;
		}

		return $this->notice->show_for_context( $context );
	}

	public function get_actions() {
		return array_merge( $this->notice->get_actions(), array(
			'remind_me' => new ITSEC_Admin_Notice_Action_Callback(
				ITSEC_Admin_Notice_Action::S_CLOSE,
				esc_html__( 'Remind Me Later', 'better-wp-security' ),
				array( $this, '_handle_remind_me' )
			)
		) );
	}

	public function _handle_remind_me() {
		$storage = $this->get_storage();

		$storage[ $this->get_id() ] = ITSEC_Core::get_current_time_gmt();
		$this->save_storage( $storage );

		return null;
	}

	private function get_storage() {
		$dismissed = get_site_option( 'itsec_remind_me_notices', array() );

		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}

		return $dismissed;
	}

	private function save_storage( $storage ) {
		update_site_option( 'itsec_remind_me_notices', $storage );
	}
}
