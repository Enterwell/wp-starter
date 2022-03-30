<?php

namespace EwStarter\View_Models\Archive_Page;

/**
 * Class Archive_Page_View_Model_Factory
 * @package EwStarter
 */
class Archive_Page_View_Model_Factory
{

	/**
	 * Build the view model
	 *
	 * @param \WP_Query $wp_query
	 *
	 * @return Archive_Page_View_Model
	 */
	public function build(\WP_Query $wp_query): Archive_Page_View_Model
	{
		// Create the view model
		$vm = new Archive_Page_View_Model();
		$vm->posts = [];

		// Set posts variable from $wp_query
		foreach ($wp_query->posts as $post) {
			$archive_item = new Archive_Page_Item($post->post_title, $post->post_excerpt, get_permalink($post));

			$vm->posts[] = $archive_item;
		}

		// Paging variables
		$current_page = $wp_query->query_vars['paged'];
		$vm->current_page = $current_page > 1 ? $current_page : 1;
		$vm->pages_total = $wp_query->max_num_pages;
		$vm->posts_per_page = $wp_query->query_vars['posts_per_page'];

		// Return view model
		return $vm;
	}
}
