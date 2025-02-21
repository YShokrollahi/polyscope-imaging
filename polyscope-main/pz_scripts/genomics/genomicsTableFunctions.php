<?php
/*
	Desc: General Genomics Table functions
	Author:	Sebastian Schmittner
	Date: 2015.10.08 22:58:56 (+02:00)
	Last Author: Sebastian Schmittner (stp.schmittner@gmail.com)
	Last Date: 2015.10.12 15:10:05 (+02:00)
	Version: 0.0.9

	Specification File Layout (*.csv)
	MxN Matrix
	
	empty, 		file, 		header1, header2 ... *
	linename1, 	filekey1,	value1,  value2 ... *
	linename2, 	filekey2,	value1,  value2 ... *
	*
*/

require_once '../../tools.php';
require_once '../../lockedFileAccess.php';
require_once '../../polyzoomerGlobals.php';
require_once '../../md5chk.php';
require_once 'customerProjects.php';

function createGenomicsTableFromCsv($configFile){
	$separator = ',';
	$tableConfig = NULL;
	$FILENAME_COLUMN = 1;
	$TITLE_COLUMN = 0;
	
	$configContents = readCsvToMatrix($configFile);
	
	if($configContents !== FALSE) {
		$tableConfig = transformGenomicsTable($configContents, $FILENAME_COLUMN, $TITLE_COLUMN);
	}
	
	return $tableConfig;
}

function transformGenomicsTable($matrix, $filename_column, $title_column) {

	$table = NULL;
	
	if(count($matrix) > 0) {
		$header = extractHeader($matrix);
		
		$files = extractColumn($matrix, $filename_column);
		
		$header = cleanHeader($header);
		
		$titles = extractColumn($matrix, $title_column);
		
		$table = array(
						'header' => $header,
						'titles' => $titles,
						'files' => $files,
						'dataMatrix' => $matrix
						);
	}
	
	return $table;
}

function extractColumn(&$matrix, $columnIndex) {
	$columnData = selectColumnFromMatrix($matrix, $columnIndex);
	$matrix = removeColumnFromMatrix($matrix, $columnIndex);
	return $columnData;
}

function cleanHeader($arr) {
	array_shift($arr); // remove the empty first element
	array_shift($arr); // remove the filename header item
	return $arr;
}

function removeColumnFromMatrix($matrix, $columnIndex) {
	
	$matrixCount = count($matrix);
	
	if($matrixCount > 0) {
		if($columnIndex <= count($matrix[0]) &&
		   $columnIndex >= 0) {
			
			for($i = 0; $i < $matrixCount; ++$i) {
				array_splice($matrix[$i], $columnIndex, 1);
			}
		}
	}
	
	return $matrix;
}

function extractHeader(&$matrix) {
	$header = $matrix[0];
	array_splice($matrix, 0, 1);
	return $header;
}

function selectColumnFromMatrix($matrix, $columnIndex) {
	$selectedItems = array();
	
	if(count($matrix) > 0) {
		
		if($columnIndex < count($matrix[0])) {
			
			foreach($matrix as $currentLine) {
				array_push($selectedItems, $currentLine[$columnIndex]);
			}
		}
	}

	return $selectedItems;
}

function splitLinesIntoWords($data, $separator) {
	$lineSeparator = PHP_EOL;
	$matrix = array();
	
	$lines = explode($lineSeparator, $data);
	foreach($lines as $line) {
		if(!($line === NULL || 
		   $line === FALSE || 
		   empty($line) || 
		   $line === "")) {
			$words = explode($separator, $line);
			array_push($matrix, $words);
		}
	}
	
	return $matrix;
}

function gatherZooms($tableConfig, $email) {
	
	$projects = new CustomerProjects($email);
	$projects = $projects->toList(); 
	
	$zoomsToLookFor = $tableConfig["files"];
	$zoomTargets = array();

	for($i = 0; $i < count($zoomsToLookFor); ++$i) {
		$zoomFile = defaultZoom();
		
		$currentZoom = $zoomsToLookFor[$i];
		
		try {
			for($j = 0; $j < count($projects); ++$j) {
			
				$currentProject = $projects[$j];
			
				if(strpos(basename($currentProject->dzi[0]), $currentZoom) !== FALSE) {
					$zoomFile = extractZoom($currentProject);
					break 1;
				}
			}
		}
		catch (Exception $e) {
			$zoomFile = defaultZoom();
		}

		array_push($zoomTargets, $zoomFile);
	}
	
	$tableConfig['targets'] = $zoomTargets;
	
	return $tableConfig;
}

function extractZoom( $project ) {
	$zoom = defaultZoom();
	
	if($project !== NULL && $project !== FALSE) {
		$zoom['index'] = ($project->index);
		$zoom['thumbnail'] = $project->image[0];
		$zoom['dzi'] = $project->dzi[0];
	}
	
	return $zoom;
}

function defaultZoom() {
	$zoom = array(
		'index' => '',
		'thumbnail' => '',
		'dzi' => ''
		);

	return $zoom;
}

function readCsvToMatrix($filename) {

	$configContents = array();
	
	$csvFile = fopen($filename, 'r');
	while (($line = fgetcsv($csvFile)) !== FALSE) {
		array_push($configContents, $line);
	}
	fclose($csvFile);
	
	return $configContents;
}	
?>
