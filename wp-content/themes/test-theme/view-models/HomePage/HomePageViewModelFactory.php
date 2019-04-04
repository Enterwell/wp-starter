<?php

namespace Ew;

/**
 * Class HomePageViewModelFactory
 * @package Ew
 */
class HomePageViewModelFactory {
	/**
	 * Build the view model
	 *
	 * @return HomePageViewModel
	 */
	public function build() {
		// Create view model and set the title
		$vm        = new HomePageViewModel();
		$vm->title = 'Front page';

		// Return view model
		return $vm;
	}
}