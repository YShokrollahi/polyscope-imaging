<?php
/*
	Desc: Adds or returns all emails for autocompletion
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2014.12.07 20:42:58 (+01:00)
	Last Author: Sebastian Schmittner
	Last Date: 2014.12.21 12:45:22 (+01:00)
	Version: 0.0.2
*/

require_once 'md5chk.php';
require_once 'lockedFileAccess.php';

$intent = json_decode($_POST['intent']);

echo json_encode( handleIntent( $intent ) );

//////////////////////////////////////////////////////////////////
function handleIntent( $intent ) {

	if( $intent == 0 ) {
		return getAllEmails();
	}
	else if( $intent == 1 ){
		$email = json_decode($_POST['email']);
		
		return addOneEmail( $email );
	}
	else {
	}
}

function getAllEmails() {

	$data = "";

	try {
		$filename = "./autoEmails.lst";
		$md5 = md5chk( $filename );
		$content = lockedFileRead( $filename, filesize( $filename ), 'r');
		
		if( $content['id'] == 0 ) {
			$data = explode("\n", $content['data']);
		}
	}
	catch (Exception $e)
	{
	}

	return $data;
}

function addOneEmail( $email ) {
	
	$emails = getAllEmails();
	$email = strtolower($email);
	
	if( !doesOccur( $emails, $email ) ) {
		$email = $email . "\n";
		$filename = "./autoEmails.lst";
		$md5 = md5_file( $filename );
		lockedFileAppend( $filename, $email, $md5 );
		return true;
	}
	
	return false;
}

function doesOccur($haystack, $pattern) {
	$i = 0;
	foreach($haystack as $straw) {
		if(contains($pattern, $straw)) {
			return true;
		}
		++$i;
	}
	
	return false;
}

function contains($needle, $haystack) {
	return strpos($haystack, $needle) !== false;
}
	
?>

