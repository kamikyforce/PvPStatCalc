<?php
require_once __DIR__ . '/../../app/Services/VisitorCounterService.php';

use App\Services\VisitorCounterService;

// Start session before any output
session_start();

// Set proper SSE headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Cache-Control, Content-Type');
header('X-Accel-Buffering: no'); // Disable nginx buffering

// Prevent timeout and buffering
set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('output_buffering', 0);
if (ob_get_level()) ob_end_clean();

// Function to send SSE data
function sendSSE($data, $event = 'message') {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

// Function to send heartbeat
function sendHeartbeat() {
    echo "event: heartbeat\n";
    echo "data: " . json_encode(['timestamp' => time()]) . "\n\n";
    if (ob_get_level()) ob_flush();
    flush();
}

try {
    $visitorService = new VisitorCounterService();
    
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
    
    $lastUpdate = time();
    $heartbeatInterval = 30; // Send heartbeat every 30 seconds
    $updateInterval = 5; // Update data every 5 seconds
    
    // Keep connection alive with proper error handling
    while (true) {
        $currentTime = time();
        
        // Check if client disconnected
        if (connection_aborted()) {
            break;
        }
        
        // Send heartbeat
        if (($currentTime - $lastUpdate) >= $heartbeatInterval) {
            sendHeartbeat();
            $lastUpdate = $currentTime;
        }
        
        // Update visitor data
        if (($currentTime - $lastUpdate) >= $updateInterval) {
            $visitorService->updateOnlineStatus();
            
            $data = [
                'total_visitors' => $visitorService->getTotalVisitors(),
                'total_online' => $visitorService->getTotalOnlineVisitors(),
                'visitor_stats' => $visitorService->getVisitorStats(),
                'online_countries' => $visitorService->getOnlineVisitorsByCountry(),
                'timestamp' => $currentTime
            ];
            
            sendSSE($data, 'visitor_update');
            $lastUpdate = $currentTime;
        }
        
        // Small sleep to prevent excessive CPU usage
        usleep(500000); // 0.5 seconds
    }
} catch (Exception $e) {
    error_log('SSE Error: ' . $e->getMessage());
    sendSSE(['error' => 'Connection error'], 'error');
}
?>