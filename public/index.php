<?php
// Production error handling
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start session for POST-Redirect-GET pattern
session_start();

// Autoloader for classes
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $file = __DIR__ . '/../' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Get request info
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Handle static files with proper MIME types
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath) && is_file($filePath)) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
        header('Cache-Control: public, max-age=86400');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
        readfile($filePath);
        exit;
    }
}

// Application routing
try {
    require_once __DIR__ . '/../app/Services/StatCalculatorService.php';
    require_once __DIR__ . '/../app/Controllers/StatCalculatorController.php';
    require_once __DIR__ . '/../app/Controllers/MacrosController.php';
    
    $service = new \App\Services\StatCalculatorService();
    $controller = new \App\Controllers\StatCalculatorController($service);
    
    if ($requestUri === '/' || $requestUri === '/index.php') {
        if ($requestMethod === 'POST') {
            // Process POST and redirect to GET (POST-Redirect-GET pattern)
            $controller->processCalculation();
        } else {
            // Show form or results via GET
            echo $controller->index();
        }
    } elseif ($requestUri === '/macros') {
        // Página simples de macros
        $macrosController = new MacrosController();
        $macrosController->index();
    } elseif ($requestUri === '/export' && $requestMethod === 'POST') {
        $controller->exportJson();
    } else {
        http_response_code(404);
        echo '404 - Page Not Found';
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('Application Error: ' . $e->getMessage());
    echo 'Application temporarily unavailable. Please try again later.';
}
?>