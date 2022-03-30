<?php

namespace EwStarter\Configuration;

class Ew_Environment {
	/**
	 * Returns if env is valid.
	 *
	 * @param string $env
	 *
	 * @return bool
	 */
	public function is_env( string $env ): bool {
		if ( ! defined( 'EW_WP_ENVIRONMENT' ) ) {
			return false;
		}

		return EW_WP_ENVIRONMENT === $env;
	}

	/**
	 * Is production environment.
	 * @return bool
	 */
	public function is_production(): bool {
		return $this->is_env( 'production' );
	}

	/**
	 * Returns theme views dir.
	 * @return string
	 */
	public function get_views_dir(): string {
		return THEME_DIR . DIRECTORY_SEPARATOR . 'views';
	}

	/**
	 * Returns theme blocks dir.
	 * @return string
	 */
	public function get_blocks_dir_path( $filename = '' ): string {
		return THEME_DIR . "/assets/gutenberg/blocks/$filename";
	}

	public function get_assets_build_dir_path( $filename = '' ): string {
		return THEME_DIR . "/assets/dist/$filename";
	}

}
