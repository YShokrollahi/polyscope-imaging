<?php
/*
	Desc: Logging and debugging facilities.
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.01.30 15:04:51 (+01:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.04.20 22:59:46 (+02:00)
	Version: 0.0.4
*/

require_once 'lockedFileAccess.php';
require_once 'polyzoomerGlobals.php';

// global debug flag
function isDebugActive() {
	return true;
}

function doEcho( $toBeLogged ) {
	if( isDebugActive() ) {
		var_dump($toBeLogged);
	}
}

function execute($command) {
	
	doLog('[EXECUTE] ' . $command, logFile());

	if( isDebugActive() ) {
		$r1 = array();
		$r2 = array();
		
		exec($command, $r1, $r2);
		
		//doEcho($r1);
		//doEcho($r2);
	}
	else {
		$command = addLogToCommand($command);
		system($command);
	}
}

function executeInt($command) {
	
	doLog('[EXECUTE] ' . $command, logFile());

	if( isDebugActive() ) {
		$r1 = array();
		$r2 = array();
		
		exec($command, $r1, $r2);
		
		//doEcho($r1);
		//doEcho($r2);
	}
	else {
		system($command);
	}
}

function doLog( $message, $logfile ) {
	$message = getTime() . ' ' . $message . "\n";
	$returnValue = lockedFileAppend($logfile, $message);
}

function addLogToCommand($command) {
	return $command . ' >> ' . logFile() . ' & echo $!';
}

function jobLog( $guid, $message ) {
	$message = getTime() . ' ' . $message . "\n";
	lockedFileAppend(jobFolder() . "$guid.log", $message);
	doLog($message, logfile());
}

function getTime() {
	return date("[Y-m-d H:i:s]");
}

function logEvent( $text, $logFile, $filename, $md5sum ) {
	$logString = $md5sum . ";" . $text . ";'" . $filename . "';" . date('YmdHi') . ";";
	$logSuccess = addLineToFile($logFile, $logString);
	
	return $logSuccess;
}

?>
