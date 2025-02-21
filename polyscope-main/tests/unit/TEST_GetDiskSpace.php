<?php
/*
	Desc: Test get disk usage
	Author:	Sebastian Schmittner
	Date: 2015.10.28
	Last Author: Sebastian Schmittner
	Last Date: 2015.10.28
	Version: 0.0.1
*/

require_once '../../isNotEmpty.php';
require_once '../../getDiskUsage.php';

class getDiskUsageTest extends PHPUnit_Framework_TestCase
{
	public function testGetDiskUsage() {
		
		$source = array("Filesystem                       Size  Used Avail Use% Mounted on",
						"/dev/mapper/zoom1-logical_zoom1   15T  1.4T   13T  10% /mnt/zoom1");
						
		$expected = '10%';
		
		$this->assertEquals($expected, getPercentUsageFromDfOutput($source));
	}
}

?>
