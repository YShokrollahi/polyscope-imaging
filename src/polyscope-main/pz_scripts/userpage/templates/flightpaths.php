<?php

/*
 Desc:		flightpaths
			This function handles all interactions with the main flightpath file
 
	Author:	Sebastian Schmittner
	Date: 2014.07.23
	Last Author: Sebastian Schmittner
	Last Date: 2014.12.04 14:28:09 (+01:00)
	Version: 0.0.5
 
*/

require_once 'guid.php';
require_once 'lockedFileAccess.php';
require_once 'addLineToFile.php';

/* 	intents
	
	*master
		load - loads all flightpaths
		add - adds a flightpath
		delete - deletes a flightpath
		rename - rename a flightpath
	*flightpath
		load - loads the specified flightpath
		add - adds a view
		delete - deletes a view
		
	targets
		master - master file
		flightpath - specific flightpath file

	combinations
		target		intent	data*
		
		master		load	path
		master 		add		name
		master		delete	key
		master 		rename	key	name
		
		flightpath	load	key
		flightpath	add		key	position	[data]
		flightpath	delete	key position
*/

$delimiter = ';';
$active = '1';
$inactive = '0';

$intent = json_decode($_POST['intent']);
$target = json_decode($_POST['target']);
$flightPathFile = json_decode($_POST['path']);

//$prefix = getPrefix();
$flightPathFile = urlToPath($flightPathFile);
//$flightPathFile = $prefix . $flightPathFile;
$pathToFiles = pathinfo( $flightPathFile );
$pathToFiles = $pathToFiles['dirname'];

if(!file_exists($flightPathFile)) {
	error_log("[" . $flightPathFile .  "] file does not exist!");
	$handle = fopen($flightPathFile, 'w');
	fclose($handle);
}

$result = "";

if($target == 'master') {
	$result = handleMasterCommands($intent, $flightPathFile);
}
elseif ($target == 'flightpath') {
	$result = handleFlightpathCommands( $intent );
}

echo json_encode($result);

return;
//////////////////////////////////////////////////////////
function handleMasterCommands($intent, $flightpath) {

	$result = "";
	
	if($intent == 'load') {
		$result = loadFlightPaths( $flightpath, true );
	}
	elseif ($intent == 'add') {
		$name = json_decode($_POST['name']);
		$result = addFlightPath( $flightpath, $name );
	}
	elseif ($intent == 'delete') {
		$key = json_decode($_POST['key']);
		$result = deleteFlightPath( $flightpath, $key );
	}
	elseif ($intent == 'rename') {
		$key = json_decode($_POST['key']);
		$name = json_decode($_POST['name']);
		$result = renameFlightPath( $flightpath, $key, $name );
	}
	else {
		errorMessage("Unknown command! [$intent]", 1);
	}
	
	return $result;
}
//////////////////////////////////////////////////////////
function handleFlightpathCommands($intent) {

	$result = "";
	
	if($intent == 'load') {
		$key = json_decode($_POST['key']);
		$result = loadSpecificFlightPath( $key );
	} 
	elseif($intent == 'add') {
		$key = json_decode($_POST['key']);
		$pos = json_decode($_POST['position']);
		$data = json_decode($_POST['data']);
		$result = addView( $key, $pos, $data );
	}
	elseif($intent == 'delete') {
		$key = json_decode($_POST['key']);
		$pos = json_decode($_POST['position']);
		$result = removeView( $key, $pos );
	}
	else{
		errorMessage("Unknown command! [$intent]", 1);
	}
	
	return $result;
}
//////////////////////////////////////////////////////////
function loadFlightPathFile( $key ) {

	$result = createReturnValue(0, 'ok', '');

	$filename = fullFlightPathFileName( $key );
	
	if(filesize($filename) == 0) {
		$result['data'] = array();
		return $result;
	}
	
	$result = lockedFileRead( $filename, filesize($filename), 'r' );
	
	$contents = array();
	
	if( $result['id'] == 0 ) {
		$contents = $result['data'];
		$contents = explode("\n", $contents);
	}
	
	$result['data'] = $contents;

	return $result;
}
	
function fullFlightPathFileName( $key ) {
	global $pathToFiles;
	
	$filename = fileNameFromGuid( $key );
	return $pathToFiles . '/' . $filename;
}

////////////////////////////////////////////////

