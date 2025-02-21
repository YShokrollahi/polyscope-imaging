<?php
/*
	Desc: Polyzoomer Globals
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.01.30 15:05:01 (+01:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.27 11:33:48 (+02:00)
	Version: 0.1.0
*/

function rootPath() {
	return '/Users/polyzoomer/polyzoomer/';
}

function logFolder() {
	return '/var/log/';
}

function tempFolder() {
	return '/tmp/';
}

function tempPrefix() {
	return 'pzTemp_';
}


function logFile() {
	return rootPath() . 'polyzoomer.log';
}

function userKeySize() {
	return 6;
}

function userKeySet() {
	return 'A-Z0-9';
}

function polyzoomerEmail() {
	return 'polyzoomer@icr.ac.uk';
}

function analysisFolder() {
	return rootPath() . 'analyses/';
}

function jobFolder() {
	return analysisFolder() . 'analysis_jobs/';
}

function jobFile() {
	return analysisJobMasterFile();
}

function jobDoneFile() {
	return analysisJobMasterFile() . '.done'; 
}

function jobFileG( $guid ) {
	return analysisJobFile( $guid );
} 

function analysisInPath() {
	return analysisFolder() . 'analysis_in/';
}

function analysisIn( $guid ) {
	return analysisInPath() . $guid . '/';
}

function analysisOutPath() {
	return analysisFolder() . 'analysis_out/';
}

function analysisOut( $guid ) {
	return analysisOutPath() . $guid . '/';
}

function analysisJobsPath() {
	return analysisFolder() . 'analysis_jobs/';
}
function analysisJobFile( $guid ) {
	return analysisJobsPath() . $guid . '.job';
}

function analysisJobLog( $guid ) {
	return analysisJobsPath() . $guid . '.log';
}

function analysisJobMasterFile() {
	return analysisJobsPath() . 'jobs.log';
}

function jobContainerPath() {
	return rootPath() . 'jobcontainers/';
}

function jobContainer( $guid ) {
	return jobContainerPath() . $guid . '/';
}

function rawPath( $guid ) {
	return jobContainer( $guid ) . 'raw/';
}

function dataPath( $guid ) {
	return jobContainer( $guid ) . 'data/cws/';
}

function resultPath( $guid ) {
	return jobContainer( $guid ) . 'result/';
}

function codePath( $guid ) {
	return jobContainer( $guid ). 'code/';
}

function modulesPath() {
	return rootPath() . 'modules/';
}

function modulePath( $name ) {
	return modulesPath() . $name . '/';
}

function moduleCodePath( $name ) {
	return modulePath( $name ) . 'code/';
}

function maximumLogfileSizeInBytes() {
	return 10 * 1024 * 1024; // 10MB
} 

?>
