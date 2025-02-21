<?php
/*
	Desc: Polyzoomer Globals
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.01.30 15:05:01 (+01:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.09.04 14:40:01 (+02:00)
	Version: 0.1.3
*/

function rootPath() {
	return '/var/www/';
}

function dataGrave() {
	return '/dev/mapper/zoom1-logical_zoom1';
}

function logFolder() {
	return '/var/www/';
}

function tempFolder() {
	return '/tmp/';
}

function tempPrefix() {
	return 'pzTemp_';
}

function uploadFolder() {
	return rootPath() . 'uploads/';
}

function jobFolder() {
	return rootPath() . 'jobs/';
}

function taskFolder() {
	return jobFolder() . 'tasks/';
}

function taskFile( $guid ) {
	return taskFolder() . $guid . '.task';
}

function jobCounter() {
	return rootPath() . 'jobCounter.log';
}

function logFile() {
	return logFolder() . 'polyzoomer.log';
}

function uploadLog() {
	return uploadFolder() . 'upload.log';
}

function jobFile() {
	return jobFolder() . 'jobs.log';
}

function jobDoneFile() {
	return jobFile() . '.done'; 
}

function jobFileG( $guid ) {
	return jobFolder() . $guid . '.job';
} 

function customersPath() {
		return rootPath() . 'customers/';
}

function userPath( $cleanEmail ) {
	return customersPath() . $cleanEmail . '/'; 
}

function cacheFile( $cleanEmail ) {
	return userPath( $cleanEmail ) . 'cache.lst';
}

function multiCacheFile( $cleanEmail ) {
	return userPath( $cleanEmail ) . 'multizooms/multi_cache.lst';
}

function userKeySize() {
	return 6;
}

function userKeySet() {
	return 'A-Z0-9';
}

function polyzoomerEmail() {
	return 'polyzoomer@mdanderson.org';
}

function analysisFolder() {
	return rootPath() . 'analyses/';
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

function maximumLogfileSizeInBytes() {
	return 10 * 1024 * 1024; // 10MB
} 

function testPath() {
	return rootPath() . 'tests/';
}

function testSamplePath() {
	return testPath() . 'sample/';
}

function testTempPath() {
	return testPath() . 'temp/';
}

function mediaDirectory() {
	return '/media/';
}

function mediaExcludes() {
	return array('.', '..');
}

function ufsUploadFolder() {
    return '///new///';
}

?>
