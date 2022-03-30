<?php
/**
 * The template for displaying archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each one. For example, tag.php (Tag archives),
 * category.php (Category archives), author.php (Author archives), etc.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Starter_Theme
 */


use EwStarter\View_Models\Archive_Page\Archive_Page_View_Model_Factory;


// Create view model factory
$view_model_factory = new Archive_Page_View_Model_Factory();
global $wp_query;

$view_data = [
	'jsFile'    => '',
	'bodyClass' => 'archive-page',
	'vm'        => $view_model_factory->build( $wp_query )
];

// Render page template
ew_render_template(
	'@theme/pages/archive-page/archive-page.twig',
	$view_data
);
