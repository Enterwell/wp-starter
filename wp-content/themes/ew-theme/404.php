<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package WordPress
 * @subpackage Starter_Theme
 */

// Render view
use EwStarter\View_Models\Base_View_Model;

$view_data = [
	'jsFile'    => '',
	'bodyClass' => '404-page',
	'vm'        => new Base_View_Model()
];
ew_render_template(
	'@theme/pages/404-page/404-page.twig',
	$view_data
);
