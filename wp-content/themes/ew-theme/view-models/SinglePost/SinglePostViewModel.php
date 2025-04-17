<?php

namespace EwStarter;

use WP_Post;

/**
 * Class SinglePostViewModel
 * @package EwStarter
 */
class SinglePostViewModel extends BaseViewModel {
	/**
	 * @var string
	 */
	public string $content;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		/** @var WP_Post $post */
		global $post;

		$this->content = apply_filters( 'the_content', $post->post_content );
	}
}
