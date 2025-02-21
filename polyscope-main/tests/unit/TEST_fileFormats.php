<?php
/*
	Desc: Test the file extension listing.
	Author:	Sebastian Schmittner
	Date: 2015.05.02 
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.02
	Version: 0.0.0
*/

require_once 'fileFormats.php';

class fileFormatsTest extends PHPUnit_Framework_TestCase 
{
		public function testFileFormats() {
			global $pz_fileFormats;
			
			$this->assertContains('svs', $pz_fileFormats);
			$this->assertContains('ndpi', $pz_fileFormats);
			$this->assertContains('tif', $pz_fileFormats);
			$this->assertContains('jpg', $pz_fileFormats);
			$this->assertContains('jpeg', $pz_fileFormats);
			$this->assertContains('png', $pz_fileFormats);
		}
}

?>
