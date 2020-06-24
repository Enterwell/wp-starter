<?php

/**
 * Class EWPlugin_Test_Case
 */
class EWPlugin_Test_Case extends WP_UnitTestCase {
	/**
	 * Activates plugin on test set up.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		\EWStarter\EWPlugin_Activator::activate();
	}

	/**
	 * Deactivates plugin on tear down
	 */
	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		\EWStarter\EWPlugin_Deactivator::deactivate();
	}
}
