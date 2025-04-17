<?php

namespace EwStarter;

/**
 * Class BaseViewModel
 * @package EwStarter
 */
class BaseViewModel {
	/**
	 * @var MenuViewModel
	 */
	public MenuViewModel $navigationMenu;

	/**
	 * BaseViewModel constructor.
	 */
	public function __construct() {
		$this->navigationMenu = new MenuViewModel( EW_MAIN_NAV_MENU );
	}
}
