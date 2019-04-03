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
		$vm = new SinglePostViewModel();

		$vm->post_title   = $wp_post->post_title;
		$vm->post_content = $wp_post->post_content;

		return $vm;
	}
}