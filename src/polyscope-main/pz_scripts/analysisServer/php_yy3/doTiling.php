<?php
/*
	Desc: Performs the initial tiling with 
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.05.25 09:05:06 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.15 21:31:39 (+02:00)
	Version: 0.0.4
	
*/

require_once 'logging.php';
require_once 'transferFiles.php';
require_once 'jobRepresentation.php';
require_once 'taskFileManipulator.php';

set_time_limit(3600);

chdir(dirname(__FILE__));

// usage: php doTiling.php "base64_encode(json_array)"
//            $0            $1   
//
// json_array:
//		'guid' => GUID,
//

$neededArguments = 2;

doLog('[DEBUG] parameters: [' . json_encode($argv) . ']', logfile());

if( $argc < $neededArguments ) {
	doLog('[ERROR] doTiling: Too few parameters [' . $argc . '/' . $neededArguments . ' : ' . json_encode($argv) . ']');
	return;
}

$parameters = json_decode( base64_decode( $argv[1] ), true );
doLog('[DEBUG] parameters: [' . json_encode($parameters) . ']', logfile());

doTiling( $parameters );

return;

///////////////////////////////////////////////////////////////////////////////

function doTiling( $parameters ) {
	
	$guid = $parameters;
	$app = 'DigitalSlideStudio';
	
	$jobFile = analysisJobFile( $guid );
	$jobLog = analysisJobLog( $guid );
	
	$job = new Job();
	$job->withText( file_get_contents( $jobFile ) );

	$rawPath = rawPath( $guid );
	$modulePath = modulePath( $app );
	$moduleCodePath = moduleCodePath( $app );
	
	$command = 'cp -R ' . enclose( $moduleCodePath ) . '* ' . enclose( $rawPath );
	jobLog2( $guid, '[INFO] Copying digital slide studio [' . $command . ']');
	executeSync( $command );
	
	tryUpdateJobEntry( $guid, '2;uploaded', '3;processing' );
	
	$command = 'cd ' . enclose( $rawPath ) . ' && ./DigitalSlideStudio.sh ' . enclose( $job->data['sampleName'] );
	jobLog2( $guid, '[INFO] DigitalSlideStudio [' . $command . ']');
	executeSync( $command );
	
	//$files = dirToArray( $rawPath, DTA_FILES );
	
	$transferSpecs = array(
		'guid' => $guid,
		'to' => dataPath( $guid ) . $job->data['sampleName'] . '/',
		'endState' => '3;toAnalyze',
		'path' => $rawPath );
		//'files' => $files );
	
	$command = 'php doTransferFiles.php ' . enclose( base64_encode( json_encode( $transferSpecs ) ) );
	jobLog2( $guid, '[INFO] Transfer generated images [' . $command . ']');
	executeSync( $command );
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
