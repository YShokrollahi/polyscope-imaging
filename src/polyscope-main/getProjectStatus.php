<?php
/*
 Desc: Functions to retrieve the current status of all open projects
 Author: Sebastian Schmittner
 Date: 2014.09.07 20:37:27 (+02:00)
 Last Author: Sebastian Schmittner
 Last Date: 2015.01.31 09:20:02 (+01:00)
 Version: 0.0.4
*/
class FileContentChangedException extends Exception {};
class WrongArgumentCountException extends Exception {};
require_once __DIR__ . '/logging.php';
require_once __DIR__ . '/taskFileManipulator.php';
require_once __DIR__ . '/jobRepresentation.php';

// Start session to access the current user
session_start();
$currentUsername = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

echo json_encode( retrieveProjectStatus($currentUsername) );

/////////////////////////////////
function retrieveProjectStatus($username) {
    $jobFile = jobFile();
    $taskFileHandler = null;
    $contents = null;
    try {
        $taskFileHandler = new taskFileManipulator($jobFile);
        $contents = $taskFileHandler->getContents();
    }
    catch (Exception $e) {
        // Handle exception
    }
    
    $valid = false;
    $jobs = array();
    $commentsLines = array();
    
    if($contents !== null) {
        $commentsLines = preg_grep("/^#/i", $contents);
        $jobLines = preg_grep("/^#/i", $contents, PREG_GREP_INVERT);
        
        foreach($jobLines as $entry) {
            $entry = trim($entry);
            if(empty($entry)) {
                continue;
            }
            
            $localJob = null;
            try {
                $file = jobFileG($entry);
                $content = lockedFileRead($file, filesize($file), 'r', true);
                $localJob = new Job($content['data']);
                
                // Only include jobs where the email contains the username
                // This assumes emails are in the format username@mdanderson.org
                if ($username != 'Guest' && strpos($localJob->data['email'], $username.'@') === 0) {
                    array_push($jobs, $localJob);
                } elseif ($username == 'Guest') {
                    // For Guest users, only show jobs with no email or placeholder email
                    if (empty($localJob->data['email']) || $localJob->data['email'] == 'EMAIL_PLACE_HOLDER') {
                        array_push($jobs, $localJob);
                    }
                }
            }
            catch (Exception $e) {
                $localJob = null;
            }
        }
        $valid = true;
    }
    
    return array(
        'valid' => $valid,
        'jobs' => $jobs,
        'comments' => $commentsLines
    );
};
?>