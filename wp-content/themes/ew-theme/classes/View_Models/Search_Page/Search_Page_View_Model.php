<?php

namespace EwStarter\View_Models\Search_Page;

use EwStarter\View_Models\Archive_Page\Archive_Page_View_Model;

/**
 * Class SearchPageViewModel
 * @package EwStarter
 */
class Search_Page_View_Model extends Archive_Page_View_Model {
	/**
	 * @var string
	 */
	public string $search_query;
}
