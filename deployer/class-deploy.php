<?php

namespace Ew;

use function Deployer\after;
use Deployer\DeployerConfig;
use function Deployer\host;
use function Deployer\input;
use function Deployer\run;
use function Deployer\runLocally;
use function Deployer\set;
use function Deployer\task;
use function Deployer\writeln;

/**
 * Class Deploy
 * @package Ew
 */
class Deploy {
	// Write constants
	const WRITE_LEVEL_INFO = 0;
	const WRITE_LEVEL_ERROR = 1;
	const WRITE_LEVEL_TASK = 2;
	const WRITE_LEVEL_WARNING = 3;
	const WRITE_LEVEL_PLAIN = 4;

	const SKIP_JS_BUILD_ARG = 'skip-js-build';

	// Config properties
	private $deploy_branch;
	private $source_branch;
	private $js_build_dirs;
	private $theme_name;
	private $php_fpm_service_name;

	/**
	 * Deploy constructor.
	 * @throws \Exception
	 */
	public function __construct() {
		// Load deployer config
		DeployerConfig::load();

		// Save config vars
		$this->deploy_branch        = DeployerConfig::$git['branch'];
		$this->source_branch        = DeployerConfig::$git['main_branch'];
		$this->js_build_dirs        = DeployerConfig::$js_build_dirs;
		$this->theme_name           = DeployerConfig::$wordpress['theme_name'];
		$php_version                = DeployerConfig::$php_version;
		$this->php_fpm_service_name = "php{$php_version}-fpm";

		$this->set_deployer_flags();
	}

	/**
	 * Set deployer flags needed for deployment.
	 */
	public function set_deployer_flags() {
		// Project options
		set( 'application', DeployerConfig::$project['name'] );
		set( 'git_tty', false );

		// Sudo for clear and remove files
		set( 'clear_use_sudo', true );
		set( 'cleanup_use_sudo', true );

		// Ssh options
		set( 'ssh_type', 'native' );
		set( 'ssh_multiplexing', true );


		// Project repository
		set( 'repository', DeployerConfig::$git['project_url_with_credentials'] );
		set( 'branch', DeployerConfig::$git['branch'] );

		// Writable dirs by web server
		set( 'allow_anonymous_stats', false );
		set( 'deploy_path', '/var/www/{{application}}' );

		// Configure host
		host( DeployerConfig::$ssh['host'] )
			->user( DeployerConfig::$ssh['username'] )
			->identityFile( DeployerConfig::$ssh['private_key_file_path'] );

		// Deployment config
		set( 'shared_files', DeployerConfig::$wordpress['shared_files'] );
		set( 'shared_dirs', DeployerConfig::$wordpress['shared_dirs'] );
		set( 'writable_dirs', DeployerConfig::$wordpress['writable_dirs'] );
	}

	/**
	 * Define deployer tasks needed for deployment.
	 */
	public function define_tasks() {
		// Custom local tasks
		task( 'deploy:local_prepare', [ $this, 'local_prepare' ] );
		task( 'deploy:local_cleanup', [ $this, 'local_finish' ] );
		task( 'deploy:chown', [ $this, 'chown' ] );
		task( 'deploy:reload_services', [ $this, 'reload_services' ] );

		// Override deploy tasks - add our custom tasks
		task( 'deploy', [
			'deploy:local_prepare',
			'deploy:info',
			'deploy:prepare',
			'deploy:lock',
			'deploy:release',
			'deploy:update_code',
			'deploy:shared',
			'deploy:writable',
			'deploy:symlink',
			'deploy:unlock',
			'cleanup',
			'deploy:local_cleanup',
			'deploy:chown',
			'deploy:reload_services'
		] )->desc( 'Deploy your project' );

		after( 'deploy', 'success' );
	}

	/**
	 * Local prepare task.
	 *
	 * Checkouts to prod branch, builds js dirs,
	 * updates theme version, pushes changes to prod branch.
	 *
	 * @throws \Exception
	 */
	public function local_prepare() {
		$this->check_branch();
		$this->git_prepare();
		$this->build_js();
		$this->update_theme_version();
		$this->git_finish();
	}

	/**
	 * Checks if the user is on
	 * the right branch for deploy (source branch) -> usually dev
	 *
	 * @throws \Exception
	 */
	private function check_branch() {
		// Check current branch
		$current_branch = trim( runLocally( 'git symbolic-ref --short HEAD' ) );

		// Get branches string
		$branches_string = runLocally( 'git branch' );

		// Get branches list from command output
		$branches = array_map( function ( $text_value ) {

			// Clear branch names
			$text_value = str_replace( '*', '', $text_value );
			$text_value = trim( $text_value );

			return $text_value;
		}, explode( "\n", $branches_string ) );

		if ( ! in_array( $this->deploy_branch, $branches ) ) // Check for dev branch
		{
			$this->write( "Branch {$this->deploy_branch} must be created!", static::WRITE_LEVEL_ERROR );
			exit( 1 );
		}

		if ( $current_branch !== $this->source_branch ) {
			$this->write( "Deploy branch must be '{$this->source_branch}' branch!", static::WRITE_LEVEL_ERROR );
			exit( 1 );
		}
	}

