<?php
/*
	Desc: Performs the final merge
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.05.30 17:46:22 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.06.04 09:37:19 (+02:00)
	Version: 0.0.2
	
*/

require_once 'logging.php';
require_once 'transferFiles.php';
require_once 'jobRepresentation.php';
require_once 'taskFileManipulator.php';

set_time_limit(3600);

// usage: php doMerge.php "base64_encode(json_array)"
//            $0            $1   
//
// json_array:
//		'guid' => GUID
//

chdir(dirname(__FILE__));

$neededArguments = 2;

doLog('[DEBUG] parameters: [' . json_encode($argv) . ']', logfile());

if( $argc < $neededArguments ) {
	doLog('[ERROR] doMerge: Too few parameters [' . $argc . '/' . $neededArguments . ' : ' . json_encode($argv) . ']');
	return;
}

$parameters = json_decode( base64_decode( $argv[1] ), true );
doLog('[DEBUG] parameters: [' . json_encode($parameters) . ']', logfile());

doMerge( $parameters );

return;

///////////////////////////////////////////////////////////////////////////////

function doMerge( $parameters ) {
	
	$guid = $parameters;
	$app = 'RowByRowTile';
	
	$jobFile = analysisJobFile( $guid );
	$jobLog = analysisJobLog( $guid );
	
	$job = new Job();
	$job->withText( file_get_contents( $jobFile ) );

	$resultPath = resultPath( $guid ) . $job->data['sampleName'] . '/';
	$modulePath = modulePath( $app );
	$moduleCodePath = moduleCodePath( $app );
	
	// copy app
	$command = 'cp -R ' . enclose( $moduleCodePath ) . '* ' . enclose( $resultPath );
	jobLog2( $guid, '[INFO] Copying RowByRowTile [' . $command . ']');
	executeSync( $command );
	
	tryUpdateJobEntry( $guid, '3;toMerge', '3;merging' );
	
	// execute app
	$command = 'cd ' . enclose( $resultPath ) . ' && ./RowByRowTile.sh FinalScan.ini';
	jobLog2( $guid, '[INFO] RowByRowTile [' . $command . ']');
	executeSync( $command );
	
	// copy results
	$outputFilename = enclose( analysisOut( $guid ) . $job->data['sampleName'] . '_' . $job->data['appName'] . '.png');
	$command = 'cp -n ' . enclose( $resultPath . 'Da_Tiled.png' ) . ' ' . $outputFilename;
	jobLog2( $guid, '[INFO] Writing result file [' . $outputFilename . ']');
	executeSync( $command );
	
	tryUpdateJobEntry( $guid, '3;merging', '4;toDownload' );
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
