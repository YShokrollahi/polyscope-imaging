<?php
/*
	Desc: Test for the mountedMedia function.
	Author:	Sebastian Schmittner
	Date: 2015.05.11 10:46:48 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.11 13:42:49 (+02:00)
	Version: 0.0.2
*/

require_once 'polyzoomerGlobals.php';
require_once 'getMountedMedia.php';

class getMountedMediaTest extends PHPUnit_Framework_TestCase
{
	private $mediaPath = '';
	private $mounts = array(
							'AHEINDL',
							'Test',
							'f55c626a-d7ca-447e-88c9-178a660be1fd',
							'A B C',
							'A$$%!'
							);

	protected function setUp() 
	{
		if(!file_exists( testTempPath() )) {
			mkdir(testTempPath());
		}
		
		$this->mediaPath = testTempPath() . 'media/';
		
		$this->assertEquals(false, file_exists($this->mediaPath));
		
		mkdir($this->mediaPath);
		
		for($i = 0; $i < count($this->mounts); ++$i) {
			mkdir($this->mediaPath . $this->mounts[$i] . '/');
		}
	}
	
	protected function tearDown() 
	{
		for($i = 0; $i < count($this->mounts); ++$i) {
			is_dir($this->mediaPath . $this->mounts[$i] . '/') && rmdir($this->mediaPath . $this->mounts[$i] . '/');
		}

		is_dir($this->mediaPath) && rmdir($this->mediaPath);
	}
	
	public function testGetMountedMedia() {
		
		$this->assertEquals(sort($this->mounts), sort(getMountedMedia($this->mediaPath, mediaExcludes())));
	}
}

?>

