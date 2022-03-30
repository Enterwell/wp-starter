<?php

namespace EwStarter\View_Models;

/**
 * Class BaseViewModel
 * @package EwStarter
 */
class Base_View_Model {
	/** @var bool */
	public bool $is_admin_logged_in;

	/**
	 * @var Menu_View_Model
	 */
	public Menu_View_Model $navigation_menu;

	/**
	 * BaseViewModel constructor.
	 */
	public function __construct() {
		$this->is_admin_logged_in = current_user_can( 'administrator' );
		$this->navigation_menu    = new Menu_View_Model( EW_MAIN_NAV_MENU );
	}

	/**
	 * Gets basic js vars used in theme.
	 * @return array
	 */
	public function get_js_vars(): array {
		return [
			'ew' => [
				'apiData' => [
					'apiNonce' => wp_create_nonce( 'wp_rest' ),
					'apiUrl'   => '/wp-json/wp-ew/v1/'
				]
			],
		];
	}
}
