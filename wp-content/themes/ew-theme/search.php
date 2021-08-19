<?php
/**
 * The template for displaying search results pages.
 *
 */

global $wp_query;

// Create view model factory
$view_model_factory = new \EwStarter\SearchPageViewModelFactory();

// Create view model
$view_model = [
	'jsFile'   => '',
	'bodyClass' => 'search-page',
	'vm'        => $view_model_factory->build( $wp_query )
];

// Render view
$ew_twig->render( 'pages/search-page/search-page.twig', $view_model );
