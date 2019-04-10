<?php
/**
 * The template for displaying all pages
 */

// Get the post
$post = get_post();

// Create view model factory
$view_model_factory = new \EwStarter\SinglePageViewModelFactory();

// Create view model
$view_model = [
	'bodyClass' => 'single-page',
	'vm'        => $view_model_factory->build( $post )
];

// Render view
$ew_twig->render( 'pages/single-page/single-page.twig', $view_model );
