<?php
/*
	Desc: Transfers a set of files
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.05.25 09:05:06 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.29 11:58:54 (+02:00)
	Version: 0.0.2
	
*/

require_once '../../logging.php';
require_once 'transferFiles.php';
require_once '../../jobRepresentationAnalysis.php';
require_once '../../taskFileManipulator.php';

// usage: php doTansferFiles.php "json_array"
//            $0                 $1   
//
// json_array:
//		'guid' => GUID,
//		'to'   => path to,
//      'preStartState' => state to be in to start copy,		(optional)
//		'startState' => state to set when starting to copy, 	(optional)
//		'endState' => state to set after completion,			(optional)
// 		'files' => array(file1 file2 ...)
//

$neededArguments = 2;

doLog('[DEBUG] parameters: [' . json_encode($argv) . ']', logfile());

if( $argc < $neededArguments ) {
	doLog('[ERROR] doTransferFiles: Too few parameters [' . $argc . '/' . $neededArguments . ' : ' . json_encode($argv) . ']');
	return;
}

$parameters = json_decode( base64_decode( $argv[1] ), true );
doLog('[DEBUG] parameters: [' . json_encode($parameters) . ']', logfile());

doTransferFiles( $parameters );

return;

///////////////////////////////////////////////////////////////////////////////

function doTransferFiles( $parameters ) {
	
	$guid = $parameters['guid'];
	$to = $parameters['to'];
	$files = $parameters['files'];
	
	$jobFile = analysisJobFile( $guid );
	$jobLog = analysisJobLog( $guid );
	
	$job = new AnalysisJob();
	$job->withText( file_get_contents( $jobFile ) );

	$currentStatus = $job->data['statusId'] . ';' . $job->data['status'];
	
	if( !isset( $parameters['preStartState']) || 
		$currentStatus == $parameters['preStartState'] ) {
		
		if( isset( $parameters['startState'] ) ) {
			if( tryUpdateJobEntry( $guid, $currentStatus, $parameters['startState'] ) ) {
				$currentStatus = $parameters['startState'];
			}
		}
		
		// real copy
		$errorOccured = transferFiles( $guid, $to, $files );
		
		if( !$errorOccured ) {
			
			if( isset( $parameters['endState'] ) ) {
				tryUpdateJobEntry( $guid, $currentStatus, $parameters['endState'] );
			}
		}
		else {
			jobLog2( $guid, '[ERROR] Error while copying the files! [' . json_encode( $parameters ) . ']');
		}
	}
	else {
		jobLog2( $guid, '[FATAL] Tried to transfer already transferred sample! [' . json_encode( $job ) . ']');
	}
}

function updateJobEntry($guid, $currentStatus, $newStatus) {
	$jobFile = analysisJobFile($guid);
	
	$taskFile = new taskFileManipulator($jobFile);
	
	$pattern = ";$currentStatus;";
	$text    = ";$newStatus;";

	$result = $taskFile->doSafeRegexUpdate($pattern, $text, 3000);
	
	if( $result['id'] != 0 ) {
		jobLog2($guid, '[WARN] Could not lock the jobfile!');
		throw new LockFailedException();
	}

	jobLog2($guid, '[INFO] ' . $guid . " changes from [" . $currentStatus . "] to [" . $newStatus . "]");
}

function tryUpdateJobEntry( $guid, $statusFrom, $statusTo ) {

	$succeeded = true;
	
	try {
		updateJobEntry( $guid, $statusFrom, $statusTo );
	}
	catch ( Exception $e ) {
		jobLog2( $guid, '[FATAL] Update of job status failed! [' . $statusFrom . '] to [' . $statusTo . ']');
		jobLog2( $guid, '- [EXCEPTION] ' . json_encode( $e ));
		$succeeded = false;
	}
	
	return $succeeded;
}
			
?>