	/**
	 * Helper write function used to format deployer
	 * output.
	 *
	 * @param $message
	 * @param int $write_level
	 */
	private function write( $message, $write_level = Deploy::WRITE_LEVEL_INFO ) {
		$message_prefix = '-> ';
		$message_suffix = '';
		$message_after  = false;
		if ( $write_level === Deploy::WRITE_LEVEL_ERROR ) {
			$message_prefix = '<error>!!! ';
			$message_suffix = '</error>';
		} else if ( $write_level === Deploy::WRITE_LEVEL_TASK ) {
			$message_after = true;
		} else if ( $write_level === Deploy::WRITE_LEVEL_PLAIN ) {
			$message_prefix = '';
		} else if ( $write_level === Deploy::WRITE_LEVEL_WARNING ) {
			$message_prefix = '*** ';
		}

		// Write message with prefix.
		writeln( $message_prefix . $message . $message_suffix );
		if ( $message_after ) {
			writeln( '--------------------' );
			writeln( '--------------------' );
			writeln( '--------------------' );
		}
	}

	/**
	 * Git prepare for deploy.
	 *
	 * Checkouts to deploy branch and merges source branch.
	 */
	private function git_prepare() {
		// Pull latest to dev
		runLocally( "git pull origin dev" );
		runLocally( "git checkout {$this->deploy_branch}" );
		runLocally( "git merge {$this->source_branch}" );
	}

	/**
	 * Builds JS and CSS from dirs.
	 */
	private function build_js() {
		// Check skip argument
		if ( input()->hasArgument( static::SKIP_JS_BUILD_ARG ) && ! empty( input()->getArgument( static::SKIP_JS_BUILD_ARG ) ) ) {
			$this->write( 'Skipped js build.', static::WRITE_LEVEL_WARNING );

			return;
		}

		// Check if there's any build dirs
		if ( empty( $this->js_build_dirs ) ) {
			$this->write( 'No js build dirs detected!', static::WRITE_LEVEL_WARNING );

			return;
		}

		$this->write( 'Local build started' );

		foreach ( $this->js_build_dirs as $build_dir ) {
			$dir_change_command = "cd $build_dir";

			// Remove previous builds
			// Clean previous builds
			runLocally( "$dir_change_command && rm assets/dist/*" );

			// Run yarn commands
			runLocally( "$dir_change_command && yarn build" );

			$this->write( "Finished building js for dir: $build_dir" );
		}

		$this->write( 'JS build finished', static::WRITE_LEVEL_TASK );
	}

	/**
	 * Function used to update WordPress theme version.
	 */
	private function update_theme_version() {
		$theme_dir = 'wp-content/themes/' . $this->theme_name;

		// Update theme version
		$theme_config_file  = $theme_dir . DIRECTORY_SEPARATOR . 'ew-theme-config.json';
		$theme_file_content = @file_get_contents( $theme_config_file );
		if ( empty( $theme_file_content ) ) {
			$this->write( 'Theme config file not found!', static::WRITE_LEVEL_ERROR );

			return;
		}

		$theme_config = json_decode( $theme_file_content, true );
		if ( empty( $theme_config['themeVersion'] ) ) {
			$this->write( 'Theme version not found in config. Theme version not updated.', static::WRITE_LEVEL_ERROR );

			return;
		}
		$version_parts                = explode( '.', $theme_config['themeVersion'] );
		$last_num                     = intval( array_pop( $version_parts ) );
		$version_parts[]              = $last_num + 1;
		$new_version                  = implode( '.', $version_parts );
		$theme_config['themeVersion'] = $new_version;
		$saved                        = @file_put_contents( $theme_config_file, json_encode( $theme_config, JSON_PRETTY_PRINT ) );

		if ( $saved ) {
			$this->write( "Theme version updated to: $new_version", static::WRITE_LEVEL_TASK );
		} else {
			$this->write( "Theme version file could not be written. Theme version not updated!", static::WRITE_LEVEL_ERROR );
		}

	}

	/**
	 * Pushes changes to prod branch.
	 */
	private function git_finish() {
		// Commit changes to prod branch
		$date           = new \DateTime();
		$commit_message = "Prod build {$date->format('U')}. DateTime: {$date->format('d.m.Y. H:i:s')}";

		// Add and commit
		$output = runLocally( 'git status --porcelain' );

		if ( ! empty( $output ) ) {
			runLocally( 'git add -A *' );
			runLocally( "git commit --allow-empty -am \"{$commit_message}\"" );
		}

		// Push to remote
		runLocally( "git push -q origin {$this->deploy_branch}" );
		$this->write( 'Local deploy prepare finished!', static::WRITE_LEVEL_TASK );
	}

	/**
	 * Chown function to be run on the server.
	 */
	public function chown() {
		run( 'sudo chown -R www-data {{deploy_path}}' );
		run( 'sudo chgrp -R www-data {{deploy_path}}' );
		run( 'sudo chmod -R g+rwx {{deploy_path}}' );
	}

	/**
	 * Reloads php and nginx services on the remote server.
	 */
	public function reload_services() {
		run( "sudo /usr/sbin/service {$this->php_fpm_service_name} reload" );
		run( 'sudo /usr/sbin/service nginx reload' );
	}

	/**
	 * Returns user to the source branch.
	 */
	public function local_finish() {
		$git_source_branch = $this->source_branch;

		runLocally( "git checkout $git_source_branch" );
	}
}