<?php
/*
	Desc: Supervises all tasks on the analysis server
	Author:	Sebastian Schmittner
	Date: 2015.05.17
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.15 21:32:18 (+02:00)
	Version: 0.0.8
*/

require_once 'polyzoomerGlobals.php';
require_once 'logging.php';
require_once 'tools.php';
require_once 'md5chk.php';
require_once 'taskFileManipulator.php';
require_once 'jobRepresentation.php';

class WrongArgumentCountException extends Exception {};

class TaskSupervisor {
	
/*	private $jobStati = array(
		'1' => array('transfer', 'pending'), 
		'2' => array('upload', 'uploading', 'uploaded'),
		'3' => array('processing', 'toAnalyze', 'analysing', 'toMerge', 'merging'),
		'4' => array('toDownload', 'downloading', 'downloaded', 'finished')
	);*/

	private $taskFileName;
	private $taskFileHandler;
	private $jobs;
	
	private $doneTaskHandler;
	
	private $statusMap;
		
	private $config;
	const maxJobs = 2;
	const maxUploads = 4;
	
	public function __construct( $taskFile ) {
		$this->taskFileName = $taskFile;
		$this->taskFileHandler = new TaskFileManipulator( $this->taskFileName );
		$this->doneTaskHandler = new TaskFileManipulator( jobDoneFile() );
		$this->config = $this->loadConfig();
	}
	
	public function __destruct() {
	}
	
	public function update() {
		
		doEcho("update()\n");
		
		$this->taskFileHandler->update();
		$contents = $this->taskFileHandler->getContents();
		
		$comments = preg_grep("/^#/i", $contents);
		$jobs = preg_grep("/^#/i", $contents, PREG_GREP_INVERT);
		
		$this->jobs = array();
		
		foreach($jobs as $guid) {
			
			$guid = trim($guid);
			if(empty($guid)) {
				continue;
			}
			
			$localJob = null;
			
			try {
				$jobFile = jobFileG($guid);
				$result = lockedFileRead($jobFile, filesize($jobFile), 'r', false);
				
				if($result['id'] == 0) {
					$entry = $result['data'];
					$localJob = new Job();
					$localJob->withText($entry);
				}
				else {
					jobLog($guid, "[ERROR] Could not read the job content of the file! [" . $guid . "]");
				}
			}
			catch (Exception $e) {
				$localJob = null;
				jobLog($guid, '[EXCEPTION] Failed to load job specific file! [' . json_encode($e) . ']');
			}
			
			if(isset($localJob)) {
				array_push($this->jobs, $localJob);
			}
		}
		
		$this->createMaps($this->jobs);
		
		$this->takeCareOfJobs($this->jobs);
	}
		
	public function createMaps($jobs) {
		
		$this->statusMap = array();
		$this->createStatusMap($this->statusMap);
		
		$i = 0;
		
		doLog('[DEBUG] ' . json_encode($jobs), logFile());

		foreach($jobs as $job) {
			array_push($this->statusMap[$job->data['status']], $i);

			++$i;
		}
	}

	public function createStatusMap(&$map) {
		
		$map['transfer'] = array();
		$map['pending'] = array();
		
		$map['upload'] = array();
		$map['uploading'] = array();
		$map['uploaded'] = array();
		
		$map['processing'] = array();
		$map['toAnalyze'] = array();
		$map['analysing'] = array();
		$map['toMerge'] = array();
		$map['merging'] = array();
		
		$map['toDownload'] = array();
		$map['downloading'] = array();
		$map['downloaded'] = array();
		$map['finished'] = array();
	}
		
	public function takeCareOfJobs(&$jobs) {
		
		// Status 4
		doEcho("4 - Finished");
		$this->handleFinishedJobs($jobs);
		$this->handleDownloadedJobs($jobs);
		$this->handleDownloadingJobs($jobs);
		$this->handleToDownloadJobs($jobs);
			
		// Status 3
		doEcho("3 - Processing");
		$this->handleMergingJobs($jobs);
		$this->handleToMergeJobs($jobs);
		$this->handleAnalysingJobs($jobs);
		$this->handleToAnalyzeJobs($jobs);
		$this->handleProcessingJobs($jobs);
		
		// Status 2
		doEcho("2 - Uploading");
		$this->handleUploadedJobs($jobs);
		$this->handleUploadingJobs($jobs);
		$this->handleUploadJobs($jobs);
		
		// Status 1
		doEcho("1 - Pending");
		$this->handlePendingJobs($jobs);
		$this->handleTransferJobs($jobs);
		
		// the only function which removes elements (must be last)
		doEcho("Remove");
		$this->removeJobs($jobs);

		doEcho("Cycle Complete");
	}
	
