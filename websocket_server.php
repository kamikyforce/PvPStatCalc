#!/usr/bin/env php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/Services/VisitorCounterService.php';
require_once __DIR__ . '/app/Services/WebSocketServer.php';

use App\Services\WebSocketServer;

// Handle CLI arguments
$port = isset($argv[1]) ? (int)$argv[1] : 8080;

echo "\n";
echo "🚀 ===== PvP Calculator WebSocket Server =====\n";
echo "📡 Starting WebSocket server on port {$port}...\n";
echo "🌍 Real-time visitor tracking enabled\n";
echo "⚡ Advanced presence system active\n";
echo "\n";

try {
    $server = new WebSocketServer();
    $server->startServer($port);
} catch (Exception $e) {
    echo "❌ Server error: {$e->getMessage()}\n";
    exit(1);
}