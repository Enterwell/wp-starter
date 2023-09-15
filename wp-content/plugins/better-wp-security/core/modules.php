<?php

use iThemesSecurity\Config_Settings;
use iThemesSecurity\Config_Validator;
use iThemesSecurity\Contracts\Import_Export_Source;
use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Exception\Unsatisfied_Module_Dependencies_Exception;
use iThemesSecurity\Import_Export\Export\Export;
use iThemesSecurity\Import_Export\Import\Import_Context;
use iThemesSecurity\Lib\Result;
use iThemesSecurity\Module_Config;
use iThemesSecurity\Strauss\Pimple\Container;
use iThemesSecurity\Strauss\Pimple\Psr11\Container as Psr11Container;
use iThemesSecurity\Strauss\Psr\Container\ContainerInterface;

final class ITSEC_Modules implements Import_Export_Source {
	const DEPRECATED = [
		'settings-page.php' => '7.0.0',
	];

	/**
	 * @var ITSEC_Modules - Static property to hold our singleton instance
	 */
	static $instance = false;

	private $_available_modules = false;
	private $_module_paths = array();

	/** @var Module_Config[] */
	private $module_config = [];
	private $_default_active_modules = array();
	private $_always_active_modules = array();
	private $inherited_modules = array();
	private $_active_modules = false;
	private $_active_modules_list = false;

	/** @var ITSEC_Settings[] */
	private $_module_settings = [];

	/** @var ITSEC_Validator[] */
	private $_module_validators = [];

	private $loaded_containers = [];
	private $labels = array();
	private $returned_files = array();
	private $module_schema = array();

	/** @var Container */
	private $pimple;

	/** @var ContainerInterface */
	private $container;

	/** @var bool */
	private $initialized_container = false;

	protected function __construct() {
		add_action( 'itsec-lib-clear-caches', array( $this, 'reload_settings' ), 0 );

		$this->pimple    = new Container();
		$this->container = new Psr11Container( $this->pimple );

		$this->pimple[ ContainerInterface::class ] = $this->container;
	}

	/**
	 * Function to instantiate our class and make it a singleton
	 *
	 * @return ITSEC_Modules
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Registers a single module
	 *
	 * @static
	 *
	 * @param string $slug The unique slug to use for the module.
	 * @param string $path The absolute path to the module.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function register_module( $slug, $path ) {
		$self = self::get_instance();
		$slug = sanitize_title_with_dashes( $slug );

		if ( ! is_dir( $path ) ) {
			trigger_error( sprintf( __( 'An attempt to register the %1$s module failed since the supplied path (%2$s) is invalid. This could indicate an invalid modification or incomplete installation of the iThemes Security plugin. Please reinstall the plugin and try again.', 'better-wp-security' ), $slug, $path ) );

			return false;
		}

		if ( ! file_exists( $path . '/module.json' ) ) {
			trigger_error( sprintf( __( 'Registering a module without a module.json definition is invalid. Module: %s.', 'better-wp-security' ), $slug ) );

			return false;
		}

		$json = file_get_contents( $path . '/module.json' );

		if ( ! $json ) {
			trigger_error( sprintf( __( 'An attempt to register the %1$s module failed because it\'s configuration file is empty.', 'better-wp-security' ), $slug ) );

			return false;
		}

		$config = json_decode( $json, true );

		if ( ITSEC_Core::is_development() && ( $valid = static::validate_module_config( $config ) ) !== true ) {
			trigger_error( wp_sprintf( __( 'An attempt to register the %1$s module failed because it has an invalid configuration: %2$l', 'better-wp-security' ), $slug, ITSEC_Lib::get_error_strings( $valid ) ) );

			return false;
		}

		$config = new Module_Config( $config );
		$type   = $config->get_status();

		$self->module_config[ $slug ] = $config;

		if ( $extends = $config->get_extends() ) {
			$extends = self::get_config( $extends );

			if ( ! $extends && ITSEC_Core::is_development() ) {
				trigger_error( wp_sprintf( __( 'An attempt to register the %1$s module failed because it extends a non-registered module: %2$l', 'better-wp-security' ), $slug, $config->get_extends() ) );

				return false;
			}

			$self->module_config[ $extends->get_id() ] = $extends->extend( $config );

			if ( $config->get_status() === 'inherit' ) {
				$self->inherited_modules[ $slug ] = true;
			}
		}

		$self->_module_paths[ $slug ] = $path;
		$self->_available_modules     = array_keys( $self->_module_paths );

		if ( 'always-active' === $type ) {
			$self->_always_active_modules[ $slug ] = true;
		} elseif ( 'default-active' === $type ) {
			$self->_default_active_modules[ $slug ] = true;
		}

		// Clear cache.
		$self->_active_modules_list = false;

		self::initialize_feature_flags( $config );

		return true;
	}

	/**
	 * Deregisters a single module
	 *
	 * @static
	 *
	 * @param string $slug The unique slug to use for the module
	 *
	 * @return bool True on success, false otherwise.
	 */
	public static function deregister_module( $slug ) {
		$self = self::get_instance();
		$slug = sanitize_title_with_dashes( $slug );

		if ( isset( $self->_module_paths[ $slug ] ) ) {
			unset(
				$self->_module_paths[ $slug ],
				$self->module_config[ $slug ],
				$self->inherited_modules[ $slug ],
				$self->_always_active_modules[ $slug ],
				$self->_default_active_modules[ $slug ]
			);
			$self->_available_modules = array_keys( $self->_module_paths );

			return true;
		}

		return false;
	}

