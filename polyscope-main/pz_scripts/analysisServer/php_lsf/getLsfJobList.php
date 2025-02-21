<?php

require_once 'logging.php';

function getLsfJobList() {

   $command = 'bjobs -l 2>&1';
   
   $output = executeSync( $command );

   return $output;
}

function jobListToString( $jobList = null ) {
   $jobList = ($jobList == null ? getLsfJobList() : $jobList);
   return implode($jobList, ' ');
}

function isJobInQueue( $guid, $jobList = null) {
   
   $jobList = jobListToString($jobList);
   return strpos($jobList, $guid) !== FALSE;
}

?>

