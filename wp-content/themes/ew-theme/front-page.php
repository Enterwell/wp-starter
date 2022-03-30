<?php
/**
 * Page template for showing static front page of the site.
 * Static front page needs to be set in Settings -> Reading -> Front page
 */

// Create view model
use EwStarter\View_Models\Front_Page\Front_Page_View_Model;

$view_data = [
	'jsFile'    => 'pages/front-page/front-page',
	'bodyClass' => 'front-page',
	'vm'        => new Front_Page_View_Model()
];

// Render view
ew_render_template( '@theme/pages/front-page/front-page.twig', $view_data );
