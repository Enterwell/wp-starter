<?php

class ITSEC_Admin_Notice_Screen_Blacklist implements ITSEC_Admin_Notice {

	/** @var ITSEC_Admin_Notice */
	private $notice;

	/** @var string[] */
	private $blacklist;

	/**
	 * ITSEC_Admin_Notice_Screen_Blacklist constructor.
	 *
	 * @param ITSEC_Admin_Notice $notice
	 * @param string[]           $blacklist Screen IDs to blacklist.
	 */
	public function __construct( ITSEC_Admin_Notice $notice, array $blacklist ) {
		$this->notice    = $notice;
		$this->blacklist = $blacklist;
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

	public function get_severity() {
		return $this->notice->get_severity();
	}

	public function get_meta() {
		return $this->notice->get_meta();
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
		if ( in_array( $context->get_screen_id(), $this->blacklist, true ) ) {
			return false;
		}

		return $this->notice->show_for_context( $context );
	}

	public function get_actions() {
		return $this->notice->get_actions();
	}
}
