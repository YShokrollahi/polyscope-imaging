<?php
/*
	Desc: Executes the supervisor 1 time
	Author:	Sebastian Schmittner
	Date: 2015.05.30 17:46:05 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.23 21:57:10 (+02:00)
	Version: 0.0.7
*/

require_once __DIR__ . '/polyzoomerGlobals.php';
require_once __DIR__ . '/taskFileManipulator.php';
require_once __DIR__ . '/jobRepresentationAnalysis.php';
require_once __DIR__ . '/issueProject.php';
require_once __DIR__ . '/subtaskMultizoom.php';

$jobFile = analysisJobMasterFile();

executeAnalysisSupervisor( $jobFile );

return;

///////////////////////////////////////////////////

function executeAnalysisSupervisor( $jobFile ) {
	
	// create task file
	$mainTaskFile = new TaskFileManipulator( $jobFile );
	$mainTaskFile->update();
	$contents = $mainTaskFile->getContents();
	
	// separate the comments from the jobs
	$comments = preg_grep("/^#/i", $contents);
	$jobsList = preg_grep("/^#/i", $contents, PREG_GREP_INVERT);
	
	$jobs = array();
	
	// load all jobs
	foreach($jobsList as $guid) {
	
		$guid = trim($guid);
		if(empty($guid)) {
			continue;
		}
	
		$localJob = null;
	
		try {
			$jobFile = analysisJobFile($guid);
			$result = lockedFileRead($jobFile, filesize($jobFile), 'r', false);
		
			if($result['id'] == 0) {
				$entry = $result['data'];
				$localJob = new AnalysisJob();
				$localJob->withText($entry);
			}
			else {
				jobLog2($guid, "[ERROR] Could not read the analysis job content of the file! [" . $guid . "]");
			}
			
			if( $localJob->data['status'] != 'toDownload' ) {
				$localJob = null;
			}
		}
		catch (Exception $e) {
			$localJob = null;
			jobLog2($guid, '[FATAL] Failed to load analysis job specific file!');
			jobLog2($guid, '- [EXCEPTION] [' . json_encode($e) . ']');
		}
	
		if(isset($localJob)) {
			array_push($jobs, $localJob);
		}
	}

	// process each job
	foreach($jobs as $job) {
		
		$guid = $job->data['guid'];
		
		$resultFolder = analysisOut( $guid );
		$resultFilename = $job->data['sampleName'] . '_' . $job->data['appName'] . '.png';
		$resultPathAndFilename = $resultFolder . $resultFilename;
		
		if( !tryUpdateJobEntry($guid, '4;toDownload', '4;downloading') ) {
			continue;
		}
		
		$targetFilename = uploadFolder( $guid ) . 'analyses/' . $resultFilename;
		
		if( file_exists($resultPathAndFilename) ) {
			$command = 'cp ' . enclose( $resultPathAndFilename ) . ' ' . enclose( $targetFilename );
			jobLog2( $guid, '[INFO] Copying result image to polyzoomer [' . $targetFilename . ']');
			executeSync( $command );
			
			$updated = tryUpdateJobEntry($guid, '4;downloading', '4;downloaded');

			$task = new MultizoomTask($job->data['email'], array( $job->data['sourcefile']));
			$taskFile = createTaskFile();
			$taskSet = loadTaskFile( $taskFile );
			$taskSet->pushTask( $task );
			saveTaskFile( $taskFile, $taskSet );
			
			jobLog2( $guid, '[INFO] Issue file for polyzoom [' . $targetFilename . '] to [' . $job->data['email'] . ']');
			issueFile( $targetFilename, $job->data['email'], $taskFile );

			$updated = tryUpdateJobEntry($guid, '4;downloaded', '4;finished');
		}
		else {
			// inform customer
			$subject = '[Analysis] failed!';
			$text = "Your submitted analysis [" . $job->data['sampleName'] . "] for [" . $job->data['appName'] . "] failed!\n" .
					"Based upon [" . $job->data['sourcefile'] . "]\n" .
					"The support is being informed.\n" . 
					"Please contact the support for further details.";

			sendMail($job->data['email'], $subject, $text);
			
			// inform support
			$subject = '[Analysis] failed!';
			$text = "Analysis [" . $guid . "] failed.\n" .
					"[" . json_encode( $job ) . "]";
			sendMail(polyzoomerEmail(), $subject, $text);

			$updated = tryUpdateJobEntry($guid, '4;downloading', '4;finished');
			
			doLog("[FATAL] Analysis failed: [" . $guid . "]", logfile());
		}
	}
	
}

function updateJobEntry($guid, $currentStatus, $newStatus) {
	$jobFile = analysisJobFile($guid);
	
	$taskFile = new taskFileManipulator($jobFile);
	
	$pattern = ";$currentStatus;";
	$text    = ";$newStatus;";

	$result = $taskFile->doSafeRegexUpdate($pattern, $text, 3000);
	
	if( $result['id'] != 0 ) {
		jobLog2($guid, '[WARN] Could not lock the jobfile!');
		throw new LockFailedException();
	}

	jobLog2($guid, '[INFO] ' . $guid . " changes from [" . $currentStatus . "] to [" . $newStatus . "]");
}

function tryUpdateJobEntry( $guid, $statusFrom, $statusTo ) {

	$succeeded = true;
	
	try {
		updateJobEntry( $guid, $statusFrom, $statusTo );
	}
	catch ( Exception $e ) {
		jobLog2( $guid, '[FATAL] Update of job status failed! [' . $statusFrom . '] to [' . $statusTo . ']');
		jobLog2( $guid, '- [EXCEPTION] ' . json_encode( $e ));
		$succeeded = false;
	}
	
	return $succeeded;
}

?>
