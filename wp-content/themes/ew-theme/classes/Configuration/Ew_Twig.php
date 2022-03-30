<?php

namespace EwStarter\Configuration;

use EwStarter\Encore\Twig\Entry_Files_Twig_Extension;
use EwStarter\Twig_Extensions\Ew_Twig_Helper_Functions_Extension;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly;

/**
 * Class Ew_Twig
 * @package EwStarter
 */
class Ew_Twig {
	/** @var Ew_Twig */
	protected static Ew_Twig $instance;

	/**
	 * Twig.
	 * @var Environment
	 */
	protected Environment $twig;

	/**
	 * Loader.
	 * @var FilesystemLoader
	 */
	protected FilesystemLoader $loader;

	/**
	 * Gets twig instance.
	 * @return Ew_Twig
	 */
	public static function get_instance(): Ew_Twig {
		if ( empty( static::$instance ) ) {
			static::$instance = new Ew_Twig();
		}

		return static::$instance;
	}

	/**
	 * Ew_Twig constructor.
	 */
	protected function __construct() {
		// TODO: Replace with DI inject
		$env = new Ew_Environment();

		// Init loader
		$this->loader = new FilesystemLoader( [
			$env->get_views_dir()
		] );

		$this->loader->addPath( $env->get_views_dir(), 'theme' );
		$this->loader->addPath( $env->get_blocks_dir_path(), 'blocks' );

		// Init twig.
		$this->twig = new Environment( $this->loader, [
			// Use cache only for production
			'cache' => $env->is_production(),

			// Do not use debug for production
			'debug' => ! $env->is_production()
		] );

		// Add twig extensions
		$this->add_twig_extensions( $env );
	}

	/**
	 * Render template.
	 *
	 * @param string $path
	 * @param array $context
	 *
	 * @throws \Exception
	 */
	public function render( string $path, array $context ) {
		// Display template
		$this->twig->display( $path, $context );
	}

	/**
	 * Add twig extensions.
	 */
	function add_twig_extensions( Ew_Environment $env ): void {
		// Add dump extension
		$this->twig->addExtension( new DebugExtension() );
		// Add helper functions
		$this->twig->addExtension( new Ew_Twig_Helper_Functions_Extension() );
		// Add encore entries extension
		$this->twig->addExtension( new Entry_Files_Twig_Extension( $env->get_assets_build_dir_path( 'entrypoints.json' ) ) );
	}
}
