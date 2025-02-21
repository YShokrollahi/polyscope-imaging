<?php
/*
	Desc: Prepare and start the polyzooming process.
	Author:	Sebastian Schmittner
	Date: 2014.08.24 22:56:00 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.01.31 09:20:08 (+01:00)
	Version: 0.1.5
*/

require_once 'logging.php';
require_once 'lockedFileAccess.php';
require_once 'taskFileManipulator.php';

class WrongArgumentCountException extends Exception {};

if($argc != 4) {
	throw new WrongArgumentCountException( array($argc, $argv) );
}

$path = $argv[1];
$guid = $argv[2];
$taskFileName = $argv[3];

set_time_limit(600);

try {
	doPolyzoom($path, $guid, $taskFileName);
}
catch (Exception $e) {
	print_r($e);
}

function doPolyzoom($path, $guid, $taskFileName) {
	
	$result = array();
	$jobFile = jobFileG($guid);
	$taskFile = new taskFileManipulator($jobFile);
	
	$pattern = ";$guid;4;inQueue;";
	$text    = ";$guid;5;processing;";
	$result = $taskFile->doSafeRegexUpdate($pattern, $text, 1000000);
	
	$path = preparePolyzoom($path);
	$output = polyzoom( $path["path"], $path["filename"] );
	$result = $taskFile->doSafeRegexUpdate(";FINAL_FILENAME_PLACEHOLDER;FINAL_PATH_PLACEHOLDER", ";" . $path['filename'] . ";" . $path['path'], 3000);
	
	$pattern = ";$guid;5;processing;";
	$text    = ";$guid;6;finished;";
	$result = $taskFile->doSafeRegexUpdate($pattern, $text, 1000000);

	return array(
		"path" => $path["path"],
		"output" => $output
	);
}

function polyzoom( $path, $filename ) {
	
	$rootPath = rootPath() . "polyzoomer/";

	$output = chdir($rootPath . $path . "/");
	
	// detox
	$executestring = "detox -n './$filename' > detox.log";
	log_error($executestring);
	$loutput = shell_exec($executestring);
	$output = $output . " - " . $loutput;
	
	$executestring = "cat detox.log";
	$realOutput = shell_exec($executestring);
	log_error($executestring);

	// detox returns nothing if there is no change done
	if(strlen($realOutput) != 0) {
		$realOutput = substr($realOutput, 0, -1);
		$executestring = "detox './$filename'";
		log_error($executestring);
		$loutput = shell_exec($executestring);
		$output = $output . " - " . $loutput;
		
		$detoxString = "./" . $filename . " -> ";
		$filenameLength = strlen($realOutput) - (strlen($detoxString) + 2);
		//$nameLength = strlen($filename) + 2;
		$filename = substr($realOutput, strlen($detoxString) + 2, $filenameLength);
		log_error($filename);
	}
	
	// add the pat_id and channel_id if missing
    $newfilename = testAndCorrectFilename($filename);
	$executestring = "mv -f './$filename' './$newfilename' >> ./process.log";
	log_error($executestring);
	$loutput = shell_exec($executestring);
	$output = $output . " - " . $loutput;
		
	// polyzoom
	$executestring = "chmod 777 ./doPolyzoom.sh";
	log_error($executestring);
	$loutput = shell_exec($executestring);
	$output = $output . " - " . $loutput;
	
	$executestring = "./doPolyzoom.sh \"" . $newfilename . "\" 2>&1 & echo $! >> ./polyzoom.pid";
	log_error($executestring);
	$loutput = shell_exec($executestring);
	$output = $output . " - " . $loutput;
	
	return $output . " - " . $rootPath . $path;
}

// check if there is the expected pattern for pat_id and channel_id
// otherwise add it
function testAndCorrectFilename( $filename ) {
	
	$pat_id = array();
	$channel_id = array();
	
	preg_match('/([a-zA-Z]+[0-9]+)/', $filename, $pat_id, PREG_OFFSET_CAPTURE);
	preg_match('/.*_([a-zA-Z]+[0-9]+)/', $filename, $channel_id, PREG_OFFSET_CAPTURE);

	if ( count($pat_id) == 0 && count($channel_id) == 0 ) {
		$filename = 'UNKNOWNPAT0001_UNKNOWNCHANNEL0001_' . $filename;
	}
	else if ( count($pat_id) == 0 ) {
		$filename = 'UNKNOWNPAT0001_' . $filename;
	}
	else if ( count($channel_id) == 0 ) {
		$filename = $pat_id[0][0] . '_UNKNOWNCHANNEL0001_' . $filename;
	}
	
	return $filename;
}

function preparePolyzoom($path) {
	
	$rootPath = rootPath();
	$counterFile = $rootPath . "/counter.log";
	
	$filePath = $path;
	$toFile = basename( $filePath );
	
	// remove the unique id while uploading from 'issueUploadProject.php'
	$uidSeparator = strpos($toFile, '_');
	$toFile = substr($toFile, $uidSeparator + 1);
	
	$counter = atomicCounterIncrement( $counterFile );
	
	if ( $counter == -1 ) {
		errorMessage("Counter seems to be invalid!", 1);
	}
	
	$basePathName = "Path" . number_pad($counter, 6) . "_" . date('YmdHi');
	$pathName = $rootPath . "/polyzoomer/" . $basePathName;
	
	$dirCreated = mkdir( $pathName );
	
	if ( !$dirCreated ) {
		errorMessage("Directory could not be created! [" . $pathName . "]", 2);
	}
	
	$success = copy($filePath, $pathName . "/" . $toFile);
	if ( !$success ) {
		errorMessage("Copy of file failed: " . $filePath, 3);		
	}
	
	$success = copy(rootPath() . "pz_scripts/polyzoomer/createPolyzoomerSite.sh", $pathName . "/createPolyzoomerSite.sh");
	if ( !$success ) {
		errorMessage("Copy of creationscript failed!", 4);		
	}

	$success = copy(rootPath() . "pz_scripts/DssConverter/DigitalSlideStudio.sh", $pathName . "/DigitalSlideStudio.sh");
	if ( !$success ) {
		errorMessage("Copy of DigitalSlideStudio failed!", 5);		
	}

	$success = copy(rootPath() . "pz_scripts/DssConverter/FinalScan_template", $pathName . "/FinalScan_template");
	if ( !$success ) {
		errorMessage("Copy of FinalScan_template failed!", 6);		
	}

	$success = copy(rootPath() . "pz_scripts/doDeepzoom.sh", $pathName . "/doDeepzoom.sh");
	if ( !$success ) {
		errorMessage("Copy of doDeepzoom.sh failed!", 7);		
	}

	$success = copy(rootPath() . "pz_scripts/doTiling.sh", $pathName . "/doTiling.sh");
	if ( !$success ) {
		errorMessage("Copy of doTiling.sh failed!", 7);		
	}

	$success = copy(rootPath() . "pz_scripts/doPolyzoom.sh", $pathName . "/doPolyzoom.sh");
	if ( !$success ) {
		errorMessage("Copy of doPolyzoom.sh failed!", 7);		
	}

	return array(
		"filename" => $toFile,
		"name" => $pathName,
		"path" => $basePathName,
	);
}

function number_pad($number,$n) {
	return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
}

function log_error($text) {
	shell_exec("echo " . $text . " >> process.log");
}

?>

