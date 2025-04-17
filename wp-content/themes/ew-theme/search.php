<?php
/**
 * The template for displaying search results pages.
 *
 */

global $ew_twig;

// Create view model
$view_model = [
	'jsFile'    => '',
	'bodyClass' => 'search-page',
	'vm'        => new \EwStarter\BaseViewModel()
];

// Render view
$ew_twig->render( 'pages/search-page/search-page.twig', $view_model );
