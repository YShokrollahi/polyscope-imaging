<?php 
/*
	Desc: Functions to send an email.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2015.04.28 22:10:42 (+02:00)
	Version: 0.0.6
*/

require_once "/var/www/pear/share/pear/Mail.php";
require_once "/var/www/polyzoomerGlobals.php";

function sendMail($recepient, $subject, $text){
	$recepient = str_replace(PHP_EOL, '', $recepient);

	sendMailInternal($recepient, $subject, $text);
	sendMailInternal(polyzoomerEmail(), $subject, $recepient . " <= " . $text);
}

function sendMailInternal($recepient, $subject, $text){
	
	$from = "Polyzoomer <polyzoomer@icr.ac.uk>";
	$to = $recepient;
	
	$headers = array(
		'From' => $from,
		'To' => $to,
		'Subject' => $subject,
		'Date' => date("r"),
	);
	
	$smtp = Mail::factory('smtp', array(
		'host' => 'outlook.icr.ac.uk',
		'port' => '587',
		'auth' => true,
		'username' => 'polyzoomer',
		'password' => '$$$PzForever$$$'
	));
	
	$mail = $smtp->send($to, $headers, $text);
	
	if(PEAR::isError($mail)) {
		echo($mail->getMessage());
	}
	else {
		echo('Message successfully sent!');
	}
	
}

?>
