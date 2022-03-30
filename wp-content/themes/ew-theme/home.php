<?php
/**
 * Page template for showing home page of the site.
 */


// Create view model
use EwStarter\View_Models\Base_View_Model;

$view_data = [
	'jsFile' => '',
	'bodyClass' => 'home-page',
	'vm' => new Base_View_Model()
];

// Render view
ew_render_template('@theme/pages/home-page/home-page.twig', $view_data);
