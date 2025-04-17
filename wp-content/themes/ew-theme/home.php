<?php
/**
 * Page template for showing home page of the site.
 */

global $ew_twig;

// Create view model
$view_model = [
	'jsFile'    => '',
	'bodyClass' => 'home-page',
	'vm'        => new \EwStarter\BaseViewModel()
];

// Render view
$ew_twig->render( 'pages/home-page/home-page.twig', $view_model );
