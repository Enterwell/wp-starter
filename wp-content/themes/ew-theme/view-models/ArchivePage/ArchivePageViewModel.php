<?php

namespace EwStarter;

use WP_Post;

/**
 * Class ArchivePageViewModel
 * @package EwStarter
 */
class ArchivePageViewModel extends BaseViewModel {

	/**
	 * @var WP_Post[]
	 */
	public array $posts;

	/**
	 * @var int
	 */
	public int $currentPage;

	/**
	 * @var int
	 */
	public int $pagesTotal;

	/**
	 * @var int
	 */
	public int $postsPerPage;
}
