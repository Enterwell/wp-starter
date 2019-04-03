<?php

namespace Ew;

/**
 * Class SinglePageViewModelFactory
 * @package Ew
 */
class SinglePageViewModelFactory {
	/**
	 * Build the view model
	 *
	 * @param \WP_Post $page
	 * @return SinglePageViewModel
	 */
	public function build( $page ) {
		$vm = new SinglePageViewModel();

		$vm->page_title   = $page->post_title;
		$vm->page_content = $page->post_content;

		return $vm;
	}
}