	/**
	 * Update the stored paths for each module.
	 *
	 * This is predominantly used when changing the WordPress content directory.
	 *
	 * @param string $old_dir
	 * @param string $new_dir
	 */
	public static function update_module_paths( $old_dir, $new_dir ) {
		$self = self::get_instance();

		foreach ( $self->_module_paths as $slug => $path ) {
			$self->_module_paths[ $slug ] = str_replace( $old_dir, $new_dir, $path );
		}
	}

	/**
	 * Register a module's settings controller.
	 *
	 * @param ITSEC_Settings $settings
	 */
	public static function register_settings( $settings ) {
		self::get_instance()->_module_settings[ $settings->get_id() ] = $settings;
	}

	/**
	 * Retrieve a module's settings controller.
	 *
	 * This will load a module's settings file if it has not yet been loaded.
	 *
	 * @param string $slug The module slug.
	 *
	 * @return ITSEC_Settings|null
	 */
	public static function get_settings_obj( $slug ) {
		$self = self::get_instance();

		if ( ! isset( $self->_module_settings[ $slug ] ) ) {
			self::load_module_file( 'settings.php', $slug );
		}

		if ( ! isset( $self->_module_settings[ $slug ] ) && ( $config = self::get_module_config( $slug ) ) && $config->get_settings() ) {
			$self->_module_settings[ $slug ] = new Config_Settings( $config );
		}

		if ( ! isset( $self->_module_settings[ $slug ] ) ) {
			return null;
		}

		return $self->_module_settings[ $slug ];
	}

	/**
	 * Get the default settings for a module.
	 *
	 * @param string $slug The module slug.
	 *
	 * @return array
	 */
	public static function get_defaults( $slug ) {
		$settings_obj = self::get_settings_obj( $slug );

		if ( $settings_obj ) {
			return $settings_obj->get_defaults();
		}

		return array();
	}

	/**
	 * Retrieve the default value of specific setting in a module.
	 *
	 * @param string     $slug    The module slug.
	 * @param string     $name    The name of the setting.
	 * @param mixed|null $default Optionally, specify a default value to be used if the module did not declare one.
	 *
	 * @return mixed
	 */
	public static function get_default( $slug, $name, $default = null ) {
		$settings_obj = self::get_settings_obj( $slug );

		if ( $settings_obj ) {
			return $settings_obj->get_default( $name, $default );
		}

		return $default;
	}

	/**
	 * Retrieve all of the settings for a module.
	 *
	 * @param string $slug The module slug.
	 *
	 * @return array
	 */
	public static function get_settings( $slug ) {
		$settings_obj = self::get_settings_obj( $slug );

		if ( $settings_obj ) {
			return $settings_obj->get_all();
		}

		return array();
	}

