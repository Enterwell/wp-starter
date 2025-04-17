<?php
/**
 * The template for displaying all single posts and attachments
 */

global $ew_twig;

// Create view model
$view_model = [
	'jsFile'    => '',
	'bodyClass' => 'single-post',
	'vm'        => new \EwStarter\SinglePostViewModel()
];

// Render view
$ew_twig->render( 'pages/single-post/single-post.twig', $view_model );
