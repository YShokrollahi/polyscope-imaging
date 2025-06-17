<?php
// Create this as check_functions.php to see what's missing

header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Checking DZI System Functions ===\n\n";

// Try to load all dependencies
$files_to_check = [
    'tools.php',
    'addLineToFile.php', 
    'guid.php',
    'lockedFileAccess.php',
    'logging.php',
    'md5chk.php',
    'fileFormats.php',
    'issueProject.php'
];

echo "1. Checking file existence:\n";
foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "✓ $file exists\n";
        try {
            require_once $path;
            echo "  ✓ Loaded successfully\n";
        } catch (Exception $e) {
            echo "  ✗ Error loading: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ $file missing\n";
    }
}

echo "\n2. Checking required functions:\n";
$required_functions = [
    'uploadFolder',
    'uniqueId', 
    'jobFile',
    'jobFileG',
    'jobCounter',
    'atomicCounterIncrement',
    'addLineToFile',
    'GUID',
    'md5chk',
    'lockedFileRead',
    'executeSync',
    'issueFile',
    'issueFiles',
    'createJobEntry',
    'addJob'
];

foreach ($required_functions as $func) {
    if (function_exists($func)) {
        echo "✓ $func() exists\n";
        
        // Test some safe functions
        if ($func === 'uniqueId') {
            try {
                $result = uniqueId();
                echo "  Sample output: $result\n";
            } catch (Exception $e) {
                echo "  ✗ Error calling: " . $e->getMessage() . "\n";
            }
        }
        
        if ($func === 'uploadFolder') {
            try {
                $result = uploadFolder();
                echo "  Upload folder: $result\n";
                if (!is_dir($result)) {
                    echo "  ⚠ Warning: Directory doesn't exist\n";
                }
            } catch (Exception $e) {
                echo "  ✗ Error calling: " . $e->getMessage() . "\n";
            }
        }
        
    } else {
        echo "✗ $func() missing\n";
    }
}

echo "\n3. Testing job system paths:\n";
if (function_exists('jobFile')) {
    try {
        $jobFile = jobFile();
        echo "Job file path: $jobFile\n";
        if (file_exists($jobFile)) {
            echo "✓ Job file exists\n";
        } else {
            echo "⚠ Job file doesn't exist (normal for new system)\n";
        }
    } catch (Exception $e) {
        echo "✗ Error getting job file: " . $e->getMessage() . "\n";
    }
}

echo "\n4. Testing with sample file:\n";
$test_file = '/media/Users/admin_22/modpathol_Hero-c406775143eb40737edb1d9f60c1b1e8.jpg';
if (file_exists($test_file)) {
    echo "✓ Test file exists: $test_file\n";
    
    if (function_exists('issueFile')) {
        try {
            echo "Attempting issueFile()...\n";
            $result = issueFile($test_file);
            echo "✓ issueFile succeeded\n";
            echo "Result type: " . gettype($result) . "\n";
            if (is_array($result)) {
                echo "Result keys: " . implode(', ', array_keys($result)) . "\n";
            }
        } catch (Exception $e) {
            echo "✗ issueFile failed: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "✗ Test file not found\n";
}

echo "\n=== Check completed ===\n";
?>