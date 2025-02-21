<?php
/*
	Desc: Test md5 checksum function
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.05.10
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.10
	Version: 0.0.0
*/

require_once 'polyzoomerGlobals.php';
require_once 'md5chk.php';

class md5chkTest extends PHPUnit_Framework_TestCase
{
	public function testMd5Checksum() {
		
		$samplePath = testSamplePath();
		
		$this->assertEquals('eb0f3fa9e8cf566d65f962af6237568d', md5chk( $samplePath . 'sample_01.png' ));
		$this->assertEquals('4346d95c9be805c77677bc58591e0e61', md5chk( $samplePath . 'sample_02.png' ));
		$this->assertEquals('d2823947ffa149e6867d5e41d7e90bdb', md5chk( $samplePath . 'sample_03.png' ));
		$this->assertEquals('eb0f3fa9e8cf566d65f962af6237568d', md5chk( $samplePath . 'sample 04.png' ));
		$this->assertEquals('eb0f3fa9e8cf566d65f962af6237568d', md5chk( $samplePath . 'sample_05%$!.png' ));
	}
}

?>
