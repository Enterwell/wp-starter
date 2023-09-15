<?php

namespace EwStarter;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly;

require_once( THEME_DIR . '/classes/twig-extensions/class-ew-twig-extension-svg.php' );
require_once( THEME_DIR . '/classes/twig-extensions/class-ew-twig-extension-image.php' );
require_once( THEME_DIR . '/classes/twig-extensions/class-ew-twig-extension-utils.php' );
require_once( THEME_DIR . '/classes/twig-extensions/class-ew-twig-extension-localization.php' );
require_once( THEME_DIR . '/classes/twig-extensions/class-ew-twig-extension-block-attributes.php' );

/**
 * Class Ew_Twig
 * @package EwStarter
 */
class Ew_Twig {

	/**
	 * Templates dir.
	 */
	const TEMPLATES_DIR = '/views';

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
     * Script and link entry renderer
     * @var EntryFilesTwigExtension
     */
	public EntryFilesTwigExtension $entry_renderer;

	/**
	 * Ew_Twig constructor.
	 */
	public function __construct() {

		// Init loader
		$this->loader = new FilesystemLoader( [
			THEME_DIR . static::TEMPLATES_DIR,
			THEME_DIR . '/assets'
		] );

		// Init twig.
		$this->twig = new Environment( $this->loader, [
			'cache' => false,
			'debug' => true
		]);

		// Init script renderer
        $this->entry_renderer = new EntryFilesTwigExtension(THEME_DIR . '/assets/dist/entrypoints.json');

		// Add twig extensions
		add_action( 'init', [ $this, 'add_twig_extensions' ] );
	}

	/**
	 * Render template.
	 *
	 * @param string $path
	 * @param array $context
	 * @throws \Twig\Error\LoaderError
	 * @throws \Twig\Error\RuntimeError
	 * @throws \Twig\Error\SyntaxError
	 */
	public function render( $path, $context ): void
	{
		// Display template
		$this->twig->display( $path, $context );
	}

	/**
	 * Add twig extensions.
	 */
	function add_twig_extensions(): void
	{
		// Add dump extension
		$this->twig->addExtension( new DebugExtension() );
		// Add svg extension
		$this->twig->addExtension( new Ew_Twig_Extension_Svg() );
		// Add image extension
		$this->twig->addExtension( new Ew_Twig_Extension_Image() );
		// Add utils extension
		$this->twig->addExtension( new Ew_Twig_Extension_Utils() );
		// Add localization extension
		$this->twig->addExtension( new Ew_Twig_Extension_Localization() );
		// Add gutenberg component attributes extension
        $this->twig->addExtension( new Ew_Twig_Extension_Block_Attributes() );
		// Add encore entries extension
		$this->twig->addExtension( $this->entry_renderer );
	}
}
