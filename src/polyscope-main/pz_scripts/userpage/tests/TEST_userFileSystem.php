<?php
/*
	Desc: Test the file extension listing.
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.12.10 
	Last Author: Sebastian Schmittner (stp.schmittner@gmail.com)
	Last Date: 2016.01.14
	Version: 0.0.1
*/

require_once __DIR__ . '/../userFileSystem.php';

class userFileSystemIOTest extends PHPUnit_Framework_TestCase 
{
	public function testDirname() {
		$this->assertEquals(__DIR__, dirname(__DIR__ . '/userfs.json'), 'Dirname does not return only the path!');
	}
	
	public function testDataFilePath() {
		$ufs = new UserFileSystemIO('test-test-com', __DIR__, 'userfs.json');
		$expected = __DIR__ . '/userfs.json';
		
		$this->assertTrue(file_exists($expected), 'File (' . $expected . ') does not exist');
		$this->assertEquals($expected, $ufs->dataFilePath(), 'Data path is not equal');
	}
	
	public function testUfsIoConstruct_FileWrong() {
		$ufs = UserFileSystemIO::fromFile('test-test-com', 'IdoNotExist.db');
		$this->assertNull($ufs, 'Specified ufs file does not exist.');
	}
	
	public function testUfsIoConstruct_FileOk() {
		$filename = __DIR__ . '/userfs.json';
		$this->assertTrue(file_exists($filename), 'File (' . $filename . ') does not exist');

		$ufs = UserFileSystemIO::fromFile('test-test-com', $filename);
		$this->assertNotNull($ufs, 'Could not load ufs file.');
	}
	
	public function testUfsLoadFileSystem() {
		$filename = __DIR__ . '/userfs.json';
		
		$ufs = UserFileSystemIO::fromFile('test-test-com', $filename);
		
		$this->assertNotNull($ufs, 'Could not load ufs file.');
		
		$fs = $ufs->getSystem();
		$expected = json_decode('{"x":{"d.x":{"name":"d.x","id":"2"},"e.y":{"name":"e.r"},"f":{"a.u":{"name":"a.u"},"b.v":{"test":"23"}}},"f":[]}', TRUE);

		$this->assertEquals($expected, $fs, 'Loaded filesystem is different than the expected one.');
	}
	
	public function testUfsDateTime() {
		$ufs = new UserFileSystem('', '');
		$now = $ufs->getNow();
		$this->assertTrue(is_int($now));
	}
	
	public function testUfsInterface() {
		$filename = __DIR__ . '/userfs.json';
		$this->assertTrue(file_exists($filename), 'Test file does not exist.');
		$this->assertTrue(is_readable($filename), 'Test file is not readable.');
		
		$ufs = UserFileSystem::fromFile('test-test-com', $filename);
		$this->assertNotNull($ufs, 'Could not load ufs file.');
		
		$hierarchy = $ufs->getHierarchy();
		$this->assertNotNull($hierarchy, 'Hierarchy is empty.');
	}

