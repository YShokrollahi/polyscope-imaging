<?php
/*
	Desc: Test server credentials.
	Author:	Sebastian Schmittner
	Date: 2015.05.02 
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.02
	Version: 0.0.0
*/

require_once 'serverCredentials.php';

class serverCredentialsTest extends PHPUnit_Framework_TestCase
{
	public function testServerCredentials() {
		
		global $externalLink;
		
		$this->assertEquals(0, strcmp("http://polyzoomer.icr.ac.uk/", $externalLink));
	}
}

?>
