<?php
/**
 * Page template for showing home page of the site.
 */


// Create view model
$view_model = [
	'jsRoute' => 'homePage',
	'bodyClass' => 'home-page',
	// TODO: If changed add new view model for home
	'vm' => new \EwStarter\BaseViewModel()
];

// Render view
$ew_twig->render('pages/home-page/home-page.twig', $view_model);
