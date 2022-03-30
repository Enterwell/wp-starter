<?php

namespace EwStarter\Configuration\Theme;

class Ew_Theme_Admin_Customizer {
	public function load() {
		$this->remove_admin_comments();
	}

	/**
	 * Remove comments from site admin.
	 */
	protected function remove_admin_comments() {
		// Removes from admin menu
		add_action( 'admin_menu', function () {
			remove_menu_page( 'edit-comments.php' );
		} );

		// Removes from post and pages
		add_action( 'init', function () {
			remove_post_type_support( 'post', 'comments' );
			remove_post_type_support( 'page', 'comments' );
		}, 100 );

		// Removes from admin bar
		add_action( 'wp_before_admin_bar_render', function () {
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu( 'comments' );
		} );
	}
}
