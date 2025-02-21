<?php
/*
	Desc: Functions to issue the update of the email.
	Author:	Sebastian Schmittner
	Date: 2014.09.07 20:37:34 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.04.19 11:40:23 (+02:00)
	Version: 0.0.7
*/

class FileContentChangedException extends Exception {};
class WrongArgumentCountException extends Exception {};

require_once __DIR__ . '/polyzoomerGlobals.php';
require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/taskFileManipulator.php';

set_time_limit(600);

$email = json_decode($_POST["email"]);
$guid = json_decode($_POST["guid"]);

echo json_encode( issueUpdateEmail( $email, $guid ) );

//////////////////////////////////////////////////////////////////

function issueUpdateEmail( $email, $guid ) {

	$jobFile = jobFileG($guid);
	$counter = 1000000;
	
	$taskFileHandler = null;
	
	while($taskFileHandler === null && $counter > 0) {
		
		try {
			$taskFileHandler = new taskFileManipulator($jobFile);
		}
		catch (Exception $e) {
			$taskFileHandler = null;
			doLog('[EXCEPTION]: ' . print_r($e, true), logfile());			
		}
		
		$counter = $counter - 1;
		usleep(1000);
	}
	
	$linePattern = ";$guid;";
	$pattern = ";EMAIL_PLACE_HOLDER;";
	$replacement = ";$email;";

	$result = array( 'id' => 0 );
	
	try {
		$result = $taskFileHandler->doSafeRegexUpdate($pattern, $replacement, 1000000);
	}
	catch (Exception $e) {
		doLog('[EXCEPTION]: ' . print_r($e, true), logfile());
	}
	
	return $result;
}
		
?>
