<?php
namespace EwStarter;

use Twig_Extension;
use Twig_SimpleFunction;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly;

/**
 * Class Ew_Twig_Extension_Menu
 * @package EwStarter
 */
class Ew_Twig_Extension_Menu extends Twig_Extension {

	/**
	 * Get functions.
	 *
	 * @return array|Twig_SimpleFunction[]
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction('ew_get_nav_menu_items', [$this, 'get_nav_menu_items']),
		];
	}

	/**
	 * Gets list of navigation menu items.
	 *
	 * @param   string $menu_name
	 *
	 * @return  array
	 */
	public function get_nav_menu_items( $menu_name ) {

		// Initialize empty list of menu items
		$nav_menu_items = [];

		// If there are any nav menus
		if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_name ] ) ) {

			// Get nav menu object
			$menu = wp_get_nav_menu_object( $locations[ $menu_name ] );

			// Get nav menu items
			$items = wp_get_nav_menu_items( $menu->term_id );

			// Get current page permalink
			$current_page_permalink = get_permalink();

			// Create nav menu items
			foreach ( $items as $item ) {

				$nav_menu_items[] = [
					'label'     => $item->title,
					'link'      => $item->url,
					'is_active' => $item->url === $current_page_permalink
				];

			}
		}

		// Return items
		return $nav_menu_items;
	}

}