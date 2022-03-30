<?php
/**
 * The template for displaying search results pages.
 *
 */


use EwStarter\View_Models\Search_Page\Search_Page_View_Model_Factory;

global $wp_query;

// Create view model factory
$factory = new Search_Page_View_Model_Factory();

// Create view model
$view_data = [
	'jsFile'    => '',
	'bodyClass' => 'search-page',
	'vm'        => $factory->build( $wp_query )
];

// Render view
ew_render_template( '@theme/pages/search-page/search-page.twig', $view_data );
