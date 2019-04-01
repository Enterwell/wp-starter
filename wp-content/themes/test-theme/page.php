<?php
/**
 * The template for displaying all single posts and attachments
 */

	// Get the post
	$post = get_post();

	// Create view model
	$view_model = [
		'bodyClass' => 'single-page',
		'post' => $post,
		'content' => $post->post_content,
		'vm' => new \Ew\BaseViewModel()
	];

	// Render twig
	$ew_twig->render('pages/single-page/single-page.twig', $view_model);
