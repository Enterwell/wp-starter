<?php

namespace Ew;

/**
 * Class BaseViewModel
 * @package Ew
 */
class BaseViewModel {
	/**
	 * @var MenuViewModel
	 */
	public $footerNavigation;

	/**
	 * @var string
	 */
	public $themeUrl;

	/**
	 * BaseViewModel constructor.
	 */
	public function __construct() {
		$this->footerNavigation = new MenuViewModel( EW_MAIN_NAV_MENU );
		$this->themeUrl         = THEME_URL;
	}
}