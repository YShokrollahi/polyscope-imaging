<?php
/*
	Desc: Submits a job to a LSF cloud
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.08.11 19:08
	Last Author: Sebastian Schmittner
	Last Date: 2015.08.13 13:38:15 (+02:00)
	Version: 0.0.3
	
*/

require_once 'logging.php';
require_once 'transferFiles.php';
require_once 'jobRepresentation.php';
require_once 'taskFileManipulator.php';

// usage: php startJob_LSF.php taskName.php "base64_encode(json_array)"
//            $0               $1   		 $2
//
// json_array:
//		'guid' => GUID
//

chdir(dirname(__FILE__));

$neededArguments = 3;

doLog('[DEBUG] parameters: [' . json_encode($argv) . ']', logfile());

if( $argc < $neededArguments ) {
	doLog('[ERROR] startJob_LSF: Too few parameters [' . $argc . '/' . $neededArguments . ' : ' . json_encode($argv) . ']');
	return;
}

$parameters = $argv[2];
$taskName = $argv[1];
doLog('[DEBUG] parameters: [' . json_encode($parameters) . ']', logfile());

startJob_LSF( $taskName, $parameters );

return;

///////////////////////////////////////////////////////////////////////////////

function startJob_LSF( $taskName, $parameters ) {
	
	$guid = json_decode( base64_decode( $parameters ), true );

	$jobFile = analysisJobFile( $guid );
	$jobLog = analysisJobLog( $guid );
	
	$job = new Job();
	$job->withText( file_get_contents( $jobFile ) );

	$jobPath = jobContainer($guid);
	$bsubTemplate = phpFolder() . 'bsub/lsf_job_sample.bsub';
	$bsubFile = $jobPath . $taskName . '_' . $guid . '.bsub';
	
	$templateContents = file_get_contents( $bsubTemplate );
	
	$command = 'php ' . phpFolder() . $taskName . ' ' . $parameters;
	
	$templateContents = str_replace('APPNAME', $taskName, $templateContents);
	$templateContents = str_replace('FILENAME', $guid, $templateContents);
	$templateContents = str_replace('FULLPATH', $guid, $templateContents);
	$templateContents = str_replace('EMAIL', polyzoomerEmail(), $templateContents);
	$templateContents = str_replace('COMMAND', $command, $templateContents);
	
	file_put_contents($bsubFile, $templateContents);
	
	chdir($jobPath);
	$lsfCommand = "bsub < $bsubFile";
	
	executeAsync($lsfCommand);
}
			
?>
