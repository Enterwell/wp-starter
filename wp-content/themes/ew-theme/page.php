<?php
/**
 * The template for displaying all pages
 */


// Create view model
$view_model = [
	'bodyClass' => 'single-page',
	'vm' => new \EwStarter\SinglePostViewModel(get_post())
];

// Render view
$ew_twig->render('pages/single-page/single-page.twig', $view_model);