function loadSpecificFlightPath( $key ) {
	
	return loadFlightPathFile( $key );
}

function addView( $key, $pos, $data ) {
	global $delimiter;
	global $active;
	global $inactive;
	
	$result = createReturnValue(0, 'ok', '');
	
	$filename = fullFlightPathFileName( $key );
	$currentMd5 = md5_file( $filename );

	//$contents = loadFlightPathFile( $key );
	
	if( $currentMd5 == md5_file( $filename ) ) {

		//if( $contents != null ) {
			
			//$buffer = $contents['data'];
			
			//array_splice($buffer, $pos, 0, $data);

			// for( $i = 0; $i < count($contents); ++$i ) {
			
				// $values = explode( $delimiter, $contents[$i] );
				
				// if( $values[1] >= $pos ) {
					// $data = "";
					// for( $j = 2; $j < count($values); ++$j ){
						// $data = $values[j] . $delimiter;
					// }
					// $contents[$i] = createEntry( $active, $values[1] + 1, $data );
				// }
			// }
			
			// array_push( $contents, createEntry( $active, $pos, $data ) );
			
			//$contents = implode( "\n", $buffer );
			
			$data = str_replace("\0", "", $data);
			
			$writeResult = lockedFileWrite( $filename, $data, $currentMd5);
			
			if( $writeResult['id'] != 0 ) {
				if( $writeResult['id'] == 1 ) {
					$result = createReturnValue(2, 'error', 'The file could not be locked!');
				}
				
				if( $writeResult['id'] == 2 ){
					$result = createReturnValue(3, 'error', 'The MD5 changed in the meantime!');
				}
			}
		//}
	}
	else {
		$result = createReturnValue(1, 'error', 'The MD5 of the flightpath file changed while loading!');
	}
		
	return $result;
}

function removeView( $key, $pos ) {
	
	global $delimiter;
	global $active;
	global $inactive;

	$result = createReturnValue(0, 'ok', '');
	
	$filename = fullFlightPathFileName( $key );
	$currentMd5 = md5_file( $filename );

	$contents = loadFlightPathFile( $key );
	
	if( $currentMd5 == md5_file( $filename ) ) {

		if( $contents != null ) {
			
			for( $i = 0; $i < count($contents); ++$i ) {
			
				$values = explode( $delimiter, $contents[$i] );
				
				if( $values[1] == $pos ) {

					$data = "";
					for( $j = 2; $j < count($values); ++$j ){
						$data = $values[j] . $delimiter;
					}

					$contents[$i] = createEntry( $inactive, $values[1], $data );
				}
			}
			
			$contents = implode( "\n", $contents );
			$writeResult = lockedFileWrite( $filename, $contents, $currentMd5);
			
			if( $writeResult['id'] != 0 ) {
				if( $writeResult['id'] == 1 ) {
					$result = createReturnValue(2, 'error', 'The file could not be locked!');
				}
				
				if( $writeResult['id'] == 2 ){
					$result = createReturnValue(3, 'error', 'The MD5 changed in the meantime!');
				}
			}
		}
	}
	else {
		$result = createReturnValue(1, 'error', 'The MD5 of the flightpath file changed while loading!');
	}
		
	return $result;
}
//////////////////////////////////////////////////////////
function fileNameFromGuid( $guid ) {
	return 'flightpath.' . $guid . '.csv';
}

function createFile( $path ) {
	$handle = fopen($path, 'w') or die("cannot open file");
	fclose($handle);
}

//////////////////////////////////////////////////////////
function loadFlightPaths( $flightPathFile, $doExplode ) {
	
	if(filesize($flightPathFile) == 0) {
		return array();
	}
	
	$result = lockedFileRead( $flightPathFile, filesize($flightPathFile), 'r' );
	
	$contents = array();
	
	if( $result['id'] == 1 ) {
		createFile( $flightPathFile );
	}
	else {
		$contents = $result['data'];
		
		if($doExplode) {
			$contents = explode("\n", $contents);
		}
	}
	
	$result['data'] = $contents;
	return $result;
}

