<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\StatCalculatorController;
use App\Services\StatCalculatorService;

// Simple dependency injection
$statCalculatorService = new StatCalculatorService();
$controller = new StatCalculatorController($statCalculatorService);

// Simple routing
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestUri === '/' || $requestUri === '/index.php') {
    if ($requestMethod === 'POST') {
        echo $controller->calculate();
    } else {
        echo $controller->index();
    }
} elseif ($requestUri === '/export' && $requestMethod === 'POST') {
    $controller->exportJson();
} else {
    // Handle static files
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath) && is_file($filePath)) {
        $mimeType = mime_content_type($filePath);
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
        exit;
    }
    
    // 404 Not Found
    http_response_code(404);
    echo '404 Not Found';
}