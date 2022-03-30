<?php

namespace EwStarter\Configuration\Theme;

use Exception;

class Ew_Theme_Assets_Loader {
	/** @var Ew_Theme_Assets_Loader */
	private static $instance;

	/** @var array */
	protected $scripts = [];

	/** @var array */
	protected $styles = [];

	protected function __construct() {
	}

	public static function get_instance() {
		if ( empty( static::$instance ) ) {
			static::$instance = new Ew_Theme_Assets_Loader();
		}

		return static::$instance;
	}

	public function load() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts_and_styles' ] );
	}

	public function enqueue_scripts_and_styles() {
		foreach ( $this->scripts as $script ) {
			wp_enqueue_script( $script );
		}

		foreach ( $this->styles as $style ) {
			wp_enqueue_style( $style );
		}
	}

	/**
	 * @param string $handle
	 * @param string $file_path
	 * @param array $deps
	 * @param bool $in_footer
	 *
	 * @throws Exception
	 */
	public function register_script( string $handle, string $file_path = '', array $deps = [], $in_footer = true ) {
		$this->register_script_or_style( $handle, $file_path, true, $deps, $in_footer );
	}

	/**
	 * @param string $handle
	 * @param string $file_path
	 * @param array $deps
	 *
	 * @throws Exception
	 */
	public function register_style( string $handle, string $file_path = '', array $deps = [] ) {
		$this->register_script_or_style( $handle, $file_path, false, $deps );
	}

	/**
	 * @param string $handle
	 * @param string $file_path
	 * @param bool $is_script
	 * @param array $deps
	 * @param bool $in_footer
	 *
	 * @throws Exception
	 */
	protected function register_script_or_style( string $handle, string $file_path, bool $is_script = true, array $deps = [], bool $in_footer = true ) {
		$asset_file_path = THEME_DIR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . $file_path;
		$asset_file_url  = THEME_URL . '/assets/' . $file_path;

		// Check if file exists
		if ( ! empty( $file_path ) && ! file_exists( $asset_file_path ) ) {
			throw new Exception( "file path $asset_file_path for $handle does not exist." );
		}

		// Get file version
		$file_version = ! empty( $file_path ) ? filemtime( $asset_file_path ) : '';

		if ( $is_script ) {
			wp_register_script( $handle, $asset_file_url, $deps, $file_version, $in_footer );
			$this->scripts[] = $handle;
		} else {
			wp_register_style( $handle, $asset_file_url, $deps, $file_version );
			$this->styles[] = $handle;
		}
	}
}
