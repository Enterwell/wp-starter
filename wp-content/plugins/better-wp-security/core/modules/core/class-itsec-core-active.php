<?php

use iThemesSecurity\Encryption\User_Key_Rotator;
use iThemesSecurity\Lib\Result;
use iThemesSecurity\Lib\Tools\Config_Tool;
use iThemesSecurity\Lib\Tools\Tools_Registry;
use iThemesSecurity\Module_Config;

class ITSEC_Core_Active {

	/** @var string[] */
	private $handles = [];

	public function run() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 0 );
		add_action( 'login_enqueue_scripts', array( $this, 'register_scripts' ), 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 0 );
		add_action( 'wp_footer', array( $this, 'add_live_reload' ), 1000 );
		add_action( 'admin_footer', array( $this, 'add_live_reload' ), 1000 );
		add_action( 'itsec_register_tools', array( $this, 'register_tools' ) );
		add_action( 'itsec_encryption_rotate_user_keys', array( $this, 'rotate_encrypted_user_keys' ), 10, 2 );
		add_action( 'itsec_scheduled_enable-encryption', array( $this, 'enable_encryption' ) );
	}

	public function rest_api_init() {
		$factory = ITSEC_Modules::get_container()->get( \iThemesSecurity\Actor\Multi_Actor_Factory::class );
		( new ITSEC_REST_Actor_Types_Controller( $factory ) )->register_routes();
		( new ITSEC_REST_Actors_Controller( $factory ) )->register_routes();
	}

	public function register_scripts() {
		$dir          = ITSEC_Core::get_plugin_dir() . 'dist/';
		$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

		if ( $script_debug && file_exists( $dir . 'manifest-dev.php' ) ) {
			$manifest = require $dir . 'manifest-dev.php';
		} else {
			$manifest = require $dir . 'manifest.php';
		}

		$handles_with_package_dependencies = [];

		foreach ( $manifest as $name => $config ) {
			if ( ! $config['files'] ) {
				continue;
			}

			$has_css = false;

			foreach ( $config['files'] as $file ) {
				$handle          = $this->name_to_handle( $name );
				$this->handles[] = $handle;

				if ( ! ITSEC_Core::is_pro() ) {
					// WordPress.org installs always use non-minified file names.
					// This is to allow for WP-CLI to scan the files since, by default
					// minified JS files are excluded.
					$path     = 'dist/' . $file;
					$is_debug = false;
				} elseif ( $script_debug && file_exists( $dir . $file ) ) {
					$path     = 'dist/' . $file;
					$is_debug = true;
				} else {
					if ( strpos( $file, '.min.' ) === false ) {
						$file = str_replace( '.', '.min.', $file );
					}

					$path     = 'dist/' . $file;
					$is_debug = false;
				}

				$is_css = ITSEC_Lib::str_ends_with( $file, '.css' );
				$is_js  = ! $is_css;

				if ( $is_css ) {
					$has_css = true;
				}

				if ( $is_debug ) {
					$version = filemtime( $dir . $file );
				} elseif ( $is_js && isset( $config['contentHash']['javascript'] ) ) {
					$version = $config['contentHash']['javascript'];
				} elseif ( $is_css && isset( $config['contentHash']['css/mini-extract'] ) ) {
					$version = $config['contentHash']['css/mini-extract'];
				} else {
					$version = $config['hash'];
				}

				$deps = $is_js ? $config['dependencies'] : [];

				foreach ( $deps as $i => $dep ) {
					if ( ! ITSEC_Lib::str_starts_with( $dep, '@ithemes/security.' ) ) {
						continue;
					}


					$parts      = explode( '.', $dep );
					$dep_handle = $this->name_to_handle( "{$parts[1]}/{$parts[2]}" );

					$deps[ $i ] = $dep_handle;

					$handles_with_package_dependencies[ $handle ][] = $dep_handle;
				}

				if ( $is_js && 'runtime' !== $name ) {
					$deps[] = $this->name_to_handle( 'runtime' );
				}

				if ( $is_css && in_array( 'wp-components', $config['dependencies'], true ) ) {
					$deps[] = 'wp-components';
				}

				foreach ( array_reverse( $config['vendors'] ) as $vendor ) {
					if ( ! isset( $manifest[ $vendor ] ) || $name === $vendor ) {
						continue;
					}

					if ( $is_js && $this->has_js( $manifest[ $vendor ]['files'] ) ) {
						$deps[] = $this->name_to_handle( $vendor );
					} elseif ( $is_css && $this->has_css( $manifest[ $vendor ]['files'] ) ) {
						$deps[] = $this->name_to_handle( $vendor );
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

				if ( in_array( 'wp-i18n', $deps, true ) ) {
					wp_set_script_translations( $handle, 'better-wp-security', '' );
				}

				if ( $is_js && ! empty( $config['runtime'] ) ) {
					$public_path = esc_js( trailingslashit( plugins_url( 'dist', ITSEC_Core::get_plugin_file() ) ) );
					wp_add_inline_script( $handle, "window.itsecWebpackPublicPath = window.itsecWebpackPublicPath || '{$public_path}';", 'before' );
				}
			}

			if ( ! $has_css && in_array( 'wp-components', $config['dependencies'], true ) ) {
				wp_register_style(
					$this->name_to_handle( $name ),
					'',
					[ 'wp-components' ]
				);
			}
		}

		foreach ( $handles_with_package_dependencies as $handle => $dependencies ) {
			if ( ! $asset = wp_styles()->registered[ $handle ] ?? null ) {
				continue;
			}

			foreach ( $dependencies as $dependency ) {
				if ( ! wp_style_is( $dependency, 'registered' ) ) {
					continue;
				}

				$asset->deps[] = $dependency;
			}
		}

		wp_add_inline_script( 'itsec-packages-data', sprintf(
			"wp.data.dispatch( 'ithemes-security/core' ).__unstableLoadInitialFeatureFlags( %s );",
			wp_json_encode( ITSEC_Lib_Feature_Flags::get_enabled() )
		) );
	}

	public function add_live_reload() {
		if ( ! ITSEC_Core::is_development() ) {
			return;
		}

		foreach ( $this->handles as $handle ) {
			if ( wp_script_is( $handle ) ) {
				$url = 'http://localhost:35729/livereload.js';

				if ( is_ssl() ) {
					$url = set_url_scheme( $url, 'https' );
				}

				echo '<script src="' . esc_url( $url ) . '" async></script>';

				return;
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

	public function register_tools( Tools_Registry $registry ) {
		$registry->register( new class( 'set-encryption-key', ITSEC_Modules::get_config( 'core' ) ) extends Config_Tool {
			public function run( array $form = [] ): Result {
				if ( ITSEC_Lib_Encryption::is_available() && ! $form['confirm'] ) {
					return Result::error( new WP_Error(
						'itsec.tool.set-encryption-key.unconfirmed',
						__( 'You must check “Confirm Reset Key” to continue.', 'better-wp-security' )
					) );
				}

				try {
					$key = ITSEC_Lib_Encryption::generate_secret();
				} catch ( RuntimeException $e ) {
					return Result::error( new WP_Error(
						'itsec.tool.set-encryption-key.cannot-generate-key',
						__( 'Could not generate a random encryption key.', 'better-wp-security' )
					) );
				}

				$saved = ITSEC_Lib_Encryption::save_secret_key( $key );

				if ( ! $saved->is_success() ) {
					return $saved;
				}

				if ( ITSEC_Lib_Encryption::is_available() ) {
					$rotated = ITSEC_Lib_Encryption::rotate_with_new_key( $key );

					return Result::combine( $saved, $rotated );
				}

				return $saved;
			}

			public function get_help(): string {
				$help = parent::get_help();

				if ( ! ITSEC_Lib_Encryption::is_available() ) {
					return $help;
				}

				$help .= ' ' . __( 'Your site already has a valid encryption key. Use this tool to automatically re-encrypt all secrets with a new encryption key.', 'better-wp-security' );
				$help .= ' ' . __( 'This may take a while. If available, try running this tool with WP CLI for better performance.', 'better-wp-security' );

				return $help;
			}

			public function get_form(): array {
				if ( ITSEC_Lib_Encryption::is_available() ) {
					return parent::get_form();
				}

				return [];
			}

			public function is_available(): bool {
				return ITSEC_Files::can_write_to_files();
			}
		} );

		$registry->register( new class( 'rotate-encryption-key', ITSEC_Modules::get_config( 'core' ) ) extends Config_Tool {
			public function run( array $form = [] ): Result {
				$old_key = $form['previous'];

				return ITSEC_Lib_Encryption::rotate_with_old_key( $old_key );
			}

			public function is_available(): bool {
				return ITSEC_Lib_Encryption::has_encryption_key_changed() && ITSEC_Lib_Encryption::is_available();
			}
		} );
	}

	public function rotate_encrypted_user_keys( User_Key_Rotator $rotator, Result $result ) {
		global $wpdb;

		$user_meta_keys = array_reduce( ITSEC_Modules::get_config_list( ':all' ), function ( $keys, Module_Config $config ) {
			array_push( $keys, ...$config->get_encrypted_user_meta_keys() );

			return $keys;
		}, [] );

		if ( ! $user_meta_keys ) {
			return;
		}

		$in_sql = implode( ', ', array_fill( 0, count( $user_meta_keys ), '%s' ) );
		$query  = "SELECT * FROM {$wpdb->usermeta} WHERE meta_key IN (" . $in_sql . ")";
		$rows   = $wpdb->get_results( $wpdb->prepare( $query, $user_meta_keys ) );

		if ( $wpdb->last_error ) {
			$result->add_warning_message(
				sprintf( __( 'Could not fetch user metadata to update: %s', 'better-wp-security' ), $wpdb->last_error )
			);

			return;
		}

		$users_to_clear = [];

		foreach ( $rows as $row ) {
			$meta_id    = (int) $row->umeta_id;
			$user_id    = (int) $row->user_id;
			$meta_key   = (string) $row->meta_key;
			$meta_value = (string) $row->meta_value;

			if ( ! ITSEC_Lib_Encryption::is_encrypted( $meta_value ) ) {
				continue;
			}

			try {
				$rotated = $rotator( $meta_value, $user_id );

				do_action( 'update_user_meta', $meta_id, $user_id, $meta_key, $rotated );

				$updated = $wpdb->update(
					$wpdb->usermeta,
					[ 'meta_value' => $rotated ],
					[ 'umeta_id' => $meta_id ],
					'%s',
					'%d'
				);

				if ( $updated ) {
					do_action( 'updated_user_meta', $meta_id, $user_id, $meta_key, $rotated );
					$users_to_clear[ $user_id ] = true;
				} else {
					$result->add_warning_message(
						sprintf(
							__( 'Could not rotate \'%1$s\' for \'%2$d\': %3$s', 'better-wp-security' ),
							$meta_key,
							$user_id,
							$wpdb->last_error ?: __( 'User meta not updated.', 'better-wp-security' )
						)
					);
				}
			} catch ( RuntimeException $e ) {
				$result->add_warning_message(
					sprintf(
						__( 'Could not rotate \'%1$s\' for \'%2$d\': %3$s', 'better-wp-security' ),
						$meta_key,
						$user_id,
						$e->getMessage()
					)
				);
			}
		}

		foreach ( array_flip( $users_to_clear ) as $user_id ) {
			wp_cache_delete( $user_id, 'user_meta' );
		}
	}

	public function enable_encryption() {
		if ( ITSEC_Lib_Encryption::is_available() || ! ITSEC_Files::can_write_to_files() ) {
			return;
		}

		if ( ! ITSEC_Lib_Feature_Flags::is_enabled( 'enable_encryption' ) ) {
			return;
		}

		if ( get_site_option( 'itsec-enable-encryption-failed' ) ) {
			return;
		}

		try {
			ITSEC_Log::add_debug( 'core', 'try-enable-encryption' );
			$secret = ITSEC_Lib_Encryption::generate_secret();
			$result = ITSEC_Lib_Encryption::save_secret_key( $secret );

			if ( $result->is_success() ) {
				ITSEC_Log::add_debug( 'core', 'enabled-encryption' );
			} else {
				update_site_option( 'itsec-enable-encryption-failed', ITSEC_Core::get_current_time_gmt() );
				ITSEC_Log::add_warning( 'core', 'enable-encryption-failed', $result->get_error() );
			}
		} catch ( \Exception $e ) {
			update_site_option( 'itsec-enable-encryption-failed', ITSEC_Core::get_current_time_gmt() );
			ITSEC_Log::add_warning( 'core', 'enable-encryption-failed', new WP_Error( 'itsec.encryption.cannot-generate-secret', $e->getMessage() ) );
		}
	}
}
