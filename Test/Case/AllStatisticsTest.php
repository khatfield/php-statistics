<?php
/**
 * All Statistics plugin tests
 */
class AllStatisticsTest extends CakeTestCase {

/**
 * Suite define the tests for this plugin
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All Statistics test');

		$path = CakePlugin::path('Statistics') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}