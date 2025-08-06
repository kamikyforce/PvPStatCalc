<?php
require_once __DIR__ . '/../../app/Services/VisitorCounterService.php';

use App\Services\VisitorCounterService;

session_start();

// Set headers for Server-Sent Events
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Prevent timeout
set_time_limit(0);
ini_set('max_execution_time', 0);

$visitorService = new VisitorCounterService();

// Function to send SSE data
function sendSSE($data, $event = 'message') {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

// Send initial data
$visitorService->updateOnlineStatus();
$initialData = [
    'total_visitors' => $visitorService->getTotalVisitors(),
    'total_online' => $visitorService->getTotalOnlineVisitors(),
    'visitor_stats' => $visitorService->getVisitorStats(),
    'online_countries' => $visitorService->getOnlineVisitorsByCountry(),
    'timestamp' => time()
];
sendSSE($initialData, 'visitor_update');

// Keep connection alive and send updates every 3 seconds
while (true) {
    // Update online status
    $visitorService->updateOnlineStatus();
    
    $data = [
        'total_visitors' => $visitorService->getTotalVisitors(),
        'total_online' => $visitorService->getTotalOnlineVisitors(),
        'visitor_stats' => $visitorService->getVisitorStats(),
        'online_countries' => $visitorService->getOnlineVisitorsByCountry(),
        'timestamp' => time()
    ];
    
    sendSSE($data, 'visitor_update');
    
    // Wait 3 seconds before next update
    sleep(3);
    
    // Check if client disconnected
    if (connection_aborted()) {
        break;
    }
}
?>