	public function testUfsDataGetItem() {
		$filename = __DIR__ . '/userfs_worksetXX.tmp';

		if(file_exists($filename)) {
			unlink($filename);
		}
		
		$preContent = '{"x":' .
							'{"type":"DIR",' .
							 '"a":{"name":"a","type":"URL","id":"http://xxx"},' .
							 '"b":{"name":"b","type":"CACHE","owner":"c","link":"http://yyy"},' .
							 '"c":{"type":"DIR","d":{"name":"d","type":"URL","id":"http://uuu"}}' .
							'}' .
						'}';

		$this->assertTrue(file_put_contents($filename, $preContent) !== FALSE, 'Could not write contents.');
		$preContent = json_decode($preContent, TRUE);
		
		$ufs = UserFileSystem::fromFile('test-test-com', $filename);
		
		$this->assertEquals($preContent["x"], $ufs->recursiveGetItemBySelectors($preContent, $ufs->pathNameToSelectorNames('x///')), 'Cannot get "x"');
		$this->assertEquals($preContent["x"], $ufs->recursiveGetItemBySelectors($preContent, $ufs->pathNameToSelectorNames('x')), 'Cannot get "x"');
		$this->assertEquals($preContent["x"]["a"], $ufs->recursiveGetItemBySelectors($preContent, $ufs->pathNameToSelectorNames('x///a')), 'Cannot get "x/a"');
		$this->assertEquals($preContent["x"]["a"], $ufs->recursiveGetItemBySelectors($preContent, $ufs->pathNameToSelectorNames('x///a///')), 'Cannot get "x/a"');
		$this->assertEquals($preContent["x"]["b"], $ufs->recursiveGetItemBySelectors($preContent, $ufs->pathNameToSelectorNames('x///b')), 'Cannot get "x/b"');
		$this->assertEquals($preContent["x"]["c"], $ufs->recursiveGetItemBySelectors($preContent, $ufs->pathNameToSelectorNames('x///c')), 'Cannot get "x/c"');
		$this->assertEquals($preContent["x"]["c"]["d"], $ufs->recursiveGetItemBySelectors($preContent, $ufs->pathNameToSelectorNames('x///c///d')), 'Cannot get "x/c/d"');
		$this->assertEquals($preContent["x"]["c"]["d"], $ufs->recursiveGetItemBySelectors($preContent, $ufs->pathNameToSelectorNames('x///c///d///')), 'Cannot get "x/c/d"');
		
		unset($ufs);
	}
	
	public function testUfsDataAccessGetItem() {
		$filename = __DIR__ . '/userfs_worksetXX.tmp';

		if(file_exists($filename)) {
			unlink($filename);
		}
		
		$preContent = '{"x":{"type":"DIR","a":{"name":"a","type":"URL","id":"http://xxx"},"b":{"name":"b","type":"CACHE","owner":"c","link":"http://yyy"}}}';
		$this->assertTrue(file_put_contents($filename, $preContent) !== FALSE, 'Could not write contents.');
		
		$ufs = UserFileSystem::fromFile('test-test-com', $filename);
		$this->assertTrue($ufs->doesItemExist("x///a") !== FALSE, '"x/a" could not be found.');
		
		unset($ufs);
	}

	public function testUfsDataAccessAdd() {
		$filename = __DIR__ . '/userfs_worksetXX.tmp';

		if(file_exists($filename)) {
			unlink($filename);
		}
		
		$preContent = '{"x":{"a":{"name":"a","id":"b"},"b":{"name":"b","id":"c"}}}';
		$postContent = '{"x":{"a":{"name":"a","id":"b"},"b":{"name":"b","id":"c"},"g":{"name":"g","id":"45"}}}';
		$this->assertTrue(file_put_contents($filename, $preContent) !== FALSE, 'Could not write contents.');
		
		$ufs = UserFileSystem::fromFile('test-test-com', $filename);
		$this->assertTrue($ufs->addItem("x", array("name" => "g", "id" => "45")) !== FALSE, 'Could not add item.');
		
		unset($ufs);
		
		$postContentRead = file_get_contents($filename);
		$this->assertEquals(json_decode($postContent, TRUE), json_decode($postContentRead, TRUE), 'Adding item does not behave as expected.');
	}

	public function testUfsDataAccessCopy() {
		$filename = __DIR__ . '/userfs_worksetXX.tmp';

		if(file_exists($filename)) {
			unlink($filename);
		}
		
		$preContent = '{"x":{"a":{"name":"a","id":"b"},"b":{"name":"b","id":"c"}}}';
		$postContent = '{"x":{"a":{"name":"a","id":"b"},"b":{"name":"b","id":"c"}},"a":{"name":"a","id":"b"}}';
		$this->assertTrue(file_put_contents($filename, $preContent) !== FALSE, 'Could not write contents.');
		
		$ufs = UserFileSystem::fromFile('test-test-com', $filename);
		$this->assertTrue($ufs->copyItem("x///a", "///") !== FALSE, 'Could not copy item.');
		
		unset($ufs);
		
		$postContentRead = file_get_contents($filename);
		$this->assertEquals(json_decode($postContent, TRUE), json_decode($postContentRead, TRUE), 'Copying item does not behave as expected.');
	}

