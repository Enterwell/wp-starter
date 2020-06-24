<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package WordPress
 * @subpackage Starter_Theme
 */

// Create the view model
$view_model = [
	'jsRoute'   => '404Page',
	'bodyClass' => '404-page',
	'vm'        => new \EwStarter\BaseViewModel()
];

// Render view
$ew_twig->render( 'pages/404-page/404-page.twig', $view_model );