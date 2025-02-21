<?php
/*
	Desc: Transfers a set of files
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.05.25 09:05:25 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.30 17:47:50 (+02:00)
	Version: 0.0.2
	
*/

require_once 'polyzoomerGlobals.php';
require_once 'tools.php';

function transferFiles( $guid, $to, $files ) {
	
	$errorOccured = false;
	
	if( is_array( $files ) ) {
		// copy
		for( $i = 0; $i < count( $files ); ++$i ) {
			$errorOccured = $errorOccured || doCopy( $guid, $to, $files[$i] );
		}
	}
	else {
		$errorOccured = $errorOccured || doCopy( $guid, $to, $files );
	}

	return $errorOccured;
}

function doCopy( $guid, $to, $file ) {
	
	if( $file == '/' ) {
		doLog('[FATAL] It was tried to copy the root!', logfile());
		return true;
	}
	
	$errorOccured = false;
	
	$currentFile = $file;
	
	if( file_exists($currentFile) ) {
		
		$command = 'cp -R ' . enclose( $currentFile ) . ' ' . enclose( $to );
		
		jobLog2( $guid, '[INFO] Copy file [' . $command . ']');
		
		execute( $command );
	 }
	else {
		jobLog2( $guid, '[ERROR] File [' . $currentFile . '] does not exist!');
		$errorOccured = true;
	}
	
	return $errorOccured;
}

?>
