<?php
/**
 * Page template for showing front page of the site.
 */

// Creates view model
$view_model = [
	'jsRoute'   => 'frontPage',
	'bodyClass' => 'front-page',
	'vm'        => new \Ew\BaseViewModel()
];

// Render views
$ew_twig->render( 'pages/front-page/front-page.twig', $view_model );
