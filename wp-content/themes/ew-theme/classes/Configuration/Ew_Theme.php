<?php

namespace EwStarter\Configuration;

use EwStarter\Configuration\Theme\Ew_Theme_Admin_Customizer;
use EwStarter\Configuration\Theme\Ew_Theme_Security;
use EwStarter\Configuration\Theme\Ew_Theme_Assets_Loader;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly;

/**
 * Class Ew_Theme
 * @package EwStarter
 */
class Ew_Theme {

	/** @var Ew_Blocks */
	protected Ew_Blocks $ew_blocks;

	/** @var Ew_Theme_Assets_Loader */
	protected Ew_Theme_Assets_Loader $ew_assets_loader;

	/** @var Ew_Theme_Security */
	protected Ew_Theme_Security $ew_security;

	/** @var Ew_Theme_Admin_Customizer */
	protected Ew_Theme_Admin_Customizer $ew_admin_customizer;

	public function __construct() {
		// TODO: Add from DI
		$this->ew_blocks           = new Ew_Blocks();
		$this->ew_assets_loader    = Ew_Theme_Assets_Loader::get_instance();
		$this->ew_security         = new Ew_Theme_Security();
		$this->ew_admin_customizer = new Ew_Theme_Admin_Customizer();
	}

	/**
	 * Load this class.
	 * @throws Exception
	 */
	public function load() {

		// Add action ew theme setup
		add_action( 'after_setup_theme', [ $this, 'theme_setup' ] );

		// Load admin customizer
		$this->ew_admin_customizer->load();

		// Load security settings
		$this->ew_security->load();

		// Load blocks
		$this->ew_blocks->load();

		// Load assets
		$this->ew_assets_loader->load();

	}

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	public function theme_setup() {

		// Add support for title tag
		add_theme_support( 'title-tag' );

		// Add support for post thumbnails
		add_theme_support( 'post-thumbnails' );

		// Register main nav menu
		register_nav_menu( EW_MAIN_NAV_MENU, __( 'Main navigation menu', THEME_TEXTDOMAIN ) );

		// Register common scripts and styles
		$this->register_common_scripts();
		$this->register_common_styles();
	}

	/**
	 * Registers all theme scripts.
	 *
	 * @throws Exception
	 */
	protected function register_common_scripts() {
		$this->ew_assets_loader->register_script( 'jquery' );
		$this->ew_assets_loader->register_script( 'gsap', 'vendor/gsap/gsap.min.js' );
	}

	/**
	 * Registers all theme styles.
	 */
	protected function register_common_styles() {
		// TODO: Register all theme styles
	}
}
