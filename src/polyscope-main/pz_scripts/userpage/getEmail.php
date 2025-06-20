<?php
/*
	Desc: Retrieves the email address of the current folder
	Author:	Sebastian Schmittner
	Date: 2014.10.21 23:42:25 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2014.11.14 10:35:11 (+01:00)
	Version: 0.0.2
	
*/

set_time_limit(600);

//$cleanMail = json_decode($_POST["email"]);
$cleanMail = pathinfo(dirname(__FILE__));
$cleanMail = $cleanMail['basename'];

echo json_encode( retrieveEmail($cleanMail) );

//////////////////////////////////////////////////////////////////

function retrieveEmail( $cleanMail ) {
	
	$path = '/var/www/customers/' . $cleanMail . '/email.txt';
	
	$command = 'cat ' . $path;
	
	$email = exec($command);
	
	return $email;
}

?>
