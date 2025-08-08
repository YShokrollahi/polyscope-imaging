<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$ai_base_url = 'http://rapuplabgpu15:8000';

// Get the endpoint from the request
$endpoint = $_GET['endpoint'] ?? '';
$url = $ai_base_url . '/' . ltrim($endpoint, '/');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = @file_get_contents($url);
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // For POST requests with file uploads, we need to handle FormData
    if (isset($_FILES['file'])) {
        // Handle file upload to AI service
        $postFields = array();
        
        // Add the uploaded file
        $postFields['file'] = new CURLFile($_FILES['file']['tmp_name'], $_FILES['file']['type'], $_FILES['file']['name']);
        
        // Add other form fields
        foreach ($_POST as $key => $value) {
            $postFields[$key] = $value;
        }
        
        // Use cURL for file uploads
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $httpCode >= 400) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to connect to AI service']);
            exit;
        }
    } else {
        // Handle regular JSON POST
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => file_get_contents('php://input')
            ]
        ]);
        $response = @file_get_contents($url, false, $context);
    }
}

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect to AI service']);
} else {
    echo $response;
}
?>