function addFlightPath( $flightPathFile, $name ) {

	$returnValue = createReturnValue(0, 'ok', '');

	global $delimiter;
	global $active;
	global $inactive;

	$guid = GUID();
	$entry = createEntry($active, $guid, sanitize( $name ));

	$path_parts = pathinfo( $flightPathFile );
	$path = $path_parts['dirname'];
	$newFileName = $path . '/' . fileNameFromGuid( $guid );
	createFile( $newFileName );
	
	if( !addLineToFile( $flightPathFile, $entry ) ) {
		$returnValue = createReturnValue(1, 'error', 'File could not be written!');
	}

	return $returnValue;
}

function deleteFlightPath( $flightPathFile, $key ) {
	
	$returnValue = createReturnValue(0, 'ok', '');

	global $delimiter;
	global $active;
	global $inactive;

	$updateSuccessful = false;
	
	$buffer['a'] = "";
	
	while ( !$updateSuccessful ) {

		$currentMd5 = md5_file( $flightPathFile );
		$contents = loadFlightPaths( $flightPathFile, true );
		$buffer['read'] = $contents;

		$contents = $contents['data'];
		
		$buffer['contents'] = $contents;
		$assoc = array();
		
		$deleted = false;
		
		for($i = 0; $i < count($contents); ++$i) {
			
			$values = explode( $delimiter, $contents[$i] );
			array_push($assoc, $values);
			
			if(count($values) >= 3) {
			
				array_push($assoc, $values[1] == $key);
				array_push($assoc, $values[1]);
				array_push($assoc, $key);
				array_push($assoc, $values[0] == $active);
				array_push($assoc, $values[0]);
				array_push($assoc, $active);
				
				if($values[1] == $key && $values[0] == $active) {
					$contents[$i] = createEntry($inactive, $values[1], $values[2]);
					$deleted = true;
				}
			}
		}

		if ( $deleted == true ) {
			$contents = implode( "\n", $contents );
			$result = lockedFileWrite($flightPathFile, $contents, $currentMd5 );
			$updateSuccessful = ($result['id'] == 0);
		}
		else {
			$updateSuccessful = true;
		}
	}
	
	if( !$updateSuccessful ) {
		$returnValue = createReturnValue(1, 'error', 'Entry could not be deleted!');
	}

	$returnValue['buffer'] = $buffer;
	$returnValue['assoc'] = $assoc;
	
	return $returnValue;
}

function renameFlightPath( $flightPathFile, $key, $name ) {

	$returnValue = createReturnValue(0, 'ok', '');

	global $delimiter;
	global $active;
	global $inactive;

	$updateSuccessful = false;
	
	while ( !$updateSuccessful ) {

		$currentMd5 = md5_file( $flightPathFile );
		$contents = loadFlightPaths( $flightPathFile, true );
		$contents = $contents['data'];
		$updated = false;
		
		for($i = 0; $i < count($contents); ++$i) {
			
			$values = explode( $delimiter, $contents[$i] );
			
			if(count($values) >= 3) {
			
				if($values[1] == $key && $values[0] == $active) {
					$contents[$i] = createEntry($values[0], $values[1], $name);
					$updated = true;
				}
			}
		}

		if ( $updated == true ) {
			$contents = implode( "\n", $contents );
			$result = lockedFileWrite($flightPathFile, $contents, $currentMd5 );
			$updateSuccessful = ($result['id'] == 0);
		}
		else {
			$updateSuccessful = true;
		}
	}

	if( !$updateSuccessful ) {
		$returnValue = createReturnValue(1, 'error', 'Entry could not be updated!');
	}

	return $returnValue;
}
//////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////
function createEntry($active, $v1, $v2) {
	
	global $delimiter;

	return $active . $delimiter . $v1 . $delimiter . $v2 . $delimiter;
}

function sanitize($text) {

	return str_replace("[`~!@#$%^&*()|+\=?;:'\",.<>\{\}\[\]\\\/", "_", $text);
}

//////////////////////////////////////////////////////////

// generates a proper HTML error (HTTP != 200)
// from: (http://stackoverflow.com/questions/4417690/return-errors-from-php-run-via-ajax)
function errorMessage($errorMsg, $errorCode)
{
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('message' => $errorMsg, 'code' => $errorCode)));
}

function getPrefix()
{
	$file = fopen( "./indexes", "r" );
	$index = fgets($file);
	fclose( $file );
	
	$parts = pathinfo($index);
	
	return $parts['dirname'] . '/';
}

function urlToPath($url) {
	
	$path = parse_url($url, PHP_URL_PATH);
	$path = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;
	return $path;
}

?>