	public function removeJobs(&$jobs) {
		
		$entries = $this->getEntriesByStatus('finished');
		
		doEcho(" >> " . count($entries) . " projects to be removed.");
		
		foreach($entries as $index) {
			$guid = $jobs[$index]->data['guid'];
			$file = jobFileG($guid);
			$result = lockedFileRead($file, filesize($file), 'r');
			
			if($result['id'] == 0) {
				$entry = $result['data'];
				jobLog( $guid, '[DONE] Jobs last entry: [' . $entry . ']' );
			}
					
			unlink($file);
			
			$result = $this->taskFileHandler->doSafeRegexRemove($jobs[$index]->data['guid'], 1000000);
			
			//if(array_key_exists('deletedData', $result)) {
				doLog('[JOB]: ' . print_r($result, true), logfile());

				$jobText = trim($result['deletedData']);
				$jobText = str_pad($jobText, 300);
				$this->doneTaskHandler->appendLine($jobText);
			/*}
			else {
				doSysLog(LOG_ERR, array('message' => "Could not safe DONE task [" . $jobs[$index]->data['guid'] . "] [" . $result . "]" ));
			}*/
		}
	}
	
	public function handleFinishedJobs(&$jobs) {
		
		$entries = $this->getEntriesByStatus('finished');

		doEcho(" >> " . count($entries) . " finished jobs.");
	}
	
	public function handleDownloadedJobs(&$jobs) {
	
		$entries = $this->getEntriesByStatus('downloaded');
		
		foreach($entries as $index) {
			$this->doTransfer($jobs[$index], 4, 'finished');
		}
	}
	
	public function handleDownloadingJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('downloading');
		
		foreach($entries as $index) {
			// NULL
		}
	}

	public function handleToDownloadJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('downloading');
		
		foreach($entries as $index) {
			// NULL
		}
	}

