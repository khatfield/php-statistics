<?php
App::uses('Statistics', 'Statistics.Lib');

/**
 * Statistics Test Case
 *
 * @property Statistics $Statistics
 */
class StatisticsTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Statistics = new Statistics();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();

		unset($this->Statistics);
	}

}
