<?php
/*
	Desc: Class to interact with all the customer projects
	Author:	Sebastian Schmittner
	Date: 2015.10.11 15:04:37 (+02:00)
	Last Author: Sebastian Schmittner (stp.schmittner@gmail.com)
	Last Date: 2015.10.12 15:10:05 (+02:00)
	Version: 0.1.3
	
*/

require_once '../../taskFileManipulator.php';

class PathDoesNotExist extends Exception {};

class CustomerProjects {
	
	private $cleanMail;
	private $rootPath;
	private $lastUpdate;
	private $projects;
	private $cacheFileName = 'cache.lst';
	
	public function __construct( $cleanMail ) {
		
		$this->cleanMail = $cleanMail;
		$this->rootPath = '/var/www/customers/' . $this->cleanMail . '/';
		
		if( !file_exists($this->rootPath) ) {
			throw new PathDoesNotExist('[' . $this->rootPath . '] does NOT exist!');
		}
		
		$this->loadCacheFile();
	}
	
	public function __destruct() {
	}
	
	public function toList() {
		return $this->projects;
	}
	
	public function cacheFilePath() {
		return $this->rootPath . $this->cacheFileName;
	}
	
	public function loadCacheFile() {
		
		$cache = array();
		$cachePath = $this->cacheFilePath();
		
		if(file_exists($cachePath)) {
			
			$cacheFile = new TaskFileManipulator($cachePath);
			$cache = $cacheFile->getContents();
			//file($cachePath, FILE_SKIP_EMPTY_LINES);
			
			$this->projects = array();
			
			foreach( $cache as $line ) {
				$project = json_decode($line);
				if($project != null) {
					array_push($this->projects, $project);
				}
			}
		}
	}
}


?>
