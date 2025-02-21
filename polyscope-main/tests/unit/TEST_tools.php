<?php
/*
	Desc: Test for Misc Tools.
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.12.17
	Last Author: Sebastian Schmittner (stp.schmittner@gmail.com)
	Last Date: 2015.12.17
	Version: 0.0.1
*/

require_once 'tools.php';

class TEST_tools extends PHPUnit_Framework_TestCase
{
	public function testPadNumber() {
		
		$numbers = array(465, 5, 0);
		$digitCount = array(5, 4, 3, 2, 1, 0);
		
		$expected = array(
			array('00465','0465','465','465','465','465'),
			array('00005','0005','005','05','5','5'),
			array('00000','0000','000','00','0','0')
			);
		
		for($number = 1; $number < count($numbers); ++$number){
			for($digit = 1; $digit < count($digitCount); ++$digit){
				$this->assertEquals($expected[$number][$digit], 
						    padNumber($numbers[$number],
						              $digitCount[$digit]));
			}
		}
	}
	
	public function uidHelper() {
		
		$digitCount = 8;
		
		$id = uniqueId();
		
		$this->assertEquals($digitCount, strlen($id));
		$this->assertTrue(is_numeric($id));
	}
	
	public function testMultipleUniqueId() {
		
		$testCount = 100;
		
		for($i = 0; $i < $testCount; ++$i){
			uidHelper();
		}
	}

	public function testCleanString() {
		
		$source = array('test.email@tohome.com',
				'Test1"§@/(TestX##99=!',
				'kjhsd783kjh32969GJHSD',
				'sdjfh/(&$(578"§GI(F(/',
				'KEH(&$)RZ93rg9wg37/IT',
				'Ih()&G)(§&9839275iUGU',
				'seguio73789&/$989023(');
	
		$expected = array('test-email-tohome-com',
				  'Test1-----TestX--99--',
				  'kjhsd783kjh32969GJHSD',
				  'sdjfh-----578--GI-F--',
				  'KEH----RZ93rg9wg37-IT',
				  'Ih---G----9839275iUGU',
				  'seguio73789---989023-');
				  
		for($i = 0; $i < count($source); ++$i){
			$this->assertEquals($source[$i], $expected[$i]);
		}
	}
	
	public function testEnclose() {
		$source = array('test sample 1',
				'exec ls -ltr',
				'cat log.log',
				'Hurray! it is working.');
	
		$expected = array('"test sample 1"',
				  '"exec ls -ltr"',
				  '"cat log.log"',
				  '"Hurray! it is working."');
				  
		for($i = 0; $i < count($source); ++$i){
			$this->assertEquals($source[$i], $expected[$i]);
		}
	}
}

?>
<?

// reference: http://php.net/manual/de/function.scandir.php
define('DTA_ALL', '3');
define('DTA_FILES', '1');
define('DTA_DIRS', '2');

define('DTA_NORMAL', '1');
define('DTA_MERGE', '2');

function dirToArray($dir, $INTENT = DTA_ALL, $STYLE = DTA_NORMAL) { 
   
   $result = array(); 

   $cdir = scandir($dir); 
   foreach ($cdir as $key => $value) 
   { 
      if (!in_array($value,array(".",".."))) 
      { 
         if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) 
         { 
            if( $INTENT === DTA_ALL || $INTENT === DTA_DIRS ) {
				
				$subarray = dirToArray($dir . DIRECTORY_SEPARATOR . $value, $INTENT, $STYLE);
				
				if( $STYLE === DTA_NORMAL ) {
					$result[$value] = $subarray; 
				}
				else {
					$result = array_merge( $result, $subarray );
				}				
			}
         } 
         else 
         { 
            if( $INTENT === DTA_ALL || $INTENT === DTA_FILES ) {
				$result[] = $dir . '/' . $value; 
			}
         } 
      } 
   } 
   
   return $result; 
} 

function rglob($pattern, $flags = 0) {
	$files = glob($pattern, $flags);
	foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR) as $dir) {
		$files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
	}

	return $files;
}

function getLargest($files) {
	
	$size = 0;
	$name = "";
	
	foreach($files as $file) {
		if(filesize($file) > $size) {
			$size = filesize($file);
			$name = $file;
		}
	}
	
	return $name;
}

function safePath( $path ) {
	
	if(!file_exists( $path )) {
		$createDirectory = "mkdir -p " . enclose($path);
		executeSync($createDirectory);
	}
	
	return $path;
}

function cleanPath( $path ) {
	return str_replace('//', '/', $path);
}

function safeFileSize( $file ) {
	
	$filesize = 0;
	
	if(file_exists($file)) {
		clearstatcache();
		$filesize = filesize($file);
	}
	
	return $filesize;
}

function safeSerialize( $item ) {
	return base64_encode( serialize( $item ) );
}

function safeUnSerialize( $item ) {
	return unserialize( base64_decode( $item ) );
}

?>
