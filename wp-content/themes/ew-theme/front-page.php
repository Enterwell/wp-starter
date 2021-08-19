<?php
/**
 * Page template for showing static front page of the site.
 * Static front page needs to be set in Settings -> Reading -> Front page
 */

// Create view model
$view_model = [
	'jsFile' => 'pages/front-page/front-page',
	'bodyClass' => 'front-page',
	'vm' => new \EwStarter\FrontPageViewModel()
];

// Render view
$ew_twig->render('pages/front-page/front-page.twig', $view_model);
