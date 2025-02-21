<?php
/*
	Desc: Polyzoomer Globals
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.01.30 15:05:01 (+01:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.04.28 22:10:36 (+02:00)
	Version: 0.0.5
*/

function rootPath() {
	return '/var/www/';
}

function logFolder() {
	return '/var/log/';
}

function uploadFolder() {
	return rootPath() . 'uploads/';
}

function jobFolder() {
	return rootPath() . 'jobs/';
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

function userKeySize() {
	return 6;
}

function userKeySet() {
	return 'A-Z0-9';
}

function polyzoomerEmail() {
	return 'polyzoomer@icr.ac.uk';
}

?>
