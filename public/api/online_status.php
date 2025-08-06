<?php
require_once __DIR__ . '/../../app/Services/VisitorCounterService.php';

use App\Services\VisitorCounterService;

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $visitorService = new VisitorCounterService();
    $visitorService->updateOnlineStatus();
    
    $response = [
        'total_online' => $visitorService->getTotalOnlineVisitors(),
        'online_countries' => $visitorService->getOnlineVisitorsByCountry(),
        'timestamp' => time(),
        'status' => 'success'
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to update online status',
        'message' => $e->getMessage(),
        'status' => 'error'
    ]);
}
?>