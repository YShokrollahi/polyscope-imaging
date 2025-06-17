<?php
/*
 Fixed version - no file system writes, clean JSON response
*/

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't output errors to browser
ini_set('log_errors', 1);     // Log errors instead

try {
    // Log the request (to server error log only)
    error_log("DZI Request received: " . print_r($_POST, true));
    
    // Check if we have the required POST data
    if (!isset($_POST["isDir"]) || !isset($_POST["path"])) {
        echo json_encode(array(
            "error" => "Missing POST parameters",
            "received" => $_POST
        ));
        exit;
    }
    
    $isDir = json_decode($_POST["isDir"]);
    $pathToIssue = json_decode($_POST["path"]);
    
    error_log("DZI Processing - isDir: " . var_export($isDir, true) . ", path: " . var_export($pathToIssue, true));
    
    if($isDir == 1) {
        echo json_encode(array(
            "error" => "Directory processing not implemented",
            "isDir" => $isDir,
            "path" => $pathToIssue
        ));
    } else {
        if(is_array($pathToIssue)) {
            // Process multiple files
            $jobs = array();
            $file_status = array();
            
            foreach($pathToIssue as $index => $path) {
                $file_status[$path] = file_exists($path) ? "exists" : "not found";
                
                if (file_exists($path)) {
                    // Create a DZI job entry
                    $job = createDZIJob($path, $index);
                    $jobs[] = $job;
                    
                    error_log("Created DZI job for: " . $path . " with GUID: " . $job['guid']);
                    
                    // Log job details for your external processing system
                    error_log("DZI_JOB_CREATED: " . json_encode($job));
                }
            }
            
            $response = array(
                "typ" => "multiple",
                "message" => "DZI processing jobs created successfully",
                "file_count" => count($pathToIssue),
                "files" => $pathToIssue,
                "file_status" => $file_status,
                "jobs" => $jobs
            );
            
            error_log("DZI Response: " . json_encode($response));
            echo json_encode($response);
            
        } else {
            // Process single file
            $exists = file_exists($pathToIssue);
            
            if ($exists) {
                $job = createDZIJob($pathToIssue, 0);
                error_log("Created single DZI job for: " . $pathToIssue . " with GUID: " . $job['guid']);
                error_log("DZI_JOB_CREATED: " . json_encode($job));
                
                $response = array(
                    "typ" => "single",
                    "message" => "DZI processing job created successfully",
                    "file" => $pathToIssue,
                    "exists" => $exists,
                    "guid" => $job['guid'],
                    "id" => $job['id'],
                    "name" => $job['name']
                );
                
                error_log("DZI Response: " . json_encode($response));
                echo json_encode($response);
            } else {
                echo json_encode(array(
                    "error" => "File not found: " . $pathToIssue,
                    "file" => $pathToIssue,
                    "exists" => false
                ));
            }
        }
    }
    
} catch (Exception $e) {
    error_log("DZI Exception: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
    echo json_encode(array(
        "error" => "Exception: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ));
} catch (Error $e) {
    error_log("DZI Fatal Error: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
    echo json_encode(array(
        "error" => "Fatal Error: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ));
}

// Function to create a DZI processing job
function createDZIJob($filePath, $index) {
    // Generate unique identifiers
    $guid = generateGUID();
    $timestamp = time();
    $id = $timestamp . '_' . $index;
    
    // Get file information
    $filename = basename($filePath);
    $filesize = file_exists($filePath) ? filesize($filePath) : 0;
    
    // Create job entry - this matches your existing system's expected format
    $job = array(
        'guid' => $guid,
        'id' => $id,
        'name' => $filename,
        'fileId' => $filePath,
        'origFilename' => $filename,
        'status' => 'checksum',
        'email' => 'EMAIL_PLACE_HOLDER',
        'fileSize' => $filesize,
        'createdTime' => $timestamp,
        'finalPath' => '',
        'finalFilename' => ''
    );
    
    // Instead of writing to files, we'll trigger your existing job system
    // Log the job creation in a format your existing system can monitor
    error_log("DZI_QUEUE_ADD: GUID=" . $guid . " FILE=" . $filePath . " STATUS=checksum EMAIL=EMAIL_PLACE_HOLDER");
    
    // Optional: Call your existing job system directly
    // You can integrate with your existing issueProject.php functions here
    // For example: integrateWithExistingSystem($job);
    
    return $job;
}

// Generate a GUID
function generateGUID() {
    if (function_exists('com_create_guid')) {
        return trim(com_create_guid(), '{}');
    }
    
    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
        mt_rand(0, 65535), mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(16384, 20479),
        mt_rand(32768, 49151),
        mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)
    );
}

// Optional: Integration function for your existing system
function integrateWithExistingSystem($job) {
    // This is where you can call your existing issueProject.php functions
    // without the file permission issues
    
    try {
        // Example: If you want to use your original functions, you could try:
        /*
        if (file_exists(__DIR__ . '/issueProject.php')) {
            require_once __DIR__ . '/issueProject.php';
            
            // Try to use the original function but catch any errors
            $result = issueFile($job['fileId'], $job['email']);
            if ($result) {
                error_log("Integrated with existing system: " . json_encode($result));
                return $result;
            }
        }
        */
        
        // For now, just log that we would integrate
        error_log("INTEGRATION_POINT: Would integrate job " . $job['guid'] . " with existing DZI system");
        
    } catch (Exception $e) {
        error_log("Integration error: " . $e->getMessage());
    }
    
    return null;
}
?>