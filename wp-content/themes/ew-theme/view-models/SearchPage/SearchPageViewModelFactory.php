<?php

namespace EwStarter;

/**
 * Class SearchPageViewModelFactory
 * @package EwStarter
 */
class SearchPageViewModelFactory {
	/**
	 * @var ArchivePageViewModelFactory
	 */
	private $archiveViewModelFactory;

	/**
	 * SearchPageViewModelFactory constructor.
	 */
	public function __construct() {
		$this->archiveViewModelFactory = new ArchivePageViewModelFactory();
	}

	/**
	 * Build the view model
	 *
	 * @param $wp_query
	 *
	 * @return SearchPageViewModel
	 */
	public function build( $wp_query ) {
		// Create view model
		$vm = new SearchPageViewModel();

		// Build the archive view model
		$archiveVm = $this->archiveViewModelFactory->build( $wp_query );

		// Set variables from $archiveVm
		$vm->posts        = $archiveVm->posts;
		// Paging variables
		$vm->currentPage  = $archiveVm->currentPage;
		$vm->pagesTotal   = $archiveVm->pagesTotal;
		$vm->postsPerPage = $archiveVm->postsPerPage;
		// Set searchQuery from $_GET
		$vm->searchQuery  = ! empty( $_GET['s'] ) ? $_GET['s'] : '';

		// Return view model
		return $vm;
	}
}