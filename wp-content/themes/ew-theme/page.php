<?php
/**
 * The template for displaying all pages
 */

global $ew_twig;

// Create view model
$view_model = [
	'jsFile'    => '',
	'bodyClass' => 'single-page',
	'vm'        => new \EwStarter\SinglePageViewModel()
];

// Render view
$ew_twig->render( 'pages/single-page/single-page.twig', $view_model );
