<?php
/*
	Desc: Test functions for locked file access.
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.12.16
	Last Author: Sebastian Schmittner (stp.schmittner@gmail.com)
	Last Date: 2015.12.16
	Version: 0.0.1
*/

require_once 'lockedFileAccess.php';

/*class asyncIncrement extends Thread
{
	public function __construct($filename, $howOften, $threadId) {
		$this->filename = $filename;
		$this->howOften = $howOften;
		$this->threadId = $threadId;
	}	
	
	public function run() {
		for($i = 0; $i < $this->howOften; ++$i) {
			while(atomicCounterIncrement($this->filename) === -1) {};
		}
	}
}*/

class lockedFileAccessTest extends PHPUnit_Framework_TestCase
{
	public function testSingleThreadedIncrementCounter() {
		$filename = './tests/unit/bench/counter.test';
		$counterTarget = 2000;
		
		file_put_contents($filename, '0');
		
		for($i = 0; $i < $counterTarget; ++$i) {
			while(atomicCounterIncrement($filename) === -1) {};
		}
		
		sleep(4);
		$counter = file_get_contents($filename);
		
		$this->assertEquals($counterTarget, $counter);
	}
	
	/*public function testMultiThreadedIncrementCounter() {
		$filename = './bench/counter.test';
		$counterTarget = 200000;
		$threadCount = 8;
		
		file_put_contents($filename, '0');
		
		for($i = 0; $i < $threadCount; ++$i) {
			$t[$i] = new asyncIncrement($filename, $counterTarget, $i);
			$t[$i]->start();
		}
		
		sleep(60);
		
		$counter = file_get_contents($filename);
		
		$this->assertEquals($counterTarget, $counter);
	}*/
}

/////////////////////////////////////////////////////////////////

?>
