<?php

class ITSEC_Admin_Notice_Managers_Only implements ITSEC_Admin_Notice {
	/** @var ITSEC_Admin_Notice */
	private $notice;

	/**
	 * ITSEC_Admin_Notice_Admin_Only constructor.
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
		if ( ! user_can( $context->get_user()->ID, ITSEC_Core::get_required_cap() ) ) {
			return false;
		}

		return $this->notice->show_for_context( $context );
	}

	public function get_actions() {
		return $this->notice->get_actions();
	}
}
