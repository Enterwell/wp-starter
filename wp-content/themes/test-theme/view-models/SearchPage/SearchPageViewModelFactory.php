<?php

namespace Ew;

/**
 * Class SearchPageViewModelFactory
 * @package Ew
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
		$vm = new SearchPageViewModel();

		$archiveVm = $this->archiveViewModelFactory->build( $wp_query );

		// Paging variables
		$vm->currentPage  = $archiveVm->currentPage;
		$vm->pagesTotal   = $archiveVm->pagesTotal;
		$vm->postsPerPage = $archiveVm->postsPerPage;
		$vm->searchQuery  = ! empty( $_GET['s'] ) ? $_GET['s'] : '';
		$vm->posts        = $archiveVm->posts;

		return $vm;
	}
}