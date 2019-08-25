<?php

namespace EwStarter;

/**
 * Class ArchivePageItem
 * @package EwStarter
 */
class ArchivePageItem {
	/**
	 * @var string
	 */
	public $post_title;

	/**
	 * @var string
	 */
	public $post_excerpt;

	/**
	 * @var string
	 */
	public $link;

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