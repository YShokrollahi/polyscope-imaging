<?php
/*
	Desc: Multizoomer Subtask implementation
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.07.17 22:00 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.23 22:37:56 (+02:00)
	Version: 0.0.2
*/

require_once 'subtaskDefinition.php';

class MultizoomTask implements SubTask {
	private $parameters;
	
	public function __construct( $email, $zooms ) {
		$this->parameters = array( 'email' => $email,
								   'zooms' => $zooms );
	}
	
	public function __destruct() {}
	
	public function taskType() {
		return SubTaskType::CREATEMULTIZOOM;
	}
	
	public function parameters() {
		return $this->parameters;
	}
	
	public function execute( Job $job ) {
		
		$zooms = $this->parameters['zooms'];
		$email = $this->parameters['email'];
		
		$zoom1 = array( 'dzi' => $zooms[0],
						'alpha' => '1' );

		$zoom2Path = $job->data['finalPath'];
		$path = rootPath() . '/polyzoomer/' . $zoom2Path . '/';
		$dziPathCommand = 'find ' . $path . ' -name "*.dzi"';
		$result = executeSync($dziPathCommand);
		
		$zoom2 = array( 'dzi' => $result[0],
						'alpha' => '0' );
		
		$col1 = array( $zoom1 );
		$col2 = array( $zoom2 );
		$row1 = array( $col1, $col2 );
		
		$layout = array(
							'rows' => 1,
							'cols' => 2,
							'table' => $row1,
							'email' => $email
		);
		
		$indexPath = doMultizoom( $layout );
		
		$subject = '[Analysis] is ready';
		$text = "Your Polyzoomer analysis is ready.\nPlease find your results under the following link.\n" . $indexPath;
		sendMail($email, $subject, $text);
	}
}

///
require_once 'lockedFileAccess.php';
require_once 'htmlError.php';
require_once 'logging.php';
require_once 'serverCredentials.php';

function doMultiZoom( $layout ) {
	
	global $externalLink;
	
	$rootPath = "/var/www/";
	$pathName = getTargetDirectory($rootPath);
	
	$home = userPath( cleanString( $layout['email'] ) );
	
	$setupFile = $pathName . '/setup.cfg';

	$setupContent = generateSetupContent($layout);
	
	$result = file_put_contents($setupFile, $setupContent, LOCK_EX);
	
	if($result === FALSE) {
		errorMessage("Setupfile could not be created/written to! [" . $setupFile . "]", 1);
	}
	
	$x = shell_exec("cp " . $home . "/createMultiZoomerSite.sh " . $pathName);
	$x = shell_exec("chmod 777 " . $pathName . "/createMultiZoomerSite.sh");
	$x = shell_exec("cd " . $pathName . " && ./createMultiZoomerSite.sh");
	
	$indexPath = $pathName . "/page/INDEX/index.html";
	
	$link = 'ln -s "' . $pathName . '" "' . $home . '/multizooms/"';
	shell_exec($link);
		
	while(!file_exists($indexPath)) {};

	$indexPath = str_replace("/var/www", $externalLink, $indexPath);
	
	$x = shell_exec('cp ' . $home . '/createThumbnails.sh ' . $pathName);
	$x = shell_exec('chmod 777 ' . $pathName . '/createThumbnails.sh');
	$x = shell_exec('cd ' . $pathName . ' && ./createThumbnails.sh "' . $setupFile . '"');
	
	return $indexPath;
}

function generateSetupContent($layout) {
	global $externalLink;

	$setupContent = Array();
	
	array_push($setupContent, $layout['cols']);
	array_push($setupContent, $layout['rows']);
	array_push($setupContent, $layout['email']);
	
	for($x = 0; $x < $layout['cols']; ++$x) {
		for($y = 0; $y < $layout['rows']; ++$y) {
			$dziPath = str_replace("/var/www", $externalLink, $layout['table'][$x][$y]['dzi']);
			array_push($setupContent, $dziPath);
			array_push($setupContent, $layout['table'][$x][$y]['alpha']);
		}
	}
	
	$setupContent = implode("\n", $setupContent);
	return $setupContent;
}

function getTargetDirectory($rootPath) {
	$counterFile = $rootPath . "/jobCounter.log";
	
	$counter = atomicCounterIncrement( $counterFile );
	
	if ( $counter == -1 ) {
		errorMessage("Counter seems to be invalid!", 1);
	}
	
	$basePathName = "Path" . number_pad($counter, 6) . "_" . date('YmdHi');
	$pathName = $rootPath . "/polyzoomer/" . $basePathName;
	
	$dirCreated = mkdir( $pathName );
	
	if ( !$dirCreated ) {
		errorMessage("Directory could not be created! [" . $pathName . "]", 2);
	}
	
	return $pathName;
}

function number_pad($number,$n) {
	return str_pad((int) $number,$n,"0",STR_PAD_LEFT);
}

function log_error($text) {
	shell_exec("echo " . $text . " >> process.log");
}

?>



















