<?php
/*
	Desc: Test for isNotEmpty.
	Author:	Sebastian Schmittner
	Date: 2015.05.02
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.02
	Version: 0.0.0
*/

require_once "isNotEmpty.php";

class isNotEmptyTest extends PHPUnit_Framework_TestCase
{
	public function testIsNotEmpty() {
		
		$this->assertEquals(false, isNotEmpty(''));
		$this->assertEquals(false, isNotEmpty(null));

		$this->assertEquals(true, isNotEmpty('123abc'));
		$this->assertEquals(true, isNotEmpty(' '));
	}
	
}

?>

