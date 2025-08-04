<?php

namespace EwStarter;

/**
 * Class Ew_Blocks
 * @package EwStarter
 */
class Ew_Blocks {

    // Constants
    const BLOCKS_FOLDER = THEME_DIR . '/assets/gutenberg/blocks';
    const COMPONENTS_FOLDER = THEME_DIR . '/assets/gutenberg/components';
    const BLOCKS_MANIFEST_FILE = THEME_DIR . '/assets/gutenberg/manifest.json';

	/**
	 * Initializes blocks.
	 */
	public static function load() {
		// Add block editor assets after WP scripts in admin footer
		add_action('admin_print_footer_scripts', [static::class, 'add_block_editor_assets'], 99);

		// Register dynamic blocks
		add_action( 'init', [ static::class, 'load_blocks' ] );

		// Register block category for project
		add_filter( 'block_categories_all', [ static::class, 'add_blocks_category' ] );
	}

    /**
     * Appends block category for our custom blocks
     * Gets its name from global manifest file for all blocks
     *
     * @param $categories
     * @return array
     */
	public static function add_blocks_category( $categories ) {
		$blocks_manifest      = static::load_json_from_file( static::BLOCKS_MANIFEST_FILE );

		if ( empty( $blocks_manifest ) || empty( $blocks_manifest['blocksCategory'] ) ) {
			return $categories;
		}

		$categories[] = [
			'title' => $blocks_manifest['blocksCategory']['title'],
			'slug'  => $blocks_manifest['blocksCategory']['slug'],
		];

		return $categories;
	}

    /**
     * Load JSON from file
     *
     * @param $file_path
     * @return mixed|null
     */
	private static function load_json_from_file( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return null;
		}

		// Get file contents
		$contents = file_get_contents( $file_path );

		if ( empty( $contents ) ) {
			return null;
		}

		// Decode in appropriate format
		$data = json_decode( $contents, true );

		return $data;
	}

    /**
     * Load all blocks in gutenberg block folders and register them
     *
     * @throws \Exception
     */
	static function load_blocks() {
		$blocks_root     = static::BLOCKS_FOLDER;
		$block_manifests = glob( "$blocks_root/**/manifest.json" );

		if ( ! $block_manifests ) {
			return;
		}

		// Load each block
		foreach ( $block_manifests as $block_manifest_file ) {
			$block_manifest   = static::load_json_from_file( $block_manifest_file );
			$block_attributes = static::get_block_attributes( $block_manifest );
			$block_component_attributes = static::get_block_component_attributes($block_manifest, $block_attributes['blockFullName']['default']);

			register_block_type(
				$block_attributes['blockFullName']['default'],
				[
					'attributes'      => array_merge($block_attributes, $block_component_attributes),
					'render_callback' => [ static::class, 'render_block' ]
				]
			);
		}
	}

    /**
     * Gets block attributes from blocks manifest file
     * Adds default attributes in case some of them are not provided
     *
     * @param $manifest
     * @return array
     * @throws \Exception
     */
	private static function get_block_attributes( $manifest ) {
	    // Default attributes
		$default_attributes = [
			'blockFullName' => [
				'type'    => 'string',
				'default' => static::get_block_name( $manifest )
			],
			'blockName'     => [
				'type'    => 'string',
				'default' => $manifest['blockName']
			]
		];

		// Attributes from blocks manifest file
		$block_attributes = ! empty( $manifest['attributes'] ) ? $manifest['attributes'] : [];

		return array_merge( $default_attributes, $block_attributes );
	}

    /**
     * Gets attributes from components used in block
     * Appends unique prefix to each component attribute based on block's manifest 'components' prop
     *
     * @param $block_manifest
     * @param $block_name
     * @return array
     * @throws \Exception
     */
	private static function get_block_component_attributes($block_manifest, $block_name) {
        // Return if no components prop in blocks manifest
	    if(empty($block_manifest['components']))
            return [];

	    $attributes = [];

	    // Foreach component in blocks manifest
        foreach ($block_manifest['components'] as $prefix => $component_name) {
            // Get component manifest file
            $components_root = static::COMPONENTS_FOLDER;
            $component_manifest_file = glob( "$components_root/$component_name/manifest.json" );

            // If component manifest doesn't not exist, throw ex and continue
            if(!$component_manifest_file) {
                throw new \Exception( "Component '$component_name' manifest inside '$block_name' manifest file does not exist (theme/assets/gutenberg/components/$component_name/manifest.json)" );
                continue;
            }

            // Get data from components manifest
            $component_manifest = static::load_json_from_file( $component_manifest_file[0] );

            // If no attributes for that component, just skip it
            if(empty($component_manifest['attributes']))
                continue;

            foreach ($component_manifest['attributes'] as $attribute_key => $attribute_object) {
                // Capitalise attribute key so it's in camelCase when appended to prefix
                $attribute_name = ucfirst($attribute_key);

                // Add components prefixed attribute to block attributes
                $attributes["$prefix$attribute_name"] = $attribute_object;
            }
        }

	    return $attributes;
    }

    /**
     * Gets block name based on blocks manifest and global manifest for all blocks
     * Appends projectNamespace from global manifest to block name
     *
     * @param $manifest
     * @return string
     * @throws \Exception
     */
	private static function get_block_name( $manifest ) {
		$blocks_manifest      = static::load_json_from_file( static::BLOCKS_MANIFEST_FILE );

		if ( empty( $blocks_manifest ) ) {
			throw new \Exception( "Blocks manifest file does not exist (theme/admin/gutenberg/manifest.json)" );
		}

		if ( empty( $blocks_manifest['projectNamespace'] ) ) {
			throw new \Exception( "Required field 'projectNamespace' is not defined in blocks manifest." );
		}

		if ( empty( $manifest['blockName'] ) ) {
			throw new \Exception( "Required field 'blockName' is missing from block manifest!" );
		}

		return implode( "/", [ $blocks_manifest['projectNamespace'], $manifest['blockName'] ] );
	}

    /**
     * Callback function for block rendering
     * Gets block twig template and returns its content
     *
     * @param $attributes
     * @param string $inner_content
     * @return false|string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
	public static function render_block( $attributes, $inner_content = '' ) {
		global $ew_twig;

		$block_name    = ! empty( $attributes['blockName'] ) ? $attributes['blockName'] : '';
		$twig_template = static::get_theme_template_for_block( $block_name );

		if (!is_admin()) {
			// Add block public script
			self::add_block_assets($block_name);
		}

		ob_start();
		$ew_twig->render( $twig_template, [
			'attributes'   => apply_filters( 'ew/block-render-attributes', $attributes, $block_name ),
			'innerContent' => $inner_content
		] );

		unset( $attributes );
		unset( $inner_content );

		return ob_get_clean();
	}

    /**
     * Returns blocks twig template route from root of blocks folder
     *
     * @param $block_name
     * @return string
     * @throws \Exception
     */
	private static function get_theme_template_for_block( $block_name ) {
		$blocks_root_dir = THEME_DIR . '/assets';

		$block_twig_template = "gutenberg/blocks/$block_name/public/$block_name.twig";
		$block_twig_file     = "$blocks_root_dir/" . $block_twig_template;
		if ( ! file_exists( $block_twig_file ) ) {
			throw new \Exception( "Block view (twig) file not found for block '$block_name' ($block_twig_file)" );
		}

		return $block_twig_template;
	}

    /**
     * Add gutenberg block public script to footer
     * @param $block_name
     */
    public static function add_block_assets($block_name) {
        global $ew_twig;

        add_action('wp_print_footer_scripts', function() use ($ew_twig, $block_name) {
            $blockEntryPath = "blocks/$block_name/public/$block_name";

            try {
                echo $ew_twig->entry_renderer->renderWebpackScriptTags($blockEntryPath, ['defer' => 'defer']);
            } catch (EntrypointNotFoundException $e) {
                // Public JS file for block doesn't exist or file isn't named the same as block
            }
        }, 20);
    }

	/**
	 * Add gutenberg admin script to head if admin interface
	 */
	public static function add_block_editor_assets() {
        global $ew_twig;

		$screen = get_current_screen();
		// Render admin scripts only when we have block editor loaded on our admin page
		if (empty( $screen ) || !$screen->is_block_editor()) return;

		// Gutenberg format types
		echo $ew_twig->entry_renderer->renderWebpackScriptTags('gutenberg_admin_format_types');
		echo $ew_twig->entry_renderer->renderWebpackLinkTags('gutenberg_admin_format_types');

		// Gutenberg components
		echo $ew_twig->entry_renderer->renderWebpackScriptTags('gutenberg_admin_components');
		echo $ew_twig->entry_renderer->renderWebpackLinkTags('gutenberg_admin_components');

		// Gutenberg blocks
		echo $ew_twig->entry_renderer->renderWebpackScriptTags('gutenberg_admin_blocks');
		echo $ew_twig->entry_renderer->renderWebpackLinkTags('gutenberg_admin_blocks');
	}
}
