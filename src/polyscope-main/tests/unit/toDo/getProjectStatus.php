<?php
/*
	Desc: Functions to retrieve the current status of all open projects
	Author:	Sebastian Schmittner
	Date: 2014.09.07 20:37:27 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.01.31 09:20:02 (+01:00)
	Version: 0.0.4
*/

class FileContentChangedException extends Exception {};
class WrongArgumentCountException extends Exception {};

require_once 'logging.php';
require_once 'taskFileManipulator.php';
require_once 'jobRepresentation.php';

echo json_encode( retrieveProjectStatus() );

/////////////////////////////////

function retrieveProjectStatus() {

	$jobFile = jobFile();
	
	$taskFileHandler = null;
	$contents = null;
	
	try {
		$taskFileHandler = new taskFileManipulator($jobFile);
		$contents = $taskFileHandler->getContents();
	}
	catch (Exception $e) {
	}
		
	$valid = false;
	$jobs = array();
	$commentsLines = array();
	
	if($contents !== null) {
		$commentsLines = preg_grep("/^#/i", $contents);
		$jobLines = preg_grep("/^#/i", $contents, PREG_GREP_INVERT);
			
		foreach($jobLines as $entry) {
			
			$entry = trim($entry);
			if(empty($entry)) {
				continue;
			}
			
			$localJob = null;
			
			try {
				$file = jobFileG($entry);
				$content = lockedFileRead($file, filesize($file), 'r', true);
				$localJob = new Job($content['data']);
			}
			catch (Exception $e) {
				$localJob = null;
			}
			
			if(isset($localJob)) {
				array_push($jobs, $localJob);
			}
		}
		
		$valid = true;
	}
	
	return array(
		'valid' => $valid,
		'jobs' => $jobs,
		'comments' => $commentsLines
		);
};

?>