	/**
	 * Retrieve the value of a specific setting in a module.
	 *
	 * @param string     $slug    The module slug.
	 * @param string     $name    The name of the setting.
	 * @param mixed|null $default Optionally, specify a default value to be used if the requested setting does not
	 *                            exist.
	 *
	 * @return mixed
	 */
	public static function get_setting( $slug, $name, $default = null ) {
		$settings_obj = self::get_settings_obj( $slug );

		if ( $settings_obj ) {
			return $settings_obj->get( $name, $default );
		}

		return $default;
	}

	/**
	 * Update all of a module's settings at once.
	 *
	 * The values will be validated, updated in-memory, and persisted.
	 *
	 * @param string $slug     The module slug.
	 * @param array  $settings New settings values.
	 *
	 * @return array|WP_Error
	 */
	public static function set_settings( $slug, $settings ) {
		$settings_obj = self::get_settings_obj( $slug );

		if ( ! $settings_obj ) {
			$error = new WP_Error( 'itsec-modules-invalid-settings-object', sprintf( __( 'Unable to find a valid settings object for %s. Settings were unable to be saved.', 'better-wp-security' ), $slug ) );
			ITSEC_Response::add_error( $error );

			return $error;
		}

		return $settings_obj->set_all( $settings );
	}

	/**
	 * Update a single setting in a module.
	 *
	 * The new value will be validated and updated in memory. The change isn't persisted until
	 * the end of the request or a manual call to {@see ITSEC_Storage::save()}.
	 *
	 * @param string $slug  The module slug.
	 * @param string $name  The setting name to updated.
	 * @param mixed  $value The settings' new value.
	 *
	 * @return array|false
	 */
	public static function set_setting( $slug, $name, $value ) {
		$settings_obj = self::get_settings_obj( $slug );

		if ( ! $settings_obj ) {
			trigger_error( sprintf( __( 'Unable to find a valid settings object for %s. Setting was unable to be saved.', 'better-wp-security' ), $slug ) );

			return false;
		}

		return $settings_obj->set( $name, $value );
	}

	/**
	 * Reloads settings values from the database.
	 */
	public static function reload_settings() {
		foreach ( self::get_instance()->_module_settings as $settings_obj ) {
			$settings_obj->load();
		}
	}

	/**
	 * Register a module's validator controller.
	 *
	 * Only one validator per-module is supported.
	 *
	 * @param ITSEC_Validator $validator
	 */
	public static function register_validator( $validator ) {
		self::get_instance()->_module_validators[ $validator->get_id() ] = $validator;
	}

	/**
	 * Retrieve the validator for a given module.
	 *
	 * This will load a module's validator component if not yet loaded.
	 *
	 * @param string $slug The module slug.
	 *
	 * @return ITSEC_Validator|null
	 */
	public static function get_validator( $slug ) {
		$self = self::get_instance();

		if ( ! isset( $self->_module_validators[ $slug ] ) ) {
			require_once( ITSEC_Core::get_core_dir() . '/lib/validator.php' );
			self::load_module_file( 'validator.php', $slug );
		}

		if ( ! isset( $self->_module_validators[ $slug ] ) && ( $config = self::get_config( $slug ) ) && $config->get_settings() ) {
			$self->_module_validators[ $slug ] = new Config_Validator( $config );
		}

		if ( ! isset( $self->_module_validators[ $slug ] ) ) {
			return null;
		}

		return $self->_module_validators[ $slug ];
	}

	/**
	 * Get's the config for a module.
	 *
	 * @param string $slug
	 *
	 * @return Module_Config|null
	 */
	public static function get_config( $slug ) {
		return isset( self::get_instance()->module_config[ $slug ] ) ? self::get_instance()->module_config[ $slug ] : null;
	}

	/**
	 * Gets a list of module config objects.
	 *
	 * @param string $modules The module specifier.
	 *
	 * @return Module_Config[]
	 */
	public static function get_config_list( $modules = ':active' ): array {
		return array_filter( array_map( [ static::class, 'get_config' ], self::transform_modules_specifier( $modules ) ) );
	}

