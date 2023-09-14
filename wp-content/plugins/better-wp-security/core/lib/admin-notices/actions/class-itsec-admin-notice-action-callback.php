<?php

class ITSEC_Admin_Notice_Action_Callback implements ITSEC_Admin_Notice_Action {

	private $style;
	private $title;
	private $callable;

	/**
	 * ITSEC_Admin_Notice_Action_Button constructor.
	 *
	 * @param string   $style
	 * @param string   $title
	 * @param callable $callable
	 */
	public function __construct( $style, $title, $callable ) {
		$this->style    = $style;
		$this->title    = $title;
		$this->callable = $callable;
	}

	public function handle( WP_User $user, array $data ) {
		return call_user_func( $this->callable, $user, $data );
	}

	public function get_title() {
		return $this->title;
	}

	public function get_style() {
		return $this->style;
	}

	public function get_uri() {
		return '';
	}

	public function get_route(): string {
		return '';
	}
}
