<?php
/**
 * Page template for showing home page of the site.
 */

// Create view model factory
$view_model_factory = new \Ew\HomePageViewModelFactory();

// Create view model
$view_model = [
	'jsRoute'   => 'homePage',
	'bodyClass' => 'home-page',
	'vm'        => $view_model_factory->build()
];

// Render view
$ew_twig->render( 'pages/home-page/home-page.twig', $view_model );