	/**
	 * Retrieve the slugs of all modules available to the plugin.
	 *
	 * This function is internally cached.
	 *
	 * @return string[]
	 */
	public static function get_available_modules() {
		$self = self::get_instance();

		if ( false !== $self->_available_modules ) {
			return $self->_available_modules;
		}

		if ( ! is_array( $self->_module_paths ) ) {
			$self->_module_paths = array();
		}

		$self->_available_modules = array_keys( $self->_module_paths );

		return $self->_available_modules;
	}

	/**
	 * Checks if the given module is available for use.
	 *
	 * @param string $module
	 *
	 * @return bool
	 */
	public static function is_available( $module ) {
		return isset( self::get_instance()->_module_paths[ $module ] );
	}

	/**
	 * Retrieve the slugs of all active modules.
	 *
	 * This includes user activated and default activated modules. The result is internally cached.
	 *
	 * @return string[]
	 */
	public static function get_active_modules() {
		$self = self::get_instance();

		if ( is_array( $self->_active_modules_list ) ) {
			return $self->_active_modules_list;
		}

		$self->_active_modules = get_site_option( 'itsec_active_modules', array() );

		if ( ! is_array( $self->_active_modules ) ) {
			$self->_active_modules = array();
		} elseif ( isset( $self->_active_modules[0] ) ) {
			// Found data from an old format.
			foreach ( $self->_active_modules as $key => $value ) {
				if ( ! is_bool( $value ) ) {
					unset( $self->_active_modules[ $key ] );

					if ( ! isset( $self->_active_modules[ $value ] ) ) {
						$self->_active_modules[ $value ] = true;
					}
				}
			}

			if ( is_multisite() ) {
				update_site_option( 'itsec_active_modules', $self->_active_modules );
			} else {
				update_option( 'itsec_active_modules', $self->_active_modules );
			}
		}

		$default_active_modules = apply_filters( 'itsec-default-active-modules', array_keys( $self->_default_active_modules ) );

		if ( ! is_array( $default_active_modules ) ) {
			$default_active_modules = array();
		}

		foreach ( $default_active_modules as $module ) {
			if ( ! isset( $self->_active_modules[ $module ] ) ) {
				$self->_active_modules[ $module ] = true;
			}
		}

		$self->_active_modules_list = array();

		foreach ( $self->_active_modules as $module => $active ) {
			if ( $active ) {
				$self->_active_modules_list[] = $module;
			}
		}

		return $self->_active_modules_list;
	}

	/**
	 * Retrieve the slugs of all modules that are required to be active.
	 *
	 * @return string[]
	 */
	public static function get_always_active_modules() {
		$self = self::get_instance();

		return array_keys( $self->_always_active_modules );
	}

