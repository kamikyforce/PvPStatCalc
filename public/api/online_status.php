<?php
require_once __DIR__ . '/../../app/Services/VisitorCounterService.php';

use App\Services\VisitorCounterService;

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$visitorService = new VisitorCounterService();
$visitorService->updateOnlineStatus();

echo json_encode([
    'total_online' => $visitorService->getTotalOnlineVisitors(),
    'online_countries' => $visitorService->getOnlineVisitorsByCountry()
]);
?>