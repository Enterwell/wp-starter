<?php

namespace EwStarter\Tests\Helpers;

use DI\Container;
use Exception;
use EwStarter\Main\DI_Container;
use EwStarter\Main\Interfaces\Plugin_Activator_Interface;
use WP_UnitTestCase;

abstract class Test_Case extends WP_UnitTestCase {
	/** @var Test_Entity_Helper */
	protected Test_Entity_Helper $entity_helper;

	/** @var Container */
	protected Container $container;

	/**
	 * @param string|null $name
	 * @param array $data
	 * @param string $dataName
	 *
	 * @throws Exception
	 */
	public function __construct( ?string $name = null, array $data = [], string $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );

		$this->container     = DI_Container::get_instance();
		$this->entity_helper = $this->container->get( Test_Entity_Helper::class );
	}

	/**
	 * Activates plugin on test set up.
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container = DI_Container::get_instance();
		$activator = $container->get( Plugin_Activator_Interface::class );

		$activator->activate();
	}

	/**
	 * Deactivates plugin on tear down
	 */
	public static function tear_down_after_class() {
		parent::tear_down_after_class();
	}
}
