<?php
/*
	Desc: Functions to create and send an email.
	Author:	Sebastian Schmittner
	Date: - 
	Last Author: Sebastian Schmittner
	Last Date: 2015.08.03 21:53:13 (+02:00)
	Version: 0.2.6
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
require_once __DIR__ . '/pz_scripts/userpage/userFileSystem.php';

function executeLinkAndEmail( $path, $file, $email, $cleanmail ) {
	
	$indexHtml = createSymbolLink($path, $cleanmail, $email);
	$logFile = getLogfileName( $path );

	$result = getOrSetUserkey( $cleanmail );

	if( $result["created"] == 1 ) {
		createAndSendKeyEmail($email, $result["key"]);
	}

	createAndSendEmail($email, $file, $indexHtml, $logFile);
} 

function createAndSendEmail($email, $file, $indexHtml, $logfile) {

	$text = "Your Polyzoomer analysis is ready.\nPlease find your results under the following link.\n" . $indexHtml;
	$subject = 	"[" . $file . "] is ready";
	sendMail($email, $subject, $text);
	
	doLog('[SAMPLE]: ' . $email . " <= " . $text, logfile());
	doLog('[SAMPLE]: ' . $email . " <= " . $text, $logfile);
}

function createSymbolLink($path, $cleanmail, $email) {
	
	global $externalLink;
	
	$emailDirectory = safePath( userPath($cleanmail) );
	$multizoomPath = safePath($emailDirectory . "multizooms/");

	if(!file_exists($emailDirectory . 'email.txt')) {
		$updateUserPage = "cp -r " . rootPath() . "pz_scripts/userpage/* " . $emailDirectory;
		executeSync($updateUserPage);
	}
	
	$emailFile = $emailDirectory . 'email.txt';
	if(!file_exists($emailFile)) {
		file_put_contents($emailFile, $email);
	}
	
	$indexPath = rootPath() . "polyzoomer/" . $path . "/page/indexes";
	
	if(!file_exists($indexPath)) {
		$data = array(
			'Msg' => 'The indexpath is missing!',
			'Stack' => getStackTrace(),
			'Vars' => get_defined_vars()
		);
		
		throw new Exception(json_encode($data));
	}
	
	$referenceEmail = rootPath() . "polyzoomer/" . $path . "/email.txt";
	if(!file_exists($referenceEmail)) {
		file_put_contents($referenceEmail, $email);
	}
	
	$indexPath = "cat " . $indexPath;
	$indexPath = executeSync($indexPath);
	$indexPath = $indexPath[0];
	
	$zoomPath = '"' . rootPath() . 'polyzoomer/' . $path . '"';
	if(!file_exists($emailDirectory . '/' . $path)) {
		$symbolLinkCommand = 'ln -s ' . $zoomPath . ' "' . $emailDirectory . '"';
		executeSync($symbolLinkCommand);
	}
	else {
		doLog('[WARNING] Path already exists [' . $emailDirectory . '/' . $path . ']', logfile());
	}
	
	$fullIndexPath = "/customers/" . $cleanmail . "/" . $path . "/page/" . $indexPath;

	appendZoomToUserCache($cleanmail, $path, $fullIndexPath);
	
	return $externalLink . $fullIndexPath;
	
}

function getLogfileName( $path ) {
	return '"' . rootPath() . 'polyzoomer/' . $path . '/email.log"';
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
		chmod($keyfile, 0660);
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

function appendZoomToUserCache($cleanmail, $pathName, $fullIndexPath) {
	
	$startDir = rootPath() . dirname($fullIndexPath);
	
	$dzi = getDziFile( $startDir );
	$thumbnail = getThumbnailImageFile( $startDir );
	
	$date = 0;
	
	$pos = strrpos($pathName, '_');
	if($pos !== false) {
		$dateTimeStr = substr($pathName, $pos + 1);
		$date = DateTime::createFromFormat('YmdHi', $dateTimeStr);
	}
	else {
		$date = new DateTime();
	}

	$newProject = new Project($pathName, $fullIndexPath, $thumbnail, $date, $dzi);
	$newLine = json_encode( $newProject ) . PHP_EOL;
	
	$cacheFile = new TaskFileManipulator( cacheFile($cleanmail) );
	$cacheFile->appendLine( $newLine );
        
        appendZoomToUserFS($cleanmail, $newProject);
}

function appendZoomToUserFS($cleanmail, $project) {
    $pwd = getcwd();
    chdir(userPath($cleanmail));
    
    $item = projectToItem($project);
    
    $ufs = UserFileSystem::fromDefault($cleanmail);
    
    if($ufs !== NULL){
        if(!$ufs->doesItemExist(ufsUploadFolder())){
            $result = $ufs->addItem('///', $item);
            doLog('[' . $cleanmail . ']: Uploadfolder does not exist! [' . ufsUploadFolder() . ']', logfile());
        }
        else {
            if(!$ufs->doesItemExist(ufsUploadFolder() . $project->name)){
                $result = $ufs->addItem(ufsUploadFolder(), $item);
            }
            else {
                doLog('[' . $cleanmail . ']: Item does exist [' . $project->name . '] <= [' . json_encode($item) . ']', logfile());
            }
        }
    }
    else {
        doLog('[' . $cleanmail . ']: Creation of UFS failed! [' . json_encode($item) . ']', logfile());
    }

    chdir($pwd);
    
    return $result;
}

function projectToItem($project){
    $item = array(
        'name' => $project->name,
        'creationDate' => $project->fileDate->date,
        'type' => 'FILE'
    );
    
    return $item;
}

function getDziFile( $path ) {
	$dzi = rglob($path . '/*.dzi*');

	return $dzi;
}

function getThumbnailImageFile( $path ) {
	
	$images = rglob($path . '/*/0_1.*');
	$numbers = array();
	
	for($k = 0; $k < count($images); ++$k) {
		$key = basename(dirname($images[$k]));
		array_push($numbers, $key);
	}
	
	$minKey = min($numbers) - 1;
	if($minKey < 0){
		$minKey = 0;
	}
	
	$image = rglob($path . '/*/*/' . $minKey . '/0_0.*');
	
	$largest = str_replace(rootPath(), '/', $image);
	
	return cleanPath( $largest );
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