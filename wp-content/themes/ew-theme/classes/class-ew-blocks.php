<?php

namespace EwStarter;


/**
 * Class Ew_Blocks
 * @package Majstor
 */
class Ew_Blocks {
	const BLOCKS_PROD_JS_FILENAME = 'blocks.min.js';
	const BLOCKS_PROD_CSS_FILENAME = 'blocks.min.css';
	const BLOCKS_DEV_JS_FILENAME = 'blocks.js';

	const BLOCKS_SCRIPT_HANDLE = 'ew_blocks_script';
	const BLOCKS_STYLE_HANDLE = 'ew_blocks_style';

	/**
	 * Initializes blocks.
	 */
	public static function load() {
		// Enqueue block editor assets (styles and js)
		add_action( 'enqueue_block_editor_assets', [ static::class, 'enqueue_block_editor_assets' ] );

		// Register dynamic blocks
		add_action( 'init', [ static::class, 'load_blocks' ] );

		// Register block category for project
		add_filter( 'block_categories', [ static::class, 'add_blocks_category' ] );
	}

	/**
	 * @param $categories
	 *
	 * @return array
	 */
	static function add_blocks_category( $categories ) {
		$blocks_manifest_file = THEME_DIR . '/assets/gutenberg/manifest.json';
		$blocks_manifest      = static::load_json_from_file( $blocks_manifest_file );

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
	 * @param $file_path
	 *
	 * @return array|mixed|null|object
	 */
	private static function load_json_from_file( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return null;
		}
		$contents = file_get_contents( $file_path );
		if ( empty( $contents ) ) {
			return null;
		}

		$data = json_decode( $contents, true );

		return $data;
	}

	/**
	 * @throws \Exception
	 */
	static function load_blocks() {
		$blocks_root     = THEME_DIR . '/assets/gutenberg/blocks';
		$block_manifests = glob( "$blocks_root/**/manifest.json" );
		if ( ! $block_manifests ) {
			return;
		}

		// Load each block
		foreach ( $block_manifests as $block_manifest_file ) {
			$block_manifest   = static::load_json_from_file( $block_manifest_file );
			$block_attributes = static::get_block_attributes( $block_manifest );

			register_block_type(
				$block_attributes['blockFullName']['default'],
				[
					'attributes'      => $block_attributes,
					'render_callback' => [ static::class, 'render_block' ]
				]
			);
		}
	}

	/**
	 * @param $manifest
	 *
	 * @return array
	 * @throws \Exception
	 */
	private static function get_block_attributes( $manifest ) {
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

		$block_attributes = ! empty( $manifest['attributes'] ) ? $manifest['attributes'] : [];

		return array_merge( $default_attributes, $block_attributes );
	}

	/**
	 * @param $manifest
	 *
	 * @return string
	 * @throws \Exception
	 */
	private static function get_block_name( $manifest ) {
		$blocks_manifest_file = THEME_DIR . '/assets/gutenberg/manifest.json';
		$blocks_manifest      = static::load_json_from_file( $blocks_manifest_file );

		if ( empty( $blocks_manifest ) ) {
			throw new \Exception( "Blocks manifest file does not exist (theme/admin/gutenberg/manifest.json)" );
		}

		if ( empty( $blocks_manifest['projectNamespace'] ) ) {
			throw new \Exception( "Required filed 'projectNamespace' is not defined in blocks manifest." );
		}

		if ( empty( $manifest['blockName'] ) ) {
			throw new \Exception( "Required field 'blockName' is missing from block manifest!" );
		}

		return implode( "/", [ $blocks_manifest['projectNamespace'], $manifest['blockName'] ] );
	}

	public static function render_block( $attributes, $inner_content = '' ) {
		global $ew_twig;

		$block_name    = ! empty( $attributes['blockName'] ) ? $attributes['blockName'] : '';
		$twig_template = static::get_theme_template_for_block( $block_name );

		ob_start();
		$ew_twig->render( $twig_template, [
			'attributes'   => apply_filters( 'ew/block-render-attributes', $attributes, $block_name ),
			'innerContent' => $inner_content
		] );

		unset( $attributes );
		unset( $inner_content );

		return ob_get_clean();
	}

