<?php
/*
	Desc: Functions to issue the upload of a project.
	Author:	Sebastian Schmittner
	Date: 2014.08.14
	Last Author: Sebastian Schmittner
	Last Date: 2015.05.30 17:47:12 (+02:00)
	Version: 0.1.3
*/

require_once __DIR__ . '/issueProject.php';

$isDir = json_decode($_POST["isDir"]);
$pathToIssue = json_decode($_POST["path"]);

if($isDir == 1) {
	if(is_array($pathToIssue)) {
		echo json_encode('NOT YET IMPLEMENTED');
	}
	else {
		echo json_encode( issueOnDir( $pathToIssue ) );
	}
}
else {
	if(is_array($pathToIssue)) {
		echo json_encode( issueFiles( $pathToIssue ) );
	}
	else {
		echo json_encode( issueFile( $pathToIssue ) );
	}
}

?>
