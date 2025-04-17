<?php

namespace EwStarter;

use WP_Query;

/**
 * Class ArchivePageViewModelFactory
 * @package EwStarter
 */
class ArchivePageViewModelFactory {
	/**
	 * Build the view model
	 *
	 * @param WP_Query|null $wpQuery
	 *
	 * @return ArchivePageViewModel
	 */
	public function build( WP_Query $wpQuery = null ): ArchivePageViewModel {
		if ( ! $wpQuery ) {
			global $wp_query;
			$wpQuery = $wp_query;
		}

		// Create the view model
		$vm        = new ArchivePageViewModel();
		$vm->posts = [];

		// Set posts variable from $wp_query
		foreach ( $wpQuery->posts as $post ) {
			$archiveItem = new ArchivePageItem( $post->post_title, $post->post_excerpt, get_permalink( $post ) ?: "" );

			$vm->posts[] = $archiveItem;
		}

		// Paging variables
		$currentPage      = $wpQuery->query_vars['paged'];
		$vm->currentPage  = max( $currentPage, 1 );
		$vm->pagesTotal   = $wpQuery->max_num_pages;
		$vm->postsPerPage = $wpQuery->query_vars['posts_per_page'];

		// Return view model
		return $vm;
	}
}
