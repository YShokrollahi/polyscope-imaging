<?php
/*
	Desc: Performs the analysis
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.05.25 09:05:06 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.29 12:04:53 (+02:00)
	Version: 0.0.7
	
*/

require_once 'logging.php';
require_once 'transferFiles.php';
require_once 'jobRepresentation.php';
require_once 'taskFileManipulator.php';

// usage: php doAnalysis.php "base64_encode(json_array)"
//            $0              $1   
//
// json_array:
//		'guid' => GUID
//

chdir(dirname(__FILE__));

$neededArguments = 2;

doLog('[DEBUG] parameters: [' . json_encode($argv) . ']', logfile());

if( $argc < $neededArguments ) {
	doLog('[ERROR] doAnalysis: Too few parameters [' . $argc . '/' . $neededArguments . ' : ' . json_encode($argv) . ']');
	return;
}

$parameters = json_decode( base64_decode( $argv[1] ), true );
doLog('[DEBUG] parameters: [' . json_encode($parameters) . ']', logfile());

doAnalysis( $parameters );

return;

///////////////////////////////////////////////////////////////////////////////

function doAnalysis( $parameters ) {
	
	$guid = $parameters;

	$jobFile = analysisJobFile( $guid );
	$jobLog = analysisJobLog( $guid );
	
	$job = new Job();
	$job->withText( file_get_contents( $jobFile ) );

	$codePath = codePath( $guid );
	$dataPath = dataPath( $guid ) . $job->data['sampleName'] . '/';
	$resultPath = resultPath( $guid ) . $job->data['sampleName'] . '/';
	
	$app = $job->data['appName'];
	$modulePath = modulePath( $app );
	$moduleCodePath = moduleCodePath( $app );

	try {
		$command = 'ln -s ' . codePath( $guid ) . 'nohup.out ' . analysisOut( $guid );
		executeSync( $command );
	}
	catch (Exception $e) {
		var_dump($e);
		jobLog2($guid, '[FATAL] App log file could not be linked! [' . json_encode($e) . ']');
	}
	
	tryUpdateJobEntry( $guid, '3;toAnalyze', '3;analysing' );
	
	$command = 'cp -R ' . enclose( $moduleCodePath ) . '* ' . enclose( $codePath );
	jobLog2( $guid, '[INFO] Copying Application codes (' . $app . ') [' . $command . ']');
	executeSync( $command );

	$command = 'cp ' . enclose( $modulePath . 'startApp.sh' ) . ' ' . enclose( $codePath );
	jobLog2( $guid, '[INFO] Copying start script (' . $app . ') [' . $command . ']');
	executeSync( $command );
	
	$command = 'cd ' . enclose( $codePath ) . ' && ./startApp.sh';
	jobLog2( $guid, '[INFO] Starting (' . $app . ') [' . $command . ']');
	executeSync( $command );

	$rawPath = rawPath( $guid );
	
	//$files = dirToArray( $rawPath, DTA_FILES );
	
	$transferSpecs = array(
		'guid' => $guid,
		'to' => resultPath( $guid ) . $job->data['sampleName'] . '/',
		'endState' => '3;toMerge',
		'path' => $rawPath );
		//'files' => $files );
	
	$command = 'php doTransferFiles.php ' . enclose( base64_encode( json_encode( $transferSpecs ) ) );
	jobLog2( $guid, '[INFO] Copying original images to fill gaps (' . $app . ') [' . $command . ']');
	executeSync( $command );

	// copy the classified images
	$command = 'cp ' . enclose( $resultPath . 'classifiedImage/section_1/' ) . '* ' . enclose( $resultPath );
	jobLog2( $guid, '[INFO] Copying classified images (' . $app . ') [' . $command . ']');
	executeAsync( $command );
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
