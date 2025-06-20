<?php
/*
	Desc: Receives all parameters to start a new analysis
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.05.25 09:05:13 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.06.19 12:45:11 (+02:00)
	Version: 0.0.2
	
*/

require_once 'useApp.php';

set_time_limit(3600);

$parameters = json_decode($_POST['params'], true);

$paths = $parameters['path'];
$app = $parameters['app'];
$email = $parameters['email'];
$parameter = $parameters['parameter'];
$sampleNames = $parameters['sampleName'];

doLog(json_encode($_POST), logfile());

echo json_encode( useApp( $app, $sampleNames, $paths, $email, $parameter ) );

//////////////////////////////////////////////////////////////////
