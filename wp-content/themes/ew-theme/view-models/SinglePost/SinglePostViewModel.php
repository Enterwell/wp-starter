<?php

namespace EwStarter;

/**
 * Class SinglePostViewModel
 * @package EwStarter
 */
class SinglePostViewModel extends BaseViewModel
{
	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $content;

	public function __construct(\WP_Post $wp_post)
	{
		parent::__construct();
		$this->title = $wp_post->post_title;
		$this->content = apply_filters('the_content', $wp_post->post_content);
	}
}
