<?php

namespace EwStarter;

/**
 * Class ArchivePageViewModel
 * @package EwStarter
 */
class ArchivePageViewModel extends BaseViewModel {
	/**
	 * @var array
	 */
	public $posts;

	/**
	 * @var int
	 */
	public $currentPage;

	/**
	 * @var int
	 */
	public $pagesTotal;

	/**
	 * @var int
	 */
	public $postsPerPage;
}