//	
	public function handleMergingJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('merging');
		
		foreach($entries as $index) {
			// NULL
		}
	}

	public function handleToMergeJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('toMerge');
		
		$freeSlots = max(self::maxJobs - $this->getCurrentAnalyses(), 0);

		if($freeSlots > 0) {
			$entriesToProcess = array_splice($entries, 0, $freeSlots);

			foreach($entriesToProcess as $index) {
				
				$currentJob = $jobs[$index];
				$jobData = $currentJob->data;
				$guid = $jobData['guid'];
				
				$command = 'php doMerge.php ' . enclose( base64_encode(json_encode( $guid )) );
				
				jobLog( $guid, '[INFO] Start Merging. [' . enclose( $command ) . ']' );
				
				executeAsync( $command );
			}
		}
	}
	
	public function handleAnalysingJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('analysing');
		
		foreach($entries as $index) {
			// NULL
		}
	}
	
	public function handleToAnalyzeJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('toAnalyze');

		$freeSlots = max(self::maxJobs - $this->getCurrentAnalyses(), 0);

		if($freeSlots > 0) {
			$entriesToProcess = array_splice($entries, 0, $freeSlots);

			foreach($entriesToProcess as $index) {
				
				$currentJob = $jobs[$index];
				$jobData = $currentJob->data;
				$guid = $jobData['guid'];
				
				$command = 'php doAnalysis.php ' . enclose( base64_encode(json_encode( $guid )) );
				
				jobLog( $guid, '[INFO] Start Analysis. [' . enclose( $command ) . ']' );
				
				executeAsync( $command );
			}
		}
	}
	
	public function handleProcessingJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('processing');
		
		foreach($entries as $index) {
			// NULL
		}
	}
	
	public function handleUploadedJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('uploaded');

		$freeSlots = max(self::maxUploads - $this->getCurrentAnalyses(), 0);

		if($freeSlots > 0) {
			$entriesToProcess = array_splice($entries, 0, $freeSlots);

			foreach($entriesToProcess as $index) {
				
				$currentJob = $jobs[$index];
				$jobData = $currentJob->data;
				$guid = $jobData['guid'];
				
				$command = 'php doTiling.php ' . enclose( base64_encode(json_encode( $guid )) );
				
				jobLog( $guid, '[INFO] Start Tiling. [' . enclose( $command ) . ']' );
				
				executeAsync( $command );
			}
		}
	}
	
	public function handleUploadingJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('uploading');
		
		foreach($entries as $index) {
			// NULL
		}
	}
	
	public function handleUploadJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('upload');
		
		foreach($entries as $index) {
			
			$currentJob = $jobs[$index];
			$jobData = $currentJob->data;
			
			$guid = $jobData['guid'];
			$sampleName = $jobData['sampleName'];
			
			mkdir( jobContainer( $guid ) . 'code/', 0777, true );
			mkdir( jobContainer( $guid ) . 'raw/', 0777, true );
			mkdir( jobContainer( $guid ) . 'data/cws/' . $sampleName . '/', 0777, true );
			mkdir( jobContainer( $guid ) . 'result/' . $sampleName . '/', 0777, true );
			
			//$files = dirToArray( analysisIn( $guid ), DTA_FILES );
			
			$commandBuffer = array( 
									'guid' => $guid,
									'to' => jobContainer( $guid ) . 'raw/',
									'preStartState' => '2;upload', 
									'startState' => '2;uploading',
									'endState' => '2;uploaded',
									'path' => analysisIn( $guid ) );
									//'files' => $files );
			
			$jsonCommandBuffer = base64_encode(json_encode( $commandBuffer ));
			
			$command = 'php doTransferFiles.php ' . enclose($jsonCommandBuffer);
			
			jobLog( $guid, '[INFO] Issue upload to job container. [' . enclose( $command ) . ']');
			
			executeAsync( $command );
		}
	}
	
	public function handlePendingJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('pending');

		$freeSlots = max(4 - $this->getCurrentAnalyses(), 0);

		if($freeSlots > 0) {
			$entriesToUpload = array_splice($entries, 0, $freeSlots);

			foreach($entriesToUpload as $index) {
				$this->doTransfer($jobs[$index], 2, 'upload');
			}
		}
	}
	
	public function handleTransferJobs(&$jobs) {

		$entries = $this->getEntriesByStatus('transfer');
		
		foreach($entries as $index) {
			// NULL
		}
	}
	
	public function doTransfer($job, $newStatusId, $newStatus) {
		$transfer = $this->makeUpStatusTransfer($job, $newStatusId, $newStatus);
		$file = new TaskFileManipulator( jobFileG( $job->data['guid'] ) );
		$file->doSafeRegexUpdate($transfer['pattern'], $transfer['text'], 3000);
		jobLog($job->data['guid'], $job->data['guid'] . " changes from [" . $transfer['pattern'] . "] to [" . $transfer['text'] . "]");
	}
	
	public function makeUpStatusTransfer($job, $newStatusId, $newStatus) {
		//$pattern = ";" . $job->data['guid'] . ";" . $job->data['statusId'] . ";" . $job->data['status'] . ";";
		//$text    = ";" . $job->data['guid'] . ";" . $newStatusId . ";" . $newStatus . ";";
		$pattern = ";" . $job->data['statusId'] . ";" . $job->data['status'] . ";";
		$text    = ";" . $newStatusId . ";" . $newStatus . ";";
		
		return array(
			'pattern' => $pattern,
			'text' => $text 
			);
	}
	
	public function getCurrentUploadsCount() {

		$uploadCount = count($this->getEntriesByStatus('uploading'));
		
		return $uploadCount;
	}
	
	public function getCurrentAnalyses() {

					   
		$processCount = count($this->getEntriesByStatus('processing')) +
					    count($this->getEntriesByStatus('analysing')) + 
					    count($this->getEntriesByStatus('merging'));
		
		return $processCount;
	}
	
	public function getEntriesByStatus($status) {
		return $this->statusMap[$status];
	}
	
	public function loadConfig() {
		$configFileName = rootPath() . 'php/concurrency.config';
		
		$result = lockedFileRead($configFileName, filesize($configFileName), 'r');
		
		if( $result['id'] != 0 ) {
			throw new FileReadException($result['comment'], $result['id']);
		}
		
		$data = explode("\n", $result['data']);
		
		$config = array();
		foreach($data as $entry) {
			if($entry != "") {
				$splitted = explode(":", $entry);
				$config[$splitted[0]] = $splitted[1];
			}
		}
		
		return $config;
	}
}


?>
