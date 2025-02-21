<?php
/*
	Desc: Class to interact with all the customer multizooms
	Author:	Sebastian Schmittner (stp.schmittner@gmail.com)
	Date: 2015.01.24 16:17:05 (+01:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.04.09 14:44:00 (+02:00)
	Version: 0.0.3
	
*/

require_once '../../taskFileManipulator.php';

class PathDoesNotExist extends Exception {};

class CustomerMultizooms {
	
	private $cleanMail;
	private $rootPath;
	private $lastUpdate;
	private $projects;
	private $imageRoot;
	private $cacheFileName = 'multi_cache.lst';
	
	public function __construct( $cleanMail ) {
		
		$this->cleanMail = $cleanMail;
		$this->rootPath = '/var/www/customers/' . $this->cleanMail . '/multizooms/';
		$this->imageRoot = '/customers/' . $this->cleanMail . '/multizooms/';
		
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
			
			$this->projects = array();
			
			foreach( $cache as $line ) {
				$line = trim($line);
				if(strlen($line) > 0) {
					$project = json_decode($line);
					if($project !== null ) {
						$project->index = str_replace('polyzoomer/', 'customers/' . $this->cleanMail . '/multizooms/', $project->index);
						array_push($this->projects, $project);
					}
				}
			}
		}
	}
}

?>
