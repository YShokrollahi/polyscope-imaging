<?php
/*
	Desc: Tests if an email can be successfully sent.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2014.10.02 12:42:40 (+02:00)
	Version: 0.0.2
*/

include 'sendEmail.php';
include 'serverCredentials.php';

sendMail("polyzoomer@gmail.com", "test", "test " . $externalLink);
sendMail("polyzoomer@icr.ac.uk", "test", "test " . $externalLink);
?>
