<?php

namespace EwStarter;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_SimpleFunction;
use Twig_Extension_Debug;

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
	 * @var \Twig\Environment
	 */
	protected $twig;

	/**
	 * Loader.
	 * @var Twig_Loader_Filesystem
	 */
	protected $loader;

    /**
     * Script and link entry renderer
     * @var EntryFilesTwigExtension
     */
	public $entry_renderer;

	/**
	 * Ew_Twig constructor.
	 */
	public function __construct() {

		// Init loader
		$this->loader = new Twig_Loader_Filesystem( [
			THEME_DIR . static::TEMPLATES_DIR,
			THEME_DIR . '/assets'
		] );

		// Init twig.
		$this->twig = new Twig_Environment( $this->loader, array(
			'cache' => false,
			'debug' => true
		) );

		// Init script renderer
        $this->entry_renderer = new EntryFilesTwigExtension(THEME_DIR . '/assets/dist/entrypoints.json');

		// Add twig functions.
		add_action( 'init', [ $this, 'add_twig_functions' ] );

		// Add twig extensions
		add_action( 'init', [ $this, 'add_twig_extensions' ] );
	}

    /**
     * Render template.
     *
     * @param string $path
     * @param array $context
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
	public function render( $path, $context ) {

		// Display template
		$this->twig->display( $path, $context );
	}

	/**
	 * Add twig functions.
	 */
	public function add_twig_functions() {

		// Add function
		$this->twig->addFunction( new Twig_SimpleFunction( 'function', [ $this, 'exec_function' ] ) );
	}

	/**
	 * Add twig extensions.
	 */
	function add_twig_extensions() {
		// Add dump extension
		$this->twig->addExtension( new Twig_Extension_Debug() );
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

	/**
	 * Execute function.
	 *
	 * @param string $function_name
	 *
	 * @return mixed
	 */
	public function exec_function( $function_name ) {

		// Get arguments
		$args = func_get_args();

		// Shift array
		array_shift( $args );

		// Is string
		if ( is_string( $function_name ) ) {

			// Trim function name
			$function_name = trim( $function_name );

		}

		// Returns
		return call_user_func_array( $function_name, ( $args ) );
	}
}
