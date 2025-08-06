<?php

use App\Services\VisitorCounterService;

class MacrosController {
    
    public function index() {
        // Dados do contador de visitantes
        $visitorService = new VisitorCounterService();
        $total_visitors = $visitorService->getTotalVisitors();
        $visitor_stats = $visitorService->getVisitorStats();
        
        include __DIR__ . '/../../resources/views/macros.php';
    }
}