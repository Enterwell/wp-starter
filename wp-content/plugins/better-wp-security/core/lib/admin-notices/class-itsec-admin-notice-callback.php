<?php

class ITSEC_Admin_Notice_Callback implements ITSEC_Admin_Notice {
	private $id;
	private $message;
	private $title;
	private $severity;

	/**
	 * ITSEC_Admin_Notice_Concrete constructor.
	 *
	 * @param string   $id       Globally unique notice ID.
	 * @param callable $message  Callable to return the message to display to the user.
	 * @param callable $title    Callable to return the title.
	 * @param string   $severity The message's severity.
	 */
	public function __construct( $id, $message, $title = null, $severity = self::S_INFO ) {
		$this->id       = $id;
		$this->message  = $message;
		$this->title    = $title;
		$this->severity = $severity;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_title() {
		return $this->title ? call_user_func( $this->title ) : '';
	}

	public function get_message() {
		return call_user_func( $this->message );
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
