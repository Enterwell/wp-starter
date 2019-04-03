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

global $wp_query;

// Create view model factory
$view_model_factory = new \Ew\ArchivePageViewModelFactory();

// Create view model
$view_model = [
	'jsRoute'   => 'archivePage',
	'bodyClass' => 'archive-page',
	'vm'        => $view_model_factory->build( $wp_query )
];

// Render view
$ew_twig->render( 'pages/archive-page/archive-page.twig', $view_model );
