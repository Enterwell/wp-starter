<?php

namespace EwStarter\Main;

use DI\Container;
use DI\ContainerBuilder;
use EwStarter\Admin\Interfaces\Plugin_Admin_Interface;
use EwStarter\Admin\Plugin_Admin;
use EwStarter\Controllers\User_Applications_Controller;
use EwStarter\Main\Interfaces\Plugin_Activator_Interface;
use EwStarter\Main\Interfaces\Plugin_i18n_Interface;
use EwStarter\Main\Interfaces\Plugin_Loader_Interface;
use EwStarter\Public\Interfaces\Plugin_Public_Interface;
use EwStarter\Public\Plugin_Public;
use EwStarter\Repositories\Interfaces\User_Applications_Repository_Interface;
use EwStarter\Repositories\User_Applications_Repository;
use EwStarter\Services\Files_Service;
use EwStarter\Services\Interfaces\Files_Service_Interface;
use EwStarter\Services\Interfaces\User_Applications_Service_Interface;
use EwStarter\Services\User_Applications_Service;
use EwStarter\Tests\Helpers\Test_Entity_Helper;
use Exception;
use function DI\autowire;
use function DI\create;
use function DI\get;

/**
 * Class DI_Container_Builder
 */
class DI_Container {
	/**
	 * @var Container
	 */
	private static Container $container;

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function __clone() {
		throw new \Exception( 'Cannot clone a singleton.' );
	}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton*
	 * instance.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function __wakeup() {
		throw new \Exception( 'Cannot unserialize a singleton.' );
	}

	/**
	 * @return Container
	 * @throws Exception
	 */
	public static function get_instance(): Container {
		if ( empty( self::$container ) ) {
			$builder = new ContainerBuilder();

			$builder->addDefinitions( self::get_definitions() );

			// TODO: If production add compilation

			self::$container = $builder->build();
		}

		return self::$container;
	}

	/**
	 * Gets all the service definitions for the plugin.
	 *
	 * @return array
	 */
	private static function get_definitions(): array {
		return [
			// String values
			'plugin.name'    => 'ewstarter',
			'plugin.version' => '0.0.1',

			'plugin.db.version'                           => '0.0.1',
			'plugin.db.version_option'                    => 'ewstarter_db_version',

			// Activator & de-activator
			Plugin_Activator_Interface::class             => create( Plugin_Activator::class )
				->constructor( get( 'plugin.db.version' ), get( 'plugin.db.version_option' ) ),

			// Plugin instances
			Plugin_Loader_Interface::class                => create( Plugin_Loader::class ),
			Plugin_i18n_Interface::class                  => create( Plugin_i18n::class )->constructor( get( 'plugin.name' ) ),
			Plugin_Admin_Interface::class                 => autowire( Plugin_Admin::class ),
			Plugin_Public_Interface::class                => autowire( Plugin_Public::class ),

			// Repositories
			User_Applications_Repository_Interface::class => autowire( User_Applications_Repository::class ),

			// Services
			Files_Service_Interface::class                => autowire( Files_Service::class ),
			User_Applications_Service_Interface::class    => autowire( User_Applications_Service::class ),

			// Controllers
			User_Applications_Controller::class           => autowire( User_Applications_Controller::class ),

			// Other
			Test_Entity_Helper::class                  => autowire( Test_Entity_Helper::class )
		];
	}
}
