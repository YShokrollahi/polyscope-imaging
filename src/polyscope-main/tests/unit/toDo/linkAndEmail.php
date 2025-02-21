<?php
/*
	Desc: Functions to create and send an email.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2015.04.28 22:10:27 (+02:00)
	Version: 0.1.7
*/

require_once 'polyzoomerGlobals.php';
require_once 'logging.php';
require_once 'sendEmail.php';
require_once 'serverCredentials.php';
require_once 'randomKeygen.php';

set_time_limit(600);

# main
$args = getArguments();
$indexHtml = createSymbolLink($args["path"], $args["cleanmail"]);
$logFile = '"' . rootPath() . 'polyzoomer/' . $args["path"] . '/email.log"';

$result = getOrSetUserkey( $args["cleanmail"] );

if( $result["created"] == 1 ) {
	createAndSendKeyEmail($email, $result["key"]);
}

createAndSendEmail($args["email"], $args["file"], $indexHtml, $logFile);

# end

function createAndSendEmail($email, $file, $indexHtml, $logfile) {

	$text = "Your Polyzoomer analysis is ready.\nPlease find your results under the following link.\n" . $indexHtml;
	$subject = 	"[" . $file . "] is ready";
	sendMail($email, $subject, $text);
	
	doLog('[SAMPLE]: ' . $email . " <= " . $text, logfile());
	doLog('[SAMPLE]: ' . $email . " <= " . $text, $logfile);
}

function createSymbolLink($path, $cleanmail) {
	
	global $externalLink;
	
	$externalPath = preg_replace("/\/[a-zA-Z]*\/[a-zA-Z]*/", "", $path);
	
	$emailDirectory = rootPath() . "customers/" . $cleanmail . "/";
	$createDirectory = "mkdir -p " . $emailDirectory;
	shell_exec($createDirectory);
	
	if(!file_exists($emailDirectory . 'raster.js')) {
		$updateUserPage = "cp -r " . rootPath() . "pz_scripts/userpage/* " . $emailDirectory;
		shell_exec($updateUserPage);
	}
	
	$multizoomPath = $emailDirectory . "multizooms/";
	$createDirectory = "mkdir -p " . $multizoomPath;
	shell_exec($createDirectory);
	
	$indexPath = "cat " . rootPath() . "polyzoomer/" . $path . "/page/indexes";
	$indexPath = shell_exec($indexPath);
	
	$symbolLinkCommand = 'ln -s "' . rootPath() . 'polyzoomer/' . $path . '" "' . $emailDirectory . '"';
	shell_exec($symbolLinkCommand);
	
	return $externalLink . "/customers/" . $cleanmail . "/" . $path . "/page/" . $indexPath;
	
}

function createAndSendKeyEmail($email, $userkey) {

	$text = "Your Polyzoomer USER-Key is: '" . $userkey . "'\nThis key is required for deleting zooms on your userpage. Please keep it secret.";
	$subject = "[Polyzoomer-Userpage KEY]";
	sendMail($email, $subject, $text);
	
	doLog('[USERCODE]: ' . $email . ' <= ' . $text, logfile());
}

function getOrSetUserkey( $cleanmail ) {
	
	$emailDirectory = rootPath() . "customers/" . $cleanmail . "/";
	$keyfile = $emailDirectory . "/.userkey";
	$created = 0;
	
	if(!file_exists($keyfile)) {
		
		$userkey = keygen();
		file_put_contents($keyfile, $userkey);
		$created = 1;
	}
	else {
		$userkey = file_get_contents($keyfile);
		
		$keys = array();
		preg_match("/[A-Z0-9]*/", $userkey, $keys);
		
		if( count($keys) != 0 ) {
			$userkey = $keys[0];
		}
		else {
			doLog('[USERCODE]: User key could not be loaded! Keyfile could be corrupted. Please remove the keyfile [' . $keyfile . '] and resend a key!', logfile());
		}
	}
	
	return array (
		"key" => $userkey,
		"created" => $created
	);
}

function getArguments() {
	global $argv;
	global $argc;
	
	$path = $argv[$argc - 4];
	$file = $argv[$argc - 3];
	
	$email = $argv[$argc - 2];
	$cleanmail = $argv[$argc - 1];
	
	return array (
		"path" => $path,
		"file" => $file,
		"email" => $email,
		"cleanmail" => $cleanmail
	);
}

?>
