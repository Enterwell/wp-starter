<?php

final class ITSEC_Admin_Notice_Action_Link implements ITSEC_Admin_Notice_Action {

	/** @var string */
	private $uri;

	/** @var string */
	private $title;

	/** @var string */
	private $style;

	/** @var callable|null */
	private $callback;

	/**
	 * ITSEC_Admin_Notice_Action_Link constructor.
	 *
	 * @param string        $uri
	 * @param string        $title
	 * @param string        $style
	 * @param callable|null $callback
	 */
	public function __construct( $uri, $title, $style = self::S_LINK, $callback = null ) {
		$this->uri      = $uri;
		$this->title    = $title;
		$this->style    = $style;
		$this->callback = $callback;
	}

	public function handle( WP_User $user, array $data ) {
		return $this->callback ? call_user_func( $this->callback, $user, $data ) : null;
	}

	public function get_title() {
		return $this->title;
	}

	public function get_style() {
		return $this->style;
	}

	public function get_uri() {
		return $this->uri;
	}
}
