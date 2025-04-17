<?php
/**
 * The template for displaying archive pages
 */

global $ew_twig;
global $wp_query;

// Create view model factory
$view_model_factory = new \EwStarter\ArchivePageViewModelFactory();

// Create view model
$view_model = [
	'jsFile'    => '',
	'bodyClass' => 'archive-page',
	'vm'        => $view_model_factory->build( $wp_query )
];

// Render view
$ew_twig->render( 'pages/archive-page/archive-page.twig', $view_model );