	public function testUfsDataAccessDelete() {
		$filename = __DIR__ . '/userfs_worksetXX.tmp';

		if(file_exists($filename)) {
			unlink($filename);
		}
		
		$preContent = '{"x":{"a":{"name":"a","id":"b"},"b":{"name":"b","id":"c"}}}';
		$postContent = '{"x":{"b":{"name":"b","id":"c"}}}';
		$this->assertTrue(file_put_contents($filename, $preContent) !== FALSE, 'Could not write contents.');
		
		$ufs = UserFileSystem::fromFile('test-test-com', $filename);
		$result = $ufs->deleteItem("x///a");
		$this->assertTrue($result !== FALSE, 'Could not delete item.');
		
		unset($ufs);
		
		$postContentRead = file_get_contents($filename);
		$this->assertEquals(json_decode($postContent, TRUE), json_decode($postContentRead, TRUE), 'Deleting item does not behave as expected.');
	}
	
	public function testUfsDataAccessMove() {
		$filename = __DIR__ . '/userfs_worksetXX.tmp';

		if(file_exists($filename)) {
			unlink($filename);
		}
		
		$preContent = '{"x":{"type":"DIR","a":{"name":"a","type":"URL","id":"http://xxx"},"b":{"name":"b","type":"CACHE","owner":"c","link":"http://yyy"}}}';
		$postContent = '{"x":{"type":"DIR","b":{"name":"b","type":"CACHE","owner":"c","link":"http://yyy"}}, "a":{"name":"a","type":"URL","id":"http://xxx"}}';
		$this->assertTrue(file_put_contents($filename, $preContent) !== FALSE, 'Could not write contents.');
		
		$ufs = UserFileSystem::fromFile('test-test-com', $filename);
		
		$this->assertTrue($ufs->doesItemExist("x///a") !== FALSE, '"x/a" could not be found.');
		$this->assertTrue($ufs->doesItemExist("///") !== FALSE, 'ROOT could not be found.');
		$this->assertTrue($ufs->moveItem("x///a", "///") !== FALSE, 'Could not move item.');
		
		unset($ufs);
		
		$postContentRead = file_get_contents($filename);
		$this->assertEquals(json_decode($postContent, TRUE), json_decode($postContentRead, TRUE), 'Moving item does not behave as expected.');
	}
	
	public function testUfsSetHierarchy() {
		$filename = __DIR__ . '/userfs_worksetXX.tmp';

		if(file_exists($filename)) {
			unlink($filename);
		}
		
		$contentValid = '{"x":{"type":"DIR","a":{"name":"a","type":"URL","id":"http://xxx"},"b":{"name":"b","type":"CACHE","owner":"c","link":"http://yyy"}}}';
		$contentInvalid = '{"x:{"type":"DIR","b":"name":"b","type":"CACHE","owner":"c","link":"http://yyy"}}, "a":{"name":"a","type":"URL","id":"http://xxx"}}';
		$this->assertTrue(file_put_contents($filename, $contentValid) !== FALSE, 'Could not write contents.');
		
		$ufs = UserFileSystem::fromFile('test-test-com', $filename);
		
		$this->assertTrue($ufs->setHierarchy($contentValid) !== FALSE, 'Could not write Valid JSON!');
		$this->assertTrue($ufs->setHierarchy($contentInvalid) !== TRUE, 'Could write Invalid JSON!');

		unset($ufs);
	}
}

?>
