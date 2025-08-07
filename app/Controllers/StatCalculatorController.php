<?php

namespace App\Controllers;

use App\Services\StatCalculatorService;
use App\Services\VisitorCounterService;

class StatCalculatorController
{
    private StatCalculatorService $statCalculator;
    private VisitorCounterService $visitorCounter;
    
    public function __construct(StatCalculatorService $statCalculator)
    {
        $this->statCalculator = $statCalculator;
        $this->visitorCounter = new VisitorCounterService();
    }
    
    public function index(): void
    {
        $visitorService = new VisitorCounterService();
        
        // Record visitor only (no online status)
        $visitor_stats = $visitorService->recordVisitor();
        
        $total_visitors = $visitorService->getTotalVisitors();
        
        require_once __DIR__ . '/../../resources/views/calculator.php';
    }
    
    public function processCalculation()
    {
        try {
            $input = $this->validateAndSanitizeInput();
            $stats = $this->statCalculator->calculateStats($input);
            $warnings = $this->statCalculator->validateInput($input);
            
            // Store results in session
            $_SESSION['calculation_results'] = [
                'stats' => $stats,
                'warnings' => $warnings,
                'input' => $input
            ];
            
        } catch (\Exception $e) {
            // Store error in session
            $_SESSION['calculation_results'] = [
                'error' => $e->getMessage()
            ];
        }
        
        // Redirect to GET to prevent resubmission
        header('Location: /', true, 303);
        exit;
    }
    
    public function calculate()
    {
        // This method is now deprecated, keeping for compatibility
        return $this->processCalculation();
    }
    
    public function exportJson()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        try {
            $input = $this->validateAndSanitizeInput();
            $stats = $this->statCalculator->calculateStats($input);
            
            // Predefined JSON structure for character build export
            $exportData = [
                'metadata' => [
                    'version' => '1.0',
                    'game_version' => 'WotLK 3.3.5a',
                    'class' => 'Druid',
                    'spec' => 'Feral',
                    'calculator' => 'PvP Stat Calculator',
                    'exported_at' => date('Y-m-d H:i:s'),
                    'timestamp' => time()
                ],
                'character' => [
                    'level' => 80,
                    'class' => 'Druid',
                    'specialization' => 'Feral',
                    'race' => null // Can be extended later
                ],
                'base_stats' => [
                    'agility' => (int)$input['agility'],
                    'strength' => (int)$input['strength'],
                    'stamina' => (int)$input['stamina'],
                    'base_attack_power' => (int)$input['base_ap']
                ],
                'combat_ratings' => [
                    'hit_rating' => (int)$input['hit'],
                    'critical_strike_rating' => (int)$input['crit_rating'],
                    'armor_penetration_rating' => (int)$input['arp'],
                    'resilience_rating' => (int)$input['resilience'],
                    'expertise_points' => (int)$input['expertise']
                ],
                'calculated_stats' => [
                    'total_attack_power' => (int)$stats['total_ap'],
                    'attack_power_breakdown' => [
                        'base' => (int)$input['base_ap'],
                        'from_agility' => (int)$stats['ap_from_agi'],
                        'from_strength' => (int)$stats['ap_from_str']
                    ],
                    'critical_strike' => [
                        'total_percent' => round($stats['total_crit'], 2),
                        'base_percent' => round($stats['base_crit_chance'], 2),
                        'from_agility_percent' => round($stats['crit_from_agi'], 2),
                        'from_rating_percent' => round($stats['crit_percent_from_rating'], 2)
                    ],
                    'armor_from_agility' => (int)$stats['armor_from_agi'],
                    'armor_penetration_percent' => round($stats['arp_percent'], 2),
                    'hit_chance_percent' => round($stats['hit_percent'], 2),
                    'expertise_percent' => round($stats['expertise_percent'], 2),
                    'health_from_stamina' => (int)$stats['health_from_stamina'],
                    'resilience' => [
                        'crit_reduction_percent' => round($stats['resilience_crit_reduction'], 2),
                        'pvp_damage_reduction_percent' => round($stats['resilience_pvp_damage_reduction'], 2),
                        'crit_damage_reduction_percent' => round($stats['resilience_crit_damage_reduction'], 2)
                    ]
                ],
                'formulas_used' => [
                    'agility_to_ap' => '1 AGI = 1 AP',
                    'strength_to_ap' => '1 STR = 2 AP',
                    'agility_to_crit' => '83.3 AGI = 1% Crit',
                    'agility_to_armor' => '1 AGI = 2 Armor',
                    'stamina_to_health' => '1 STA = 10 Health',
                    'hit_rating_conversion' => '32.79 Rating = 1% Hit',
                    'crit_rating_conversion' => '45.91 Rating = 1% Crit',
                    'arp_rating_conversion' => '13.99 Rating = 1% ArP',
                    'resilience_conversion' => '94.3 Rating = 1% Resilience',
                    'expertise_conversion' => '1 Point = 0.25% Reduction'
                ]
            ];
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="feral_druid_build_' . date('Y-m-d_H-i-s') . '.json"');
            echo json_encode($exportData, JSON_PRETTY_PRINT);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    private function validateAndSanitizeInput(): array
    {
        $fields = ['agility', 'strength', 'base_ap', 'arp', 'resilience', 'stamina', 'hit', 'expertise', 'crit_rating'];
        $input = [];
        
        foreach ($fields as $field) {
            $value = filter_input(INPUT_POST, $field, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => $field === 'base_ap' ? 99999 : ($field === 'expertise' ? 99 : 9999)]
            ]);
            
            if ($value === false) {
                throw new \Exception("Invalid input for {$field}. Please check your entries.");
            }
            
            $input[$field] = $value;
        }
        
        return $input;
    }
    
    private function renderView(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        include __DIR__ . "/../../resources/views/{$view}.php";
        return ob_get_clean();
    }
}