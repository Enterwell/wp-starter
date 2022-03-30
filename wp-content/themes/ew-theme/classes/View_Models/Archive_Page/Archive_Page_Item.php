<?php

namespace EwStarter\View_Models\Archive_Page;

/**
 * Class ArchivePageItem
 * @package EwStarter
 */
class Archive_Page_Item {
	/**
	 * @var string
	 */
	public string $post_title;

	/**
	 * @var string
	 */
	public string $post_excerpt;

	/**
	 * @var string
	 */
	public string $link;

	/**
	 * ArchivePageItem constructor.
	 *
	 * @param $title
	 * @param $excerpt
	 * @param $link
	 */
	public function __construct( $title, $excerpt, $link ) {
		$this->post_title   = $title;
		$this->post_excerpt = $excerpt;
		$this->link         = $link;
	}
}
