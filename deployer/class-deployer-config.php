<?php

namespace Deployer;

use \Exception;

/**
 * Class DeployConfig
 */
class DeployerConfig {
	static $ssh = [
		'host'                  => '',
		'username'              => '',
		'private_key_file_path' => '',
	];
	static $git = [
		'project_url_with_credentials' => '',
		'branch'                       => 'prod',
		'main_branch'                  => 'dev'
	];
	static $project = [
		'name' => 'deployer-test'
	];
	static $wordpress = [
		'theme_name'    => '',
		'shared_files'  => [ 'wp-config.php' ],
		'shared_dirs'   => [ 'wp-content/uploads' ],
		'writable_dirs' => [ 'wp-content/uploads' ],
	];

	static $js_build_dirs = [];

	static $php_version = '7.0';

	/**
	 * Loads config from file.
	 * @throws Exception
	 */
	static function load() {

		// Get json config file
		$private_config_file = DEPLOYER_CONFIG_DIR_PATH . '/deployer-config-private.json';
		$public_config_file  = DEPLOYER_CONFIG_DIR_PATH . '/deployer-config-public.json';

		if ( ! file_exists( $private_config_file ) ) {
			throw new Exception( 'Private configuration file does not exit!' );
		}

		if ( ! file_exists( $public_config_file ) ) {
			throw new Exception( 'Public configuration file does not exit!' );
		}

		$private_file_content = file_get_contents( $private_config_file );
		$public_file_content  = file_get_contents( $public_config_file );

		// Decode json file
		$private_config = json_decode( $private_file_content, 'ARRAY_A' );
		$public_config  = json_decode( $public_file_content, 'ARRAY_A' );

		// Validate files
		if ( empty( $private_config ) || empty( $public_config ) ) {
			throw new Exception( 'Configuration file(s) not valid.' );
		}

		// Merge private and public configuration files
		$config = \array_merge( $public_config, $private_config );

		// Save project config
		static::$project['name'] = $config['project']['name'];

		// Save ssh config
		static::$ssh['host']                  = $config['ssh']['address'];
		static::$ssh['username']              = $config['ssh']['username'];
		static::$ssh['private_key_file_path'] = $config['ssh']['privateKeyFilePath'];

		// Save git config
		static::$git['project_url_with_credentials'] = $config['git']['projectUrlWithCredentials'];
		static::$git['branch']                       = $config['git']['deployBranch'];
		static::$git['main_branch']                  = $config['git']['buildBranch'];

		// Save WordPress config
		static::$wordpress['theme_name']    = $config['wordpress']['themeName'];
		static::$wordpress['shared_files']  = $config['sharedFiles'];
		static::$wordpress['shared_dirs']   = $config['sharedDirs'];
		static::$wordpress['writable_dirs'] = $config['writableDirs'];

		// Save js build config
		static::$js_build_dirs = $config['jsBuildDirs'];

		if ( ! empty( $config['phpVersion'] ) ) {
			static::$php_version = $config['phpVersion'];
		}
	}
}