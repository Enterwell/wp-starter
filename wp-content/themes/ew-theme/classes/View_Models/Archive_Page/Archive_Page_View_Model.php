<?php

namespace EwStarter\View_Models\Archive_Page;

use EwStarter\View_Models\Base_View_Model;

/**
 * Class ArchivePageViewModel
 * @package EwStarter
 */
class Archive_Page_View_Model extends Base_View_Model {
	/**
	 * @var array
	 */
	public array $posts;

	/**
	 * @var int
	 */
	public int $current_page;

	/**
	 * @var int
	 */
	public int $pages_total;

	/**
	 * @var int
	 */
	public int $posts_per_page;
}
