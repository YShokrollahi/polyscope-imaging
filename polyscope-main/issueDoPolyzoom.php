<?php
/*
	Desc: Prepare and start the polyzooming process.
	Author:	Sebastian Schmittner
	Date: 2014.08.24 22:56:00 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.23 22:37:41 (+02:00)
	Version: 0.1.6
*/

require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/lockedFileAccess.php';
require_once __DIR__ . '/taskFileManipulator.php';

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
		log_error("Counter seems to be invalid!");
	}
	
	$basePathName = "Path" . number_pad($counter, 6) . "_" . date('YmdHi');
	$pathName = $rootPath . "/polyzoomer/" . $basePathName;
	
	$dirCreated = mkdir( $pathName );
	
	if ( !$dirCreated ) {
		log_error("Directory could not be created! [" . $pathName . "]");
	}
	
	$success = rename($filePath, $pathName . "/" . $toFile);
	if ( !$success ) {
		log_error("Moving image file failed: " . $filePath . " -> " . $pathName . "/" . $toFile);		
	}

	$success = copy(rootPath() . "pz_scripts/polyzoomer/createPolyzoomerSite.sh", $pathName . "/createPolyzoomerSite.sh");
	if ( !$success ) {
		log_error("Copy of creationscript failed!");		
	}

	$success = copy(rootPath() . "pz_scripts/DssConverter/DigitalSlideStudio.sh", $pathName . "/DigitalSlideStudio.sh");
	if ( !$success ) {
		log_error("Copy of DigitalSlideStudio failed!");		
	}

	$success = copy(rootPath() . "pz_scripts/DssConverter/FinalScan_template", $pathName . "/FinalScan_template");
	if ( !$success ) {
		log_error("Copy of FinalScan_template failed!");		
	}

	$success = copy(rootPath() . "pz_scripts/doDeepzoom.sh", $pathName . "/doDeepzoom.sh");
	if ( !$success ) {
		log_error("Copy of doDeepzoom.sh failed!");		
	}

	$success = copy(rootPath() . "pz_scripts/doTiling.sh", $pathName . "/doTiling.sh");
	if ( !$success ) {
		log_error("Copy of doTiling.sh failed!");		
	}

	$success = copy(rootPath() . "pz_scripts/doPolyzoom.sh", $pathName . "/doPolyzoom.sh");
	if ( !$success ) {
		log_error("Copy of doPolyzoom.sh failed!");		
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
