<?php
/*
	Desc: Test for getDateTime.
	Author:	Sebastian Schmittner
	Date: 2015.05.02 
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.02
	Version: 0.0.0
*/

require_once 'getDateTime.php';

class getDateTimeTest extends PHPUnit_Framework_TestCase
{
	public function testGetDateTime() {
		
		$dateTime = getDateTime();

		$this->assertArrayHasKey('year', $dateTime);
		$this->assertArrayHasKey('month', $dateTime);
		$this->assertArrayHasKey('day', $dateTime);
		$this->assertArrayHasKey('hour', $dateTime);
		$this->assertArrayHasKey('minute', $dateTime);
		$this->assertArrayHasKey('second', $dateTime);
		
		$patternYear 	= '/^[0-9]{4}$/';
		$patternMonth 	= '/^[0-9]{1,2}$/';
		$patternDay 	= '/^[0-9]{1,2}$/';
		$patternHour 	= '/^[0-9]{1,2}$/';
		$patternMinute 	= '/^[0-9]{1,2}$/';
		$patternSecond 	= '/^[0-9]{1,2}$/';
		
		$this->assertEquals(1, preg_match($patternYear	, $dateTime['year']));
		$this->assertEquals(1, preg_match($patternMonth	, $dateTime['month']));
		$this->assertEquals(1, preg_match($patternDay	, $dateTime['day']));
		$this->assertEquals(1, preg_match($patternHour	, $dateTime['hour']));
		$this->assertEquals(1, preg_match($patternMinute, $dateTime['minute']));
		$this->assertEquals(1, preg_match($patternSecond, $dateTime['second']));
	}
}

?>

