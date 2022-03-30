<?php
/**
 * The template for displaying all pages
 */


// Create view model
use EwStarter\View_Models\Single_Post\Single_Post_View_Model;

$view_data = [
	'jsFile' => '',
	'bodyClass' => 'single-page',
	'vm' => new Single_Post_View_Model(get_post())
];

// Render view
ew_render_template('@theme/pages/single-page/single-page.twig', $view_data);
