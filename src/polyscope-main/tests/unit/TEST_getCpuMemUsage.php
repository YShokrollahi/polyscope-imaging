<?php
/*
	Desc: Test the getCpuMemUsage.
	Author:	Sebastian Schmittner
	Date: 2015.05.03 20:21:05 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.03 20:21:05 (+02:00)
	Version: 0.0.1
*/

namespace Polyzoomer;

require_once 'getCpuMemUsage.php';

/**
 * Override sys_getloadavg for testing
 *
 * @return array
 */
function sys_getloadavg() {
	return getCpuMemUsageTest::$loadAverage ?: \sys_getloadavg();
}

class getCpuMemUsageTest extends \PHPUnit_Framework_TestCase 
{
	/**
	 * @var array $loadAverage
	 */
	public static $loadAverage;
	
	protected function setUp() {
		self::$loadAverage = array(2, 4, 5);
	}
		
	protected function tearDown() {
		self::$loadAverage = null;
	}
	
	public function createLoadAverageResult( $first, $second, $third ) {
	
		$result = array(
			'loadAverage' => array( $first, $second, $third ),
			'weightedAverage' => ($first + $second * 5.0 + $third * 15.0) / 21.0
		);
		
		$result = array( 
			'cpu' => $result
		);
		
		return $result;
	}
	
	/**
	 * Tests
	 */
	public function testGetLoadAverage() {
		
		// setup
		$expected = $this->createLoadAverageResult(2, 4, 5);
		$expected = $expected['cpu'];
		
		// execute
		$output = getLoadAverage();
		
		// check
		$this->assertArrayHasKey('loadAverage', $output);
		$this->assertArrayHasKey('weightedAverage', $output);
		
		$this->assertEquals($expected, $output);
	}
	
	public function testGetCpuMemUsage() {
		
		// setup
		$expected = $this->createLoadAverageResult(2, 4, 5);
		
		// execute 
		$output = getCpuMemUsage();
		
		$this->assertEquals($expected, $output);
	}
}

?>

