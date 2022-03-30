<?php

namespace EwStarter\View_Models\Search_Page;

use EwStarter\View_Models\Archive_Page\Archive_Page_View_Model_Factory;

/**
 * Class SearchPageViewModelFactory
 * @package EwStarter
 */
class Search_Page_View_Model_Factory
{
	/**
	 * @var Archive_Page_View_Model_Factory
	 */
	private Archive_Page_View_Model_Factory $archive_page_view_model_factory;

	/**
	 *
	 */
	public function __construct()
	{
		$this->archive_page_view_model_factory = new Archive_Page_View_Model_Factory();
	}

	/**
	 * Build the view model
	 *
	 * @param $wp_query
	 *
	 * @return Search_Page_View_Model
	 */
	public function build($wp_query): Search_Page_View_Model
	{
		// Create view model
		$vm = new Search_Page_View_Model();

		// Build the archive view model
		$archiveVm = $this->archive_page_view_model_factory->build($wp_query);

		// Set variables from $archiveVm
		$vm->posts = $archiveVm->posts;
		// Paging variables
		$vm->current_page = $archiveVm->current_page;
		$vm->pages_total = $archiveVm->pages_total;
		$vm->posts_per_page = $archiveVm->posts_per_page;
		// Set searchQuery from $_GET
		$vm->search_query = !empty($_GET['s']) ? $_GET['s'] : '';

		// Return view model
		return $vm;
	}
}
