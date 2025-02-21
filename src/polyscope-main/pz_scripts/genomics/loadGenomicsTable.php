<?php
/*
	Desc: Function to load a specified genomics table
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.10.08 22:58:56 (+02:00)
	Last Author: Sebastian Schmittner (stp.schmittner@gmail.com)
	Last Date: 2015.10.12 15:10:05 (+02:00)
	Version: 0.0.9
*/

class WrongArgumentCountException extends Exception {};

require_once 'linkAndEmailTools.php';

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
	executeLinkAndEmail( $path, '[Polyscope] Automatically added', $email, $cleanMail );
}
catch (Exception $e) {
	doErrorLog('[EXCEPTION]: ' . json_encode($e));
}

//}

?>
