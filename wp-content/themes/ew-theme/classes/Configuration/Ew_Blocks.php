<?php

namespace EwStarter\Configuration;

use EwStarter\Encore\Twig\Entry_Files_Twig_Extension;

/**
 * Class Ew_Blocks
 */
class Ew_Blocks {
	/**
	 * Initializes blocks.
	 */
	public function load() {
		// Add block editor assets after WP scripts in admin footer
		add_action( 'admin_print_footer_scripts', [ $this, 'add_block_editor_assets' ], 20 );

		// Register dynamic blocks
		add_action( 'init', [ $this, 'load_blocks' ] );

		// Register block category for project
		add_filter( 'block_categories', [ $this, 'add_blocks_category' ] );
	}

	/**
	 * @param $categories
	 *
	 * @return array
	 */
	function add_blocks_category( $categories ) {
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
	private function load_json_from_file( $file_path ) {
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
	function load_blocks() {
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
	private function get_block_attributes( $manifest ) {
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
	private function get_block_name( $manifest ) {
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

	public function render_block( $attributes, $inner_content = '' ) {
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

	private function get_theme_template_for_block( $block_name ) {
		$blocks_root_dir = THEME_DIR . '/assets/gutenberg/blocks';

		$block_twig_template = "$block_name/public/$block_name.twig";
		$block_twig_file     = "$blocks_root_dir/" . $block_twig_template;
		if ( ! file_exists( $block_twig_file ) ) {
			throw new \Exception( "Block view file not found for block '$block_name' ($block_twig_file)" );
		}

		return $block_twig_template;
	}


	/**
	 * Add gutenberg admin script to head if admin interface
	 */
	function add_block_editor_assets() {
		$encore_renderer = new Entry_Files_Twig_Extension( THEME_DIR . '/assets/dist/entrypoints.json' );

		echo $encore_renderer->render_webpack_script_tags( 'gutenberg_admin' );
		echo $encore_renderer->render_webpack_link_tags( 'gutenberg_admin' );
	}
}
