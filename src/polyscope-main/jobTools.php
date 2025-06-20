<?php
/*
	Desc: job toolset
	Author:	Sebastian Schmittner
	Date: 2015.07.29 12:00:22 (+02:00)
	Last Author: Sebastian Schmittner
	Last Date: 2015.07.29 12:00:22 (+02:00)
	Version: 0.0.1
*/

require_once __DIR__ . '/polyzoomerGlobals.php';
require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/taskFileManipulator.php';
require_once __DIR__ . '/jobRepresentation.php';
require_once __DIR__ . '/jobRepresentationAnalysis.php';

class JobTools {
	
	static public function getActiveJobs( $user = NULL ) {
		
		$jobs = array(
				'zoom' => JobTools::getActiveZoomJobs(),
				'analysis' => JobTools::getActiveAnalysisJobs() 
				);
			
		if( $user != NULL ) {
			
			$jobs['zoom'] = JobTools::filterJobsByUser( $jobs['zoom'], $user );
			$jobs['analysis'] = JobTools::filterJobsByUser( $jobs['analysis'], $user );
		}
		
		return $jobs;
	}

	static public function filterJobsByUser( $jobs, $user ) {
		
		$filteredJobs = array();
		
		foreach( $jobs as $job ) {
			
			if( $job->data['email'] == $user ) {
				array_push( $filteredJobs, $job );
			}
		}
		
		return $filteredJobs;
	}
	
	static public function getActiveZoomJobs() {
		$guids = JobTools::getJobGuidsFromFile( jobFile() );
		return JobTools::readJobs( $guids );
	}
	
	static public function getActiveAnalysisJobs() {
		$guids = JobTools::getJobGuidsFromFile( analysisJobMasterFile() );
		return JobTools::readAnalysisJobs( $guids );
	}
	
	static public function readJobs( $guids ) {
		
		$jobs = array();
		
		foreach( $guids as $guid ) {
			
			$job = NULL;
			
			try {
				$jobFile = jobFileG( $guid );
				$result = lockedFileRead( $jobFile, filesize( $jobFile ), 'r', false );
				
				if( $result['id'] == 0 ) {
					$job = new Job( $result['data'] );
					$job->disassemble( $result['data'] );
				}
				
			}
			catch (Exception $e) {
				$job = NULL;
			}
			
			if( isset( $job ) ) {
				array_push( $jobs, $job );
			}
		}
		
		return $jobs;		
	}
	
	static public function readAnalysisJobs( $guids ) {
		
		$jobs = array();
		
		foreach( $guids as $guid ) {
			
			$job = NULL;
			
			try {
				$jobFile = analysisJobFile( $guid );
				$result = lockedFileRead( $jobFile, filesize( $jobFile ), 'r', false );
				
				if( $result['id'] == 0 ) {
					$job = new AnalysisJob();
					$job->disassemble($result['data']);
					$job->data['md5'] = 'http://polzoomer.icr.ac.uk/analyses/analysis_out/' . $job->data['guid'] . '/nohup.out';
				}
				
			}
			catch (Exception $e) {
				$job = NULL;
			}
			
			if( isset( $job ) ) {
				array_push( $jobs, $job );
			}
		}
		
		return $jobs;		
	}
	
	static public function getJobGuidsFromFile( $filename ) {

		$activeJobs = array();
		
		$taskFile = new TaskFileManipulator( $filename );
		$contents = $taskFile->getContents();
		$jobs = preg_grep("/^#/i", $contents, PREG_GREP_INVERT);
		
		foreach( $jobs as $guid ) {
			
			$guid = trim( $guid );
			if( empty( $guid ) ) {
				continue;
			}
			
			array_push( $activeJobs, $guid );
		}
		
		return $activeJobs;
	}
}

?>
