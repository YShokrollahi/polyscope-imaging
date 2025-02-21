<?php
/*
	Desc: Creates a new code and sends it to the email of the user
	Author:	Sebastian Schmittner
	Date: 2015.04.19 11:41:48 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.04.19 11:41:48 (+02:00)
	Version: 0.0.1
*/

require_once './../../polyzoomerGlobals.php';
require_once './../../logging.php';
require_once './../../sendEmail.php';
require_once './../../randomKeygen.php';

echo json_encode( sendCode() );

function sendCode() {
	
	$keyfile = './.userkey';
	
	$userkey = keygen();
	file_put_contents($keyfile, $userkey);
	
	$email = file_get_contents('./email.txt');
	
	$subject = "[Polyzoomer] Userpage Code";
	$message = "Your new Polyzoomer Userpage code is: '" . $userkey . "'."; 
	
	$result = sendMail($email, $subject, $message);
	
	$email = str_replace(PHP_EOL, '', $email);
	
	doLog('[USERCODE]: User [' . $email . '] requested new code [' . $userkey . ']', logfile());
	
	return $result;
}

?>
