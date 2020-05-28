<?php

class ITSEC_Core_Active {

	public function run() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 0 );
		add_action( 'login_enqueue_scripts', array( $this, 'register_scripts' ), 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 0 );
	}

	public function register_scripts() {
		$dir          = ITSEC_Core::get_plugin_dir() . 'dist/';
		$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

		if ( $script_debug && file_exists( $dir . 'manifest-dev.php' ) ) {
			$manifest = require $dir . 'manifest-dev.php';
		} else {
			$manifest = require $dir . 'manifest.php';
		}

		foreach ( $manifest as $name => $config ) {
			foreach ( $config['files'] as $file ) {
				$handle = $this->name_to_handle( $name );

				if ( $script_debug && file_exists( $dir . $file ) ) {
					$path     = 'dist/' . $file;
					$is_debug = true;
				} else {
					$path     = 'dist/' . str_replace( '.', '.min.', $file );
					$is_debug = false;
				}

				$is_css = ITSEC_Lib::str_ends_with( $file, '.css' );
				$is_js  = ! $is_css;

				if ( $is_debug ) {
					$version = filemtime( $dir . $file );
				} elseif ( $is_js && isset( $config['contentHash']['javascript'] ) ) {
					$version = $config['contentHash']['javascript'];
				} elseif ( $is_css && isset( $config['contentHash']['css/mini-extract'] ) ) {
					$version = $config['contentHash']['css/mini-extract'];
				} else {
					$version = $config['hash'];
				}

				$deps = $is_js ? $config['dependencies'] : array();

				if ( $is_css && in_array( 'wp-components', $config['dependencies'], true ) ) {
					$deps[] = 'wp-components';
				}

				foreach ( $deps as $i => $dep ) {
					if ( ! ITSEC_Lib::str_starts_with( $dep, '@ithemes/security.' ) ) {
						continue;
					}

					$parts = explode( '.', $dep );

					$deps[ $i ] = $this->name_to_handle( "{$parts[1]}/{$parts[2]}" );
				}

				if ( ! $is_debug ) {
					foreach ( array_reverse( $config['vendors'] ) as $vendor ) {
						if ( ! isset( $manifest[ $vendor ] ) ) {
							continue;
						}

						if ( $is_js && $this->has_js( $manifest[ $vendor ]['files'] ) ) {
							$deps[] = $this->name_to_handle( $vendor );
						} elseif ( $is_css && $this->has_css( $manifest[ $vendor ]['files'] ) ) {
							$deps[] = $this->name_to_handle( $vendor );
						}
					}
				}

				if ( $is_css ) {
					wp_register_style(
						$handle,
						plugins_url( $path, ITSEC_Core::get_plugin_file() ),
						$deps,
						$version
					);
				} else {
					wp_register_script(
						$handle,
						plugins_url( $path, ITSEC_Core::get_plugin_file() ),
						$deps,
						$version
					);
				}

				if ( function_exists( 'wp_set_script_translations' ) && ! ITSEC_Core::is_pro() && in_array( 'wp-i18n', $deps, true ) ) {
					wp_set_script_translations( $handle, 'better-wp-security' );
				}

				if ( $is_js && ! empty( $config['runtime'] ) ) {
					$public_path = esc_js( trailingslashit( plugins_url( 'dist', ITSEC_Core::get_plugin_file() ) ) );
					wp_add_inline_script( $handle, "window.itsecWebpackPublicPath = window.itsecWebpackPublicPath || '{$public_path}';", 'before' );
				}
			}
		}
	}

	private function has_js( $files ) {
		foreach ( $files as $file ) {
			if ( ITSEC_Lib::str_ends_with( $file, '.js' ) ) {
				return true;
			}
		}

		return false;
	}

	private function has_css( $files ) {
		foreach ( $files as $file ) {
			if ( ITSEC_Lib::str_ends_with( $file, '.css' ) ) {
				return true;
			}
		}

		return false;
	}

	private function name_to_handle( $name ) {
		$name = str_replace( '/dist/', '/entry/', $name );

		return 'itsec-' . str_replace( '/', '-', $name );
	}
}
