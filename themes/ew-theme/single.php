<?php
/**
 * The template for displaying all single posts and attachments
 */
// Create view model
$view_model = [
	'jsFile' => '',
	'bodyClass' => 'single-page',
	'vm' => new \EwStarter\SinglePostViewModel(get_post())
];

// Render view
$ew_twig->render('pages/single-post/single-post.twig', $view_model);
