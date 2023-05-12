<?php
/**
 * Class Random Values Helper Test
 *
 * @package Ewplugin
 */

/**
 * Random values helper test case.
 */
class Random_Values_Helper_Test extends WP_UnitTestCase {
	/**
	 * Test if length generated is the one wanted
	 */
	public function test_random_values_helper_length() {
		// Expected length
		$expected_length = 10;

		// Get random string
		$random_string = \EwStarter\Random_Values_Helper::get_random_string($expected_length);

		// Actual length
		$actual_length = strlen($random_string);

		$this->assertSame($expected_length, $actual_length);
	}
}
