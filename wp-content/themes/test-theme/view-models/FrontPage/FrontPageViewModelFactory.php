<?php

namespace Ew;

/**
 * Class FrontPageViewModelFactory
 * @package Ew
 */
class FrontPageViewModelFactory {
	/**
	 * Build the view model
	 *
	 * @return FrontPageViewModel
	 */
	public function build() {
		$vm        = new FrontPageViewModel();
		$vm->title = 'Front page';

		return $vm;
	}
}