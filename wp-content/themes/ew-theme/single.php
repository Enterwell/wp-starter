<?php
/**
 * The template for displaying all single posts and attachments
 */
use EwStarter\View_Models\Single_Post\Single_Post_View_Model;

$view_data = [
	'jsFile' => '',
	'bodyClass' => 'single-page',
	'vm' => new Single_Post_View_Model(get_post())
];

// Render view
ew_render_template('@theme/pages/single-post/single-post.twig', $view_data);
