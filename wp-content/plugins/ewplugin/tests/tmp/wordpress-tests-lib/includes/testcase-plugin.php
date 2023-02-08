<?php

/**
 * Class Plugin_Test_Case
 */
class Plugin_Test_Case extends WP_UnitTestCase {
	/**
	 * Activates plugin on test set up.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		\EwStarter\Plugin_Activator::activate();
	}

	/**
	 * Deactivates plugin on tear down
	 */
	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		\EwStarter\Plugin_Deactivator::deactivate();
	}
}
