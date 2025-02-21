<?php
/*
	Desc: Generic starter interface for the jobs
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.08.11 15:45
	Last Author: Sebastian Schmittner
	Last Date: 2015.08.11 15:45
	Version: 0.0.1
	
*/

// usage: php startJob.php taskName.php "base64_encode(json_array)"
//            $0           $1            $2
//
// json_array:
//		'guid' => GUID
//

require_once 'logging.php';
require_once 'polyzoomerGlobals.php';

chdir(dirname(__FILE__));

$neededArguments = 3;

doLog('[DEBUG] parameters: [' . json_encode($argv) . ']', logfile());

if( $argc < $neededArguments ) {
	doLog('[ERROR] startJob: Too few parameters [' . $argc . '/' . $neededArguments . ' : ' . json_encode($argv) . ']');
	return;
}

$taskName = $argv[1];
$parameters = $argv[2];

startJob( $taskName, $parameters );

return;

///////////////////////////////////////////////////////////////////////////////

function startJob( $taskName, $parameters ) {
	
	$phpFile = jobSubmission();
	
	$command = 'php ' . $phpFile . ' ' . $taskName . ' ' . $parameters;
	executeAsync($command);
}
			
?>
