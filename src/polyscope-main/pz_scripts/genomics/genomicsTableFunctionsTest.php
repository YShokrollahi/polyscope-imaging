<?php
/*
	Desc: General Genomics Table functions
	Author:	Sebastian Schmittner
	Date: 2015.10.08 22:58:56 (+02:00)
	Last Author: Sebastian Schmittner (stp.schmittner@gmail.com)
	Last Date: 2015.10.12 15:10:05 (+02:00)
	Version: 0.0.9
*/

require_once 'genomicsTableFunctions.php';

class GenomicsTableFunctionTest extends PHPUnit_Framework_TestCase {
	
	public function testSplitLineIntoWords() {
		
		$data = "Test,Line;1,A\nTest,Line;2,B\nTest,Line;3,C";
		$expected = array(
			array("Test,Line", "1,A"),
			array("Test,Line", "2,B"),
			array("Test,Line", "3,C")
		);
		
		$this->assertEquals($expected, splitLinesIntoWords($data, ';'));
		
		///////////////////////////////////////////////////////////////////////
		
		$data = "Test,Line;1,A\nTest,Line;2,B\nTest,Line;3,C\n";
		$expected = array(
			array("Test,Line", "1,A"),
			array("Test,Line", "2,B"),
			array("Test,Line", "3,C")
		);
		
		$this->assertEquals($expected, splitLinesIntoWords($data, ';'));
	}
	
	public function testDelectColumnFromMatrix() {
		$matrix = array(
			array("A", "B", "C"),
			array("1", "3", "5"),
			array("2", "4", "6")
		);
		
		$expectedOne = array("A", "1", "2");
		$expectedTwo = array("B", "3", "4");
		$expectedThree = array("C", "5", "6");
		
		$this->assertEquals($expectedOne, selectColumnFromMatrix($matrix, 0));
		$this->assertEquals($expectedTwo, selectColumnFromMatrix($matrix, 1));
		$this->assertEquals($expectedThree, selectColumnFromMatrix($matrix, 2));
	}
	
	public function testExtractHeader() {
		$matrix = array(
			array("A", "B", "C"),
			array("1", "3", "5"),
			array("2", "4", "6")
		);

		$header = array("A", "B", "C");
		$data = array(
			array("1", "3", "5"),
			array("2", "4", "6")
		);
		
		$result = extractHeader($matrix);
		
		$this->assertEquals($header, $result);
		$this->assertEquals($data, $matrix);
	}
	
	public function testRemoveColumnFromMatrix() {
		$matrix = array(
			array("A", "B", "C"),
			array("1", "3", "5"),
			array("2", "4", "6")
		);

		$expected = array(
			array("A", "C"),
			array("1", "5"),
			array("2", "6")
		);
		
		$this->assertEquals($expected, removeColumnFromMatrix($matrix, 1));
	}
	
	public function testExtractColumn() {
		$matrix = array(
			array("A", "B", "C"),
			array("1", "3", "5"),
			array("2", "4", "6")
		);

		$expectedMatrix = array(
			array("A", "C"),
			array("1", "5"),
			array("2", "6")
		);
		
		$expectedColumnData = array("B", "3", "4");
		
		$columnIndex = 1;

		$columnData = extractColumn($matrix, $columnIndex);
		
		$this->assertEquals($expectedColumnData, $columnData);
		$this->assertEquals($expectedMatrix, $matrix);
	}
	
	public function testTransformGenomicsTable() {
		$data = array(
					array('empty','file','header1','header2'),
					array('line1','file1','v1','v2'),
					array('line2','file2','v3','v4')
					);

		$header = array('header1', 'header2');
		$files = array('file1', 'file2');
		$titles = array('line1', 'line2');
		$matrix = array(
			array('v1', 'v2'),
			array('v3', 'v4')
		);
		
		$separator = ',';
		$filename_column = 1;
		$title_column = 0;
		
		$generatedConfig = transformGenomicsTable($data, $filename_column, $title_column);

		$this->assertEquals($header, $generatedConfig['header']);
		$this->assertEquals($files, $generatedConfig['files']);
		$this->assertEquals($titles, $generatedConfig['titles']);
		$this->assertEquals($matrix, $generatedConfig['dataMatrix']);
	}
	
	public function testExtractZoom() {
		$project = (object) array(
			'index' => 'http://www.google.com/index.html',
			'image' => array('http://www.google.com/thumbnail.png')
			);
			
		$expectedZoom = array(
			'index' => 'http://www.google.com/index.html',
			'thumbnail' => 'http://www.google.com/thumbnail.png'
			);
			
		$this->assertEquals($expectedZoom, extractZoom($project));
	}
	
	public function testReadCsvToMatrix() {
		$filename = 'test.csv';
		
		$configContents = array();
		
		$configContents = readCsvToMatrix($filename);
		
		$expected = array(
						array('test1', 'a  ','  b'),
						array('7634','   2324', '  234sef  ')
						);
		
		$this->assertEquals($expected, $configContents);
	}
}

?>
