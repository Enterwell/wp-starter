<?php

namespace Ew;

/**
 * Class ArchivePageViewModel
 * @package Ew
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