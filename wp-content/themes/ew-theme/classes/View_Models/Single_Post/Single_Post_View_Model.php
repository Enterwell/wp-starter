<?php

namespace EwStarter\View_Models\Single_Post;

use EwStarter\View_Models\Base_View_Model;

/**
 * Class SinglePostViewModel
 * @package EwStarter
 */
class Single_Post_View_Model extends Base_View_Model
{
	/**
	 * @var string
	 */
	public string $title;

	/**
	 * @var string
	 */
	public string $content;

	public function __construct(\WP_Post $wp_post)
	{
		parent::__construct();
		$this->title = $wp_post->post_title;
		$this->content = apply_filters('the_content', $wp_post->post_content);
	}
}
