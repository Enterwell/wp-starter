<?php

final class ITSEC_Admin_Notice_Action_Link implements ITSEC_Admin_Notice_Action {

	/** @var string */
	private $uri;

	/** @var string */
	private $route;

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
		$this->route    = '';
		$this->title    = $title;
		$this->style    = $style;
		$this->callback = $callback;
	}

	/**
	 * Builds a Link action for a settings page route.
	 *
	 * @param string        $route
	 * @param string        $title
	 * @param string        $style
	 * @param callable|null $callback
	 *
	 * @return ITSEC_Admin_Notice_Action_Link
	 */
	public static function for_route( string $route, string $title, string $style = self::S_LINK, callable $callback = null ): self {
		$bits = explode( '#', $route );
		$uri  = ITSEC_Core::get_url_for_settings_route( $bits[0] );

		if ( isset( $bits[1] ) ) {
			$uri .= '#' . $bits[1];
		}

		$action        = new self( $uri, $title, $style, $callback );
		$action->route = $route;

		return $action;
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

	public function get_route(): string {
		return $this->route;
	}
}
