<?php
/*
	Desc: Functions to create and send an email.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.11 09:18:57 (+02:00)
	Version: 0.2.4
*/

require_once __DIR__ . '/polyzoomerGlobals.php';
require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/sendEmail.php';
require_once __DIR__ . '/serverCredentials.php';
require_once __DIR__ . '/randomKeygen.php';
require_once __DIR__ . '/tools.php';
require_once __DIR__ . '/customerProject.php';
require_once __DIR__ . '/addLineToFile.php';
require_once __DIR__ . '/taskFileManipulator.php';
require_once __DIR__ . '/linkAndEmailTools.php';

set_time_limit(600);

# main

$parameters = getArguments();

executeLinkAndEmail($parameters["path"], $parameters["file"], $parameters["email"], $parameters["cleanmail"]);

# end

?>
