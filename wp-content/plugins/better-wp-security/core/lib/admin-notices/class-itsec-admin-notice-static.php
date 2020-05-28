<?php

class ITSEC_Admin_Notice_Static implements ITSEC_Admin_Notice {

	private $id;
	private $message;
	private $severity;
	private $title;

	/**
	 * ITSEC_Admin_Notice_Concrete constructor.
	 *
	 * @param string $id       Globally unique notice ID.
	 * @param string $message  The message to display to the user.
	 * @param string $title
	 * @param string $severity The message's severity.
	 */
	public function __construct( $id, $message, $title = '', $severity = self::S_INFO ) {
		$this->id       = $id;
		$this->message  = $message;
		$this->severity = $severity;
		$this->title    = $title;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_title() {
		return $this->title;
	}

	public function get_message() {
		return $this->message;
	}

	public function get_severity() {
		return $this->severity;
	}

	public function get_meta() {
		return array();
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
		return true;
	}

	public function get_actions() {
		return array();
	}
}
