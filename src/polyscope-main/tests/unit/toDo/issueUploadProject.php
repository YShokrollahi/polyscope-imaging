<?php
/*
	Desc: Functions to issue the upload of a project.
	Author:	Sebastian Schmittner
	Date: 2014.08.14
	Last Author: Sebastian Schmittner
	Last Date: 2015.01.31 09:20:19 (+01:00)
	Version: 0.1.2
*/

require_once 'logging.php';
require_once 'md5chk.php';
require_once 'lockedFileAccess.php';
require_once 'addLineToFile.php';
require_once 'guid.php';
require_once 'fileFormats.php';
require_once 'tools.php';

$isDir = json_decode($_POST["isDir"]);
$pathToIssue = json_decode($_POST["path"]);

if($isDir == 1) {
	if(is_array($pathToIssue)) {
		echo json_encode('NOT YET IMPLEMENTED');
	}
	else {
		echo json_encode( issueOnDir( $pathToIssue ) );
	}
}
else {
	if(is_array($pathToIssue)) {
		echo json_encode( issueFiles( $pathToIssue ) );
	}
	else {
		echo json_encode( issueFile( $pathToIssue ) );
	}
}

//////////////////////////////////////////////////////////////////

function issueOnDir( $path ) {

	return array( "name" => "", 
			  "md5" => "",
			  "guid" => "",
			  "id" => "",
			  "fullStatus" => "");
}

function issueFile( $path ) {
	
	$uploadFolder = uploadFolder();
	$filename = basename($path);
	$uploadFile =  $uploadFolder . uniqueId() . "_" . $filename;
	$md5OfFile = md5chk( $path );
	
	$success = addJob( $path, $uploadFile );
	
	return array( "typ" => "single",
				  "name" => $uploadFile, 
			      "md5" => $md5OfFile,
				  "guid" => $success['projectGuid'],
				  "id" => $success['projectId'],
				  "fullStatus" => $success);
}

function issueFiles( $paths ) {
	
	$max = sizeof($paths);
	
	$rootPath = rootPath();
	$timeout = 30;
	$firstTime = time();
	$uploadFolder = uploadFolder();

	$jobList = "";
	$jobs = array();
	
	for($i = 0; $i < $max; ++$i) {
		$filename = basename($paths[$i]);
		$uploadFile = $uploadFolder . uniqueId() . "_" . $filename;

		$jobEntry = createJobEntry( $paths[$i], $uploadFile );
		$guid = $jobEntry['guid'];
		
		addLineToFile(jobFileG($guid), $jobEntry['entry']);
		$jobList = $jobList . $guid . "\n";
		
		$jobEntry["name"] = $uploadFile;
		$jobEntry["fileId"] = $paths[$i];
		array_push($jobs, $jobEntry);
	}
	
	$jobFile = jobFile();
	$success = addLineToFile($jobFile, $jobList);
	
	while( $success['id'] != 0 && (time() - $firstTime) < $timeout ) {
		$success = addLineToFile($jobFile, $jobList);
	}
	
	return array( "typ" => "multiple",
				  "jobs" => $jobs,
				  "fullStatus" => $success);
}

/////////////////////////////////////////////////////////////////////

function createJobEntry( $fileFrom, $fileTo ) {
	
	$jobCounterFile = jobCounter();
	$counter = atomicCounterIncrement( $jobCounterFile );
	$guid = GUID();
	
	$projectEntry = $counter . ";" . $guid . 
					";1;checksum;" . 
					$fileFrom . ";" . $fileTo . ";MD5CHECKSUM;EMAIL_PLACE_HOLDER;FINAL_FILENAME_PLACEHOLDER;FINAL_PATH_PLACEHOLDER                                                                                                                                                                                                                                                                                                                                                                       ";
	
	return array( "entry" => $projectEntry,
				  "id" => $counter,
				  "guid" => $guid);
}

// adds a new job to the job list
// it uses also a timeout in case that the atomic write does not 
// succeed
function addJob( $fileFrom, $fileTo ) {
	
	$timeout = 30;
	$firstTime = time();
	
	$project = createJobEntry($fileFrom, $fileTo);
	
	$guid = $project['guid'];
	$success = addLineToFile(jobFileG($guid), $project['entry']);
	
	$jobFile = jobFile();
	$success = addLineToFile($jobFile, $project['guid']);
	
	while( $success['id'] != 0 && (time() - $firstTime) < $timeout ) {
		$success = addLineToFile($jobFile, $project['guid']);
	}
	
	return array( "success" => $success, 
			      "projectGuid" => $project['guid'],
				  "projectId" => $project['id'] );
}

?>