	private static function get_theme_template_for_block( $block_name ) {
		$blocks_root_dir = THEME_DIR . '/assets/gutenberg/blocks';

		$block_twig_template = "$block_name/public/$block_name.twig";
		$block_twig_file     = "$blocks_root_dir/" . $block_twig_template;
		if ( ! file_exists( $block_twig_file ) ) {
			throw new \Exception( "Block view file not found for block '$block_name' ($block_twig_file)" );
		}

		return $block_twig_template;
	}

	/**
	 * If development env this function loads assets from asset-manifest.json and injects
	 * them as scripts (or styles).
	 */
	static function enqueue_block_editor_assets() {
		$build_dir     = THEME_DIR . '/assets/dist';
		$build_url     = THEME_URL . '/assets/dist';
		$manifest_file = $build_dir . '/asset-manifest.json';

		$is_dev = defined( 'EW_DEV' ) && EW_DEV;
		if ( $is_dev ) {
			// Load dev assets
			static::enqueue_dev_assets( $manifest_file, $build_dir, $build_url );
		} else {
			// Load built assets
			static::enqueue_prod_assets( $build_dir, $build_url );
		}
	}

	/**
	 * Enqueue dev assets for blocks.
	 *
	 * @param $manifest
	 */
	private static function enqueue_dev_assets( $manifest ) {
		$dev_script_url = static::get_dev_script_url_from_manifest( $manifest );

		if ( ! $dev_script_url ) {
			// TODO: Warning no dev script in manifest
			return;
		}

		wp_register_script(
			static::BLOCKS_SCRIPT_HANDLE,
			$dev_script_url,
			static::get_blocks_script_dependencies(),
			filemtime( $manifest ),
			true
		);

		wp_enqueue_script( static::BLOCKS_SCRIPT_HANDLE );
	}

	/**
	 * @param $manifest
	 *
	 * @return null
	 */
	private static function get_dev_script_url_from_manifest( $manifest ) {
		$manifest_data = static::load_json_from_file( $manifest );
		if ( empty( $manifest_data ) ) {
			return null;
		}

		return $manifest_data[ static::BLOCKS_DEV_JS_FILENAME ] ? $manifest_data[ static::BLOCKS_DEV_JS_FILENAME ] : null;
	}

	/**
	 * Gets all WordPress scripts that needs to be loaded
	 * before our blocks script.
	 *
	 * @return array
	 */
	private static function get_blocks_script_dependencies() {
		return [
			'wp-blocks',
			'wp-data',
			'wp-edit-post',
			'wp-element',
			'wp-i18n',
			'wp-plugins',
			'jquery',
			'wp-components',
			'wp-editor',
			'wp-date',
			'wp-viewport',
			'wp-blob',
			'wp-url',
		];
	}

	/**
	 * Load blocks script for PROD environment.
	 *
	 * @param $build_dir
	 * @param $build_url
	 */
	private static function enqueue_prod_assets( $build_dir, $build_url ) {
		$blocks_js_out_file  = $build_dir . '/' . static::BLOCKS_PROD_JS_FILENAME;
		$blocks_css_out_file = $build_dir . '/' . static::BLOCKS_PROD_CSS_FILENAME;
		$blocks_js_out_url   = $build_url . '/' . static::BLOCKS_PROD_JS_FILENAME;
		$blocks_css_out_url  = $build_url . '/' . static::BLOCKS_PROD_CSS_FILENAME;
		if ( file_exists( $blocks_js_out_file ) ) {

			// Register blocks script
			wp_register_script(
				static::BLOCKS_SCRIPT_HANDLE,
				$blocks_js_out_url,
				static::get_blocks_script_dependencies(),
				filemtime( $blocks_js_out_file ),
				true
			);

			// Enqueue blocks script
			wp_enqueue_script( static::BLOCKS_SCRIPT_HANDLE );
		} else {
			// TODO: Add notification - PROD is on and there's no blocks file
		}

		if ( file_exists( $blocks_css_out_file ) ) {

			// Register blocks script
			wp_register_style(
				static::BLOCKS_STYLE_HANDLE,
				$blocks_css_out_url,
				[],
				filemtime( $blocks_css_out_file )
			);

			// Enqueue blocks script
			wp_enqueue_style( static::BLOCKS_STYLE_HANDLE );
		} else {
			// TODO: Add notification - PROD is on and there's no blocks file
		}

	}
}
