<?php

namespace EwStarter;

/**
 * Class ArchivePageViewModelFactory
 * @package EwStarter
 */
class ArchivePageViewModelFactory {

	/**
	 * Build the view model
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return ArchivePageViewModel
	 */
	public function build( $wp_query ) {
		// Create the view model
		$vm        = new ArchivePageViewModel();
		$vm->posts = [];

		// Set posts variable from $wp_query
		foreach ( $wp_query->posts as $post ) {
			$archive_item = new ArchivePageItem( $post->post_title, $post->post_excerpt, get_permalink( $post ) );

			$vm->posts[] = $archive_item;
		}

		// Paging variables
		$current_page     = $wp_query->query_vars['paged'];
		$vm->currentPage  = $current_page > 1 ? $current_page : 1;
		$vm->pagesTotal   = $wp_query->max_num_pages;
		$vm->postsPerPage = $wp_query->query_vars['posts_per_page'];

		// Return view model
		return $vm;
	}
}