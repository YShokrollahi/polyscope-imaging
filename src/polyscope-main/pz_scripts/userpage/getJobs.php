<?php
/*
	Desc: Retrieves all current active jobs for the user
	Author:	Sebastian Schmittner
	Date: 2015.07.29 12:00:47 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.29 12:00:47 (+02:00)
	Version: 0.0.3
	
*/

require_once '../../jobTools.php';

set_time_limit(600);

$cleanMail = pathinfo(dirname(__FILE__));
$cleanMail = $cleanMail['basename'];

$email = retrieveEmail($cleanMail);

echo json_encode( JobTools::getActiveJobs( $email ) );

//////////////////////////////////////////////////////////////////

function retrieveEmail( $cleanMail ) {
	
	$path = '/var/www/customers/' . $cleanMail . '/email.txt';
	
	$command = 'cat ' . $path;
	
	$email = exec($command);
	
	return $email;
}

?>