	/**
	 * Check if a module is configured to be always active.
	 *
	 * @param string $module_id The module slug.
	 *
	 * @return bool
	 */
	public static function is_always_active( $module_id ) {
		$self = self::get_instance();

		if ( ! empty( $self->_always_active_modules[ $module_id ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a module is active.
	 *
	 * @param string $module_id The module slug.
	 *
	 * @return bool
	 */
	public static function is_active( $module_id ) {
		$self = self::get_instance();

		if ( ! is_array( $self->_active_modules ) ) {
			self::get_active_modules();
		}

		if ( ! empty( $self->_always_active_modules[ $module_id ] ) ) {
			return true;
		}

		if ( isset( $self->_active_modules[ $module_id ] ) ) {
			return $self->_active_modules[ $module_id ];
		}

		return false;
	}

	/**
	 * Activate a single module using its ID
	 *
	 * @param string $module_id           The ID of the module to activate
	 * @param array  $args                Additional arguments to customize behavior.
	 *
	 * @type bool    $ignore_requirements Whether to skip evaluating module requirements.
	 *
	 * @return bool|WP_Error If the module can be activated, true if it was previously active and false if it was
	 *                       previously inactive. If the module cannot be activated, a WP_Error object is returned.
	 */
	public static function activate( $module_id, array $args = [] ) {
		$self = self::get_instance();

		if ( self::is_always_active( $module_id ) ) {
			return new WP_Error( 'itsec-modules-cannot-activate-always-active-module', sprintf( __( 'The %s module is a Core module and cannot be activated or deactivated.', 'better-wp-security' ), $module_id ) );
		}

		if ( empty( $args['ignore_requirements'] ) ) {
			$validated = self::validate_module_requirements( $module_id, 'activate' );

			if ( $validated->has_errors() ) {
				return new WP_Error(
					'itsec-modules-cannot-activate-module-unsatisfied-requirements',
					__( 'Cannot activate module.', 'better-wp-security' ) . ' ' .
					implode( ' ', ITSEC_Lib::get_error_strings( $validated ) ),
					[
						'status' => WP_Http::BAD_REQUEST
					]
				);
			}
		}

		if ( ! is_array( $self->_active_modules ) ) {
			self::get_active_modules();
		}

		$was_active = false;

		if ( isset( $self->_active_modules[ $module_id ] ) ) {
			$was_active = $self->_active_modules[ $module_id ];
		}

		try {
			self::load_module_file( 'activate.php', $module_id );
		} catch ( Unsatisfied_Module_Dependencies_Exception $e ) {
			return new WP_Error( 'itsec-modules-cannot-activate-module-unsatisfied-dependencies', $e->getMessage(), [
				'status' => WP_Http::INTERNAL_SERVER_ERROR,
			] );
		}

		$self->_active_modules[ $module_id ] = true;
		self::set_active_modules( $self->_active_modules );

		ITSEC_Core::get_scheduler()->register_events_for_module( $module_id );

		return $was_active;
	}

	/**
	 * Deactivate a single module using its ID
	 *
	 * @param string $module_id The ID of the module to deactivate
	 *
	 * @return bool|WP_Error If the module can be deactivated, true if it was previously active and false if it was
	 *                       previously inactive. If the module cannot be deactivated, a WP_Error object is returned.
	 */
	public static function deactivate( $module_id ) {
		$self = self::get_instance();

		if ( self::is_always_active( $module_id ) ) {
			return new WP_Error( 'itsec-modules-cannot-activate-always-active-module', sprintf( __( 'The %s module is a Core module and cannot be activated or deactivated.', 'better-wp-security' ), $module_id ) );
		}

		if ( ! is_array( $self->_active_modules ) ) {
			self::get_active_modules();
		}

		$was_active = false;

		if ( isset( $self->_active_modules[ $module_id ] ) ) {
			$was_active = $self->_active_modules[ $module_id ];
		}

		self::load_module_file( 'deactivate.php', $module_id );

		$self->_active_modules[ $module_id ] = false;
		self::set_active_modules( $self->_active_modules );

		if ( $config = self::get_config( $module_id ) ) {
			ITSEC_Core::get_scheduler()->unregister_events_for_config( $config );
		}

		return $was_active;
	}

	/**
	 * Change the active and deactivate modules in bulk.
	 *
	 * The deactivation routine for no-longer active modules will NOT be run.
	 *
	 * @param string[] $new_active_modules
	 *
	 * @return bool
	 */
	public static function set_active_modules( $new_active_modules ) {
		$self = self::get_instance();

		if ( ! is_array( $new_active_modules ) ) {
			return false;
		}

		// Ensure that the new values are sane by using the current active modules as a starting set of defaults.
		self::get_active_modules();
		$self->_active_modules = array_merge( $self->_active_modules, $new_active_modules );

		$self->_active_modules_list = array();

		foreach ( $self->_active_modules as $module => $active ) {
			if ( $active ) {
				$self->_active_modules_list[] = $module;
			}
		}

		if ( is_multisite() ) {
			update_site_option( 'itsec_active_modules', $self->_active_modules );
		} else {
			update_option( 'itsec_active_modules', $self->_active_modules );
		}

		return true;
	}

	/**
	 * Attempt to load a module(s)'s file.
	 *
	 * The file will only be loaded once and will not error if does not exist.
	 *
	 * @param string          $file    The file name to load, including extension.
	 * @param string|string[] $modules The modules to load the files from. Accepts either a module slug, an array of
	 *                                 module slugs, ':all' to load the files from all modules, or ':active' to load the
	 *                                 files from active modules.
	 * @param callable|null   $process Callback to run returned values from module files through.
	 *
	 * @return bool|WP_Error True if a module matching the $modules parameter is found, false otherwise.
	 */
	public static function load_module_file( $file, $modules = ':all', callable $process = null ) {
		if ( isset( self::DEPRECATED[ $file ] ) ) {
			_deprecated_file( $file, self::DEPRECATED[ $file ] );

			return false;
		}

		$self = self::get_instance();

		if ( ! $modules = self::transform_modules_specifier( $modules ) ) {
			return false;
		}

		foreach ( $modules as $module ) {
			if ( empty( $self->_module_paths[ $module ] ) ) {
				continue;
			}

			$config = self::get_config( $module );

			if ( $config && $config->is_deprecated() ) {
				_deprecated_file( "{$self->_module_paths[ $module ]}/{$file}", $config->get_deprecated_version() );
			}

			$self->load_container_definitions( $module );
			$returned = null;

			if ( self::get_container()->has( "module.{$module}.files" ) ) {
				$files = self::get_container()->get( "module.{$module}.files" );

				if ( isset( $files[ $file ] ) ) {
					$returned = $files[ $file ];
				}
			}

			if ( ! $returned ) {
				$path = "{$self->_module_paths[$module]}/{$file}";

				if ( ! file_exists( $path ) ) {
					continue;
				}

				if ( array_key_exists( $path, $self->returned_files ) ) {
					$returned = $self->returned_files[ $path ];
				} else {
					$returned                      = include_once( $path );
					$self->returned_files[ $path ] = $returned;
				}
			}

			if ( $returned ) {
				if ( $process ) {
					$process( $returned, $module );
				} else {
					$self->run( $returned );
				}
			}
		}

		return true;
	}

	/**
	 * Gets the module's config definition.
	 *
	 * @param string $id The module id.
	 *
	 * @return Module_Config|null
	 */
	public static function get_module_config( $id ) {
		return isset( self::get_instance()->module_config[ $id ] ) ? self::get_instance()->module_config[ $id ] : null;
	}

	/**
	 * Get a list of the active modules to run.
	 *
	 * @return string[]
	 */
	public static function get_active_modules_to_run() {
		if ( ITSEC_Core::is_temp_disable_modules_set() ) {
			$modules = array();
		} else {
			$modules = self::get_active_modules();
		}

		$modules = array_merge( $modules, array_keys( self::get_instance()->_always_active_modules ) );
		$modules = array_unique( $modules );
		$modules = array_filter( $modules, static function ( $module ) {
			return ! self::validate_module_requirements( $module, 'run' )->has_errors();
		} );

		foreach ( self::get_instance()->inherited_modules as $slug => $_ ) {
			if ( self::is_active( self::get_config( $slug )->get_extends() ) ) {
				$modules[] = $slug;
			}
		}

		return $modules;
	}

	/**
	 * Fires an action to begin the registration of modules.
	 */
	public static function init_modules() {
		do_action( 'itsec-register-modules' );
	}

	/**
	 * Initialize the container.
	 */
	public static function initialize_container() {
		$load = require __DIR__ . '/container.php';

		if ( is_callable( $load ) && ! self::get_instance()->initialized_container ) {
			$load( self::get_instance()->pimple );
		}

		self::get_instance()->initialized_container = true;
	}

	/**
	 * Load and run all active modules.
	 */
	public static function run_active_modules() {
		self::initialize_container();

		foreach ( self::get_active_modules_to_run() as $module ) {
			self::get_instance()->load_container_definitions( $module );
		}

		self::load_module_file( 'active.php', 'global' );
		// The active.php file is for code that will only run when the module is active.
		self::load_module_file( 'active.php', ':active' );
	}

	/**
	 * Run the activation routine for all registered modules.
	 */
	public function run_activation() {
		self::load_module_file( 'setup.php' );

		do_action( 'itsec_modules_do_plugin_activation' );
	}

	/**
	 * Run the deactivation routine for all registered modules.
	 */
	public function run_deactivation() {
		self::load_module_file( 'setup.php' );

		do_action( 'itsec_modules_do_plugin_deactivation' );
	}

	/**
	 * Run the uninstall routine for all registered modules.
	 */
	public static function run_uninstall() {
		self::load_module_file( 'setup.php' );

		do_action( 'itsec_modules_do_plugin_uninstall' );
	}

	/**
	 * Run the upgrade routine for all registered modules.
	 *
	 * @param int $old_version
	 * @param int $new_version
	 */
	public function run_upgrade( $old_version, $new_version ) {
		self::load_module_file( 'setup.php' );

		do_action( 'itsec_modules_do_plugin_upgrade', $old_version, $new_version );
	}

	/**
	 * Get the container.
	 *
	 * @return ContainerInterface
	 */
	public static function get_container() {
		if ( ! self::get_instance()->initialized_container ) {
			self::initialize_container();
		}

		return self::get_instance()->container;
	}

	/**
	 * Load the settings controller for all registered modules.
	 *
	 * This function can only be run once per-request.
	 */
	public function load_settings_page() {
		self::load_module_file( 'settings-page.php' );
	}

	/**
	 * Get labels for a module.
	 *
	 * @param string $module
	 *
	 * @return array
	 */
	public static function get_labels( $module ) {
		$config = self::get_config( $module );

		if ( $config ) {
			return [
				'title'       => $config->translate( Module_Config::T_ABOUT )->get_title(),
				'description' => $config->translate( Module_Config::T_ABOUT )->get_description(),
			];
		}

		if ( ! isset( self::get_instance()->labels[ $module ] ) ) {
			self::get_instance()->labels[ $module ] = [];
			self::load_module_file( 'labels.php', $module, function ( $labels, $module ) {
				if ( is_array( $labels ) ) {
					self::get_instance()->labels[ $module ] = $labels;
				}
			} );
		}

		return self::get_instance()->labels[ $module ];
	}

	/**
	 * Validates a module's requirements.
	 *
	 * @param string $module
	 * @param string $mode The mode to evaluate for. Either 'activate' or 'run'.
	 *
	 * @return WP_Error
	 */
	public static function validate_module_requirements( string $module, string $mode ): WP_Error {
		if ( defined( 'ITSEC_IGNORE_MODULE_REQUIREMENTS' ) && ITSEC_IGNORE_MODULE_REQUIREMENTS ) {
			return new WP_Error();
		}

		$config = self::get_config( $module );

		if ( ! $config || ! $config->get_requirements() ) {
			return new WP_Error();
		}

		$requirements = $config->get_requirements();
		$check        = [];

		if ( isset( $requirements['ssl'] ) && ( $mode === 'activate' || $requirements['ssl']['validate'] === $mode ) ) {
			$check['ssl'] = true;
		}

		if ( isset( $requirements['feature-flags'] ) && ( $mode === 'activate' || $requirements['feature-flags']['validate'] === $mode ) ) {
			$check['feature-flags'] = $requirements['feature-flags']['flags'];
		}

		if ( isset( $requirements['multisite'] ) && ( $mode === 'activate' || $requirements['multisite']['validate'] === $mode ) ) {
			$check['multisite'] = $requirements['multisite']['status'];
		}

		if ( isset( $requirements['server'] ) && ( $mode === 'activate' || $requirements['server']['validate'] === $mode ) ) {
			$check['server'] = [
				'php'        => $requirements['server']['php'] ?? null,
				'extensions' => $requirements['server']['extensions'] ?? [],
			];
		}

		return ITSEC_Lib::evaluate_requirements( $check );
	}

	public function get_export_slug(): string {
		return 'modules';
	}

	public function get_export_title(): string {
		return __( 'Features', 'better-wp-security' );
	}

	public function get_export_description(): string {
		return __( 'List of active modules.', 'better-wp-security' );
	}

	public function get_export_options_schema(): array {
		return [];
	}

	public function get_export_schema(): array {
		return [
			'type'                 => 'object',
			'additionalProperties' => [
				'type' => 'string',
				'enum' => [ 'active', 'inactive' ],
			],
		];
	}

	public function get_transformations(): array {
		return [];
	}

	public function export( $options ): Result {
		$data = [];

		foreach ( self::get_available_modules() as $module ) {
			if ( ! self::is_always_active( $module ) ) {
				$data[ $module ] = self::is_active( $module ) ? 'active' : 'inactive';
			}
		}

		return Result::success( $data );
	}

	public function import( Export $from, Import_Context $context ): Result {
		$result = Result::success();

		if ( ! $from->has_data( $this->get_export_slug() ) ) {
			return $result;
		}

		foreach ( $from->get_data( $this->get_export_slug() ) as $module => $status ) {
			$success = true;

			if ( $status === 'active' && ! self::is_active( $module ) ) {
				$success = self::activate( $module );
			} elseif ( $status === 'inactive' && self::is_active( $module ) ) {
				$success = self::deactivate( $module );
			}

			if ( is_wp_error( $success ) ) {
				$result->add_warning_message( ...ITSEC_Lib::get_error_strings( $success ) );
			}
		}

		return $result;
	}

	/**
	 * Validates a module's schema.
	 *
	 * @param array $config
	 *
	 * @return true|WP_Error
	 */
	private static function validate_module_config( $config ) {
		$self = self::get_instance();

		if ( ! $self->module_schema ) {
			$self->module_schema = ITSEC_Lib::resolve_schema_refs(
				json_decode( file_get_contents( __DIR__ . '/module-schema.json' ), true )
			);
		}

		return rest_validate_value_from_schema( $config, $self->module_schema, 'config' );
	}

	/**
	 * Initializes the module's feature flags.
	 *
	 * @param Module_Config $config
	 */
	private static function initialize_feature_flags( Module_Config $config ) {
		if ( ! $config->get_feature_flags() ) {
			return;
		}

		$flags = $config->get_feature_flags();

		if ( self::is_active( $config->get_id() ) ) {
			$register = array_keys( $flags );
		} else {
			$register = $config->get_requirements()['feature-flags']['flags'] ?? [];
		}

		foreach ( $register as $flag ) {
			if ( isset( $flags[ $flag ] ) ) {
				ITSEC_Lib_Feature_Flags::register_flag( $flag, $flags[ $flag ] );
			}
		}
	}

	/**
	 * Checks if the given module has the requested file.
	 *
	 * @param string $module The module id.
	 * @param string $file   The filename to check.
	 *
	 * @return bool
	 */
	private static function module_has_file( $module, $file ) {
		return file_exists( self::get_instance()->_module_paths[ $module ] . '/' . $file );
	}

	/**
	 * Transforms a module specifier to a list of modules.
	 *
	 * @param string|array $modules The modules specifier.
	 *
	 * @return string[]
	 */
	private static function transform_modules_specifier( $modules ) {
		if ( ':all' === $modules ) {
			return self::get_available_modules();
		}

		if ( ':active' === $modules ) {
			return self::get_active_modules_to_run();
		}

		if ( is_string( $modules ) ) {
			return [ $modules ];
		}

		if ( is_array( $modules ) ) {
			return $modules;
		}

		return [];
	}

	private function run( $definition ) {
		if ( $definition && is_string( $definition ) ) {
			$object = $this->container->get( $definition );

			if ( $object instanceof Runnable ) {
				$object->run();
			}
		}
	}

	/**
	 * Load the container definitions for a module.
	 *
	 * @param string $module
	 */
	private function load_container_definitions( $module ) {
		if ( ! isset( $this->loaded_containers[ $module ] ) && isset( $this->_module_paths[ $module ] ) ) {
			$path = $this->_module_paths[ $module ] . '/container.php';

			if ( file_exists( $this->_module_paths[ $module ] . '/container.php' ) && $register = include( $path ) ) {
				$this->loaded_containers[ $module ] = true;
				$register( $this->pimple );
			} else {
				$this->loaded_containers[ $module ] = false;
			}
		}
	}
}

ITSEC_Modules::get_instance();
