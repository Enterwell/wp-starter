<?php

namespace EwStarter;

/**
 * Class SinglePageViewModelFactory
 * @package EwStarter
 */
class SinglePageViewModelFactory {
	/**
	 * Build the view model
	 *
	 * @param \WP_Post $page
	 *
	 * @return SinglePageViewModel
	 */
	public function build( $page ) {
		// Create view model
		$vm = new SinglePageViewModel();

		// Set the variables from $page
		$vm->pageTitle   = $page->post_title;
		$vm->pageContent = apply_filters( 'the_content', $page->post_content );

		// Return view model
		return $vm;
	}
}