<?php
/**
 * Page template for showing front page of the site.
 */

// Create view model factory
$view_model_factory = new \Ew\FrontPageViewModelFactory();

// Create view model
$view_model = [
	'jsRoute'   => 'frontPage',
	'bodyClass' => 'front-page',
	'vm'        => $view_model_factory->build()
];

// Render view
$ew_twig->render( 'pages/front-page/front-page.twig', $view_model );
