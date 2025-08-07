<?php

namespace App\Controllers;

use App\Services\StatCalculatorService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Support\MessageBag;

class StatCalculatorController
{
    private $statCalculator;

    public function __construct(StatCalculatorService $statCalculator)
    {
        $this->statCalculator = $statCalculator;
    }

    public function index()
    {
        // Get and clear session data
        $stats = $_SESSION['stats'] ?? null;
        $input = $_SESSION['input'] ?? [];
        $message = $_SESSION['message'] ?? null;
        $error = $_SESSION['error'] ?? null;
        $sessionErrors = $_SESSION['errors'] ?? [];
        
        // Clear session data
        unset($_SESSION['stats'], $_SESSION['input'], $_SESSION['message'], $_SESSION['error'], $_SESSION['errors']);
        
        // Create errors object for Blade
        $errors = new ViewErrorBag();
        if (!empty($sessionErrors)) {
            $messageBag = new MessageBag();
            foreach ($sessionErrors as $field => $fieldErrors) {
                foreach ($fieldErrors as $errorMessage) {
                    $messageBag->add($field, $errorMessage);
                }
            }
            $errors->put('default', $messageBag);
        }
        
        return View::make('calculator', compact('stats', 'input', 'message', 'error', 'errors'));
    }

    public function processCalculation()
    {
        try {
            $data = $_POST;
            
            // Validate input data
            $validation = $this->validateInput($data);
            
            if (!$validation['valid']) {
                $_SESSION['errors'] = $validation['errors'];
                $_SESSION['error'] = 'Please fix the validation errors.';
                header('Location: /');
                exit;
            }
            
            // Calculate stats
            $stats = $this->statCalculator->calculateStats($validation['data']);
            
            // Check for warnings
            $warnings = $this->checkForWarnings($validation['data'], $stats);
            
            // Store results in session
            $_SESSION['stats'] = $stats;
            $_SESSION['input'] = $validation['data'];
            
            if (!empty($warnings)) {
                $_SESSION['message'] = 'Calculations completed with warnings: ' . implode(' ', $warnings);
            } else {
                $_SESSION['message'] = 'Stats calculated successfully!';
            }
            
            header('Location: /');
            exit;
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Calculation failed: ' . $e->getMessage();
            header('Location: /');
            exit;
        }
    }

    private function validateInput(array $data): array
    {
        $errors = [];
        $validatedData = [];
        
        $rules = [
            'agility' => ['required' => true, 'numeric' => true, 'min' => 0, 'max' => 9999],
            'strength' => ['required' => true, 'numeric' => true, 'min' => 0, 'max' => 9999],
            'base_ap' => ['required' => true, 'numeric' => true, 'min' => 0, 'max' => 9999],
            'arp' => ['required' => true, 'numeric' => true, 'min' => 0, 'max' => 9999],
            'resilience' => ['required' => true, 'numeric' => true, 'min' => 0, 'max' => 9999],
            'stamina' => ['required' => true, 'numeric' => true, 'min' => 0, 'max' => 9999],
            'hit' => ['required' => true, 'numeric' => true, 'min' => 0, 'max' => 9999],
            'expertise' => ['required' => true, 'numeric' => true, 'min' => 0, 'max' => 9999],
            'crit_rating' => ['required' => true, 'numeric' => true, 'min' => 0, 'max' => 9999],
        ];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            if ($fieldRules['required'] && (is_null($value) || $value === '')) {
                $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' is required.';
                continue;
            }
            
            if (!is_null($value) && $value !== '') {
                if ($fieldRules['numeric'] && !is_numeric($value)) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must be a number.';
                    continue;
                }
                
                $numValue = (float)$value;
                
                if (isset($fieldRules['min']) && $numValue < $fieldRules['min']) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must be at least ' . $fieldRules['min'] . '.';
                }
                
                if (isset($fieldRules['max']) && $numValue > $fieldRules['max']) {
                    $errors[$field][] = ucfirst(str_replace('_', ' ', $field)) . ' must not exceed ' . $fieldRules['max'] . '.';
                }
                
                if (empty($errors[$field])) {
                    $validatedData[$field] = $numValue;
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $validatedData
        ];
    }
    
    private function checkForWarnings(array $input, array $stats): array
    {
        $warnings = [];
        
        // Hit cap warnings
        if ($stats['hit_chance_pve'] > 8.0) {
            $warnings[] = "Hit chance vs PvE targets ({$stats['hit_chance_pve']}%) exceeds the 8% cap. Consider reducing Hit Rating.";
        }
        
        if ($stats['hit_chance_pvp'] > 5.0) {
            $warnings[] = "Hit chance vs PvP targets ({$stats['hit_chance_pvp']}%) exceeds the 5% cap. Consider reducing Hit Rating.";
        }
        
        // Expertise warnings
        if ($input['expertise'] > 26) {
            $warnings[] = "Expertise ({$input['expertise']}) exceeds the 26 point cap (6.5%). Excess expertise provides no benefit.";
        }
        
        // Armor penetration warnings
        if ($stats['armor_pen_percent'] > 100) {
            $warnings[] = "Armor Penetration ({$stats['armor_pen_percent']}%) exceeds 100%. Consider redistributing stats.";
        }
        
        // Low resilience warning for PvP
        if ($input['resilience'] < 500) {
            $warnings[] = "Low Resilience ({$input['resilience']}) may result in high vulnerability to critical strikes in PvP.";
        }
        
        return $warnings;
    }
    
    public function export()
    {
        try {
            $data = $_POST;
            
            // Validate export data
            $validation = $this->validateInput($data);
            
            if (!$validation['valid']) {
                $_SESSION['error'] = 'Export failed: Invalid data provided.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            // Calculate stats for export
            $stats = $this->statCalculator->calculateStats($validation['data']);
            
            $exportData = [
                'character_stats' => $validation['data'],
                'calculated_stats' => $stats,
                'export_date' => date('Y-m-d H:i:s'),
                'calculator_version' => '1.0.0'
            ];
            
            $filename = 'feral_druid_stats_' . date('Y-m-d_H-i-s') . '.json';
            
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo json_encode($exportData, JSON_PRETTY_PRINT);
            exit;
                
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Export failed: ' . $e->getMessage();
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
}