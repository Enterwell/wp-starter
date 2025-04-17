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
	public string $title;

	/**
	 * @var string
	 */
	public string $excerpt;

	/**
	 * @var string
	 */
	public string $link;

	/**
	 * ArchivePageItem constructor.
	 *
	 * @param string $title
	 * @param string $excerpt
	 * @param string $link
	 */
	public function __construct( string $title, string $excerpt, string $link ) {
		$this->title   = $title;
		$this->excerpt = $excerpt;
		$this->link    = $link;
	}
}
