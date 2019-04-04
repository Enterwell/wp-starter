<?php

namespace Ew;

/**
 * Class SinglePostViewModelFactory
 * @package Ew
 */
class SinglePostViewModelFactory {
	/**
	 * Build the view model
	 *
	 * @param \WP_Post $wp_post
	 *
	 * @return SinglePostViewModel
	 */
	public function build( $wp_post ) {
		// Create view model
		$vm = new SinglePostViewModel();

		// Set variables from $wp_post
		$vm->postTitle   = $wp_post->post_title;
		$vm->postContent = apply_filters( 'the_content', $wp_post->post_content );

		// Return view model
		return $vm;
	}
}