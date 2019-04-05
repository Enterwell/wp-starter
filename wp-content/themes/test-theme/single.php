<?php
/**
 * The template for displaying all single posts and attachments
 */

// Get the post
$wp_post            = get_post();

// Create view model factory
$view_model_factory = new \Ew\SinglePostViewModelFactory();

// Create view model
$view_model = [
	'jsRoute'   => 'singlePage',
	'bodyClass' => 'single-page',
	'vm'        => $view_model_factory->build( $wp_post )
];

// Render view
$ew_twig->render( 'pages/single-post/single-post.twig', $view_model );