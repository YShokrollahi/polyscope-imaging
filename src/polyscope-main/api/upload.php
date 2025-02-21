<?php
// Set PHP configuration for large file uploads
ini_set('upload_max_filesize', '1024M'); // 1GB
ini_set('post_max_size', '1024M'); // 1GB
ini_set('memory_limit', '1024M'); // 1GB
ini_set('max_execution_time', 600); // 10 minutes
ini_set('max_input_time', 600); // 10 minutes

// Start session and include security files
session_start();
require_once '../auth/session_timeout.php';

// Set content type to JSON
header('Content-Type: application/json');

// Enable detailed error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/error.log');

// Debug logging function
function debug_log($message, $data = null) {
    error_log("DEBUG: " . $message);
    if ($data !== null) {
        error_log("DEBUG DATA: " . print_r($data, true));
    }
}

// Start processing
try {
    debug_log("Starting upload process");

    // Check authentication
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || empty($_SESSION['username'])) {
        throw new Exception('Authentication required');
    }

    $username = $_SESSION['username'];
    debug_log("User authenticated: " . $username);

    // Validate and sanitize upload directory
    $uploadDir = $_POST['directory'] ?? '';
    $uploadDir = str_replace('___SLASH___', '/', $uploadDir);
    debug_log("Upload directory: " . $uploadDir);

    if (empty($uploadDir)) {
        throw new Exception('No directory specified');
    }

    // Verify directory is within user's allowed space
    $userBaseDir = '/media/Users/' . $username;
    debug_log("User base directory: " . $userBaseDir);
    debug_log("Checking if upload dir starts with base dir");

    if (strpos($uploadDir, $userBaseDir) !== 0) {
        debug_log("Directory access denied", [
            'uploadDir' => $uploadDir,
            'userBaseDir' => $userBaseDir,
            'username' => $username
        ]);
        throw new Exception('Invalid directory access');
    }

    // Check if directory exists or can be created
    if (!file_exists($uploadDir)) {
        debug_log("Directory doesn't exist, attempting to create: " . $uploadDir);
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create upload directory: ' . $uploadDir);
        }
    }

    // Debug uploaded files
    debug_log("FILES array contents:", $_FILES);

    // Initialize response array
    $response = [
        'success' => false,
        'uploaded' => [],
        'failed' => [],
        'messages' => [],
        'debug' => [] // Additional debug info
    ];

    // Process uploaded files
    if (empty($_FILES['files'])) {
        throw new Exception('No files received');
    }

    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        try {
            $filename = $_FILES['files']['name'][$key];
            $filesize = $_FILES['files']['size'][$key];
            $error = $_FILES['files']['error'][$key];

            debug_log("Processing file:", [
                'filename' => $filename,
                'size' => $filesize,
                'tmp_name' => $tmp_name,
                'error' => $error
            ]);

            // Check for upload errors
            if ($error !== UPLOAD_ERR_OK) {
                throw new Exception('Upload error code: ' . $error);
            }

            // Validate file exists
            if (!is_uploaded_file($tmp_name)) {
                throw new Exception('Not a valid uploaded file');
            }

            // Create safe filename
            $safe_filename = preg_replace("/[^a-zA-Z0-9.]/", "_", $filename);
            $destination = $uploadDir . '/' . $safe_filename;

            debug_log("Moving file to destination:", [
                'from' => $tmp_name,
                'to' => $destination
            ]);

            // Attempt to move file
            if (!move_uploaded_file($tmp_name, $destination)) {
                $moveError = error_get_last();
                throw new Exception('Failed to move uploaded file: ' . ($moveError['message'] ?? 'Unknown error'));
            }

            // Set permissions
            chmod($destination, 0644);

            $response['uploaded'][] = [
                'name' => $safe_filename,
                'originalName' => $filename,
                'size' => $filesize
            ];

        } catch (Exception $e) {
            debug_log("Failed to process file: " . $filename, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $response['failed'][] = [
                'name' => $filename ?? 'unknown',
                'reason' => $e->getMessage()
            ];
            continue;
        }
    }

    // Set success flag and messages
    $response['success'] = !empty($response['uploaded']);
    $response['messages'][] = count($response['uploaded']) . ' files uploaded successfully';
    if (!empty($response['failed'])) {
        $response['messages'][] = count($response['failed']) . ' files failed';
    }

    // Add debug info
    $response['debug'] = [
        'uploadDir' => $uploadDir,
        'userBaseDir' => $userBaseDir,
        'username' => $username,
        'dirExists' => file_exists($uploadDir),
        'dirWritable' => is_writable($uploadDir)
    ];

} catch (Exception $e) {
    debug_log("Main process error: " . $e->getMessage(), [
        'trace' => $e->getTraceAsString()
    ]);

    $response = [
        'success' => false,
        'message' => $e->getMessage(),
        'uploaded' => [],
        'failed' => [],
        'debug' => [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ];
}

// Send response
debug_log("Sending response:", $response);
echo json_encode($response);