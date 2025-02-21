<?php
/*
	Desc: CLI function to add a zoom path to an user account
	Author:	Sebastian Schmittner
	Date: 2015.06.23
	Last Author: Sebastian Schmittner
	Last Date: 2015.08.03 21:53:06 (+02:00)
	Version: 0.0.1
*/

class WrongArgumentCountException extends Exception {};

require_once __DIR__ . '/linkAndEmailTools.php';

if($argc != 3) {
	fwrite(STDERR, 'Argument count: ' . $argc . '\n');
	fwrite(STDOUT, 'Argument count: ' . $argc . '\n');
	throw new WrongArgumentCountException();
}

$path = $argv[1];
$email = $argv[2];
$cleanMail = cleanString( $email );

$realPath = rootPath() . '/polyzoomer/' . $path;

if(!file_exists($realPath)) {
	fwrite(STDOUT, "The specified path [" . $realPath . "] does not exist!\n");
	fwrite(STDERR, "The specified path [" . $realPath . "] does not exist!\n");
	exit(1);
}

$probableSetupFile = $realPath . '/setup.cfg';

/*if( file_exists($probableSetupFile) ) {
	$multizoomPath = rootPath() . '/customers/' . $cleanMail . '/multizooms/';
	$command = 'ln -s ' . enclose($realPath) . ' ' . enclose($multizoomPath); 
	executeSync($command);
}
else {*/

try {
	executeLinkAndEmail( $path, '[Polyzoomer] Automatically added', $email, $cleanMail );
}
catch (Exception $e) {
	doErrorLog('[EXCEPTION]: ' . json_encode($e));
}

//}

?>
