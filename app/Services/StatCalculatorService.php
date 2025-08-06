<?php

namespace App\Services;

class StatCalculatorService
{
    /**
     * Calculate all stats for Feral Druid (WotLK 3.3.5a)
     */
    public function calculateStats(array $input): array
    {
        // WotLK 3.3.5a EXACT FORMULAS (Level 80)
        
        // Combat Rating Conversions
        $hit_percent = $input['hit'] / 32.79; // 32.79 Hit Rating = 1% Hit
        $expertise_percent = $input['expertise'] * 0.25; // 1 Expertise Point = 0.25% reduction
        $arp_percent = $input['arp'] / 13.99; // 13.99 ArP Rating = 1% Armor Penetration
        $crit_percent_from_rating = $input['crit_rating'] / 45.91; // 45.91 Crit Rating = 1% Crit
        
        // RESILIENCE - OFFICIAL WoWpedia 3.3.5a FORMULAS
        $resilience_percent = $input['resilience'] / 94.3; // 94.3 Resilience Rating = 1% Resilience (Level 80)
        $resilience_crit_reduction = $resilience_percent; // 1% Resilience = 1% Crit Reduction
        $resilience_pvp_damage_reduction = $resilience_percent * 2; // 1% Resilience = 2% PvP Damage Reduction
        $resilience_crit_damage_reduction = $resilience_percent * 2.2; // 1% Resilience = 2.2% Crit Damage Reduction
        
        // Primary Attribute Conversions
        $ap_from_str = $input['strength'] * 2; // 1 Str = 2 AP
        $ap_from_agi = $input['agility'] * 1; // 1 Agi = 1 AP for Feral Druids
        $total_ap = $input['base_ap'] + $ap_from_agi + $ap_from_str;
        
        // Stamina to Health
        $health_from_stamina = $input['stamina'] * 10; // 1 Sta = 10 Health at level 80
        
        // CRITICAL STRIKE - OFFICIAL WoWpedia Formula with Base Crit
        $base_crit_chance = 10.97; // Base crit chance for Feral Druids (5% + class constant)
        $crit_from_agi = $input['agility'] / 83.3; // 83.3 Agi = 1% Crit for Feral Druids (Level 80)
        $total_crit = $base_crit_chance + $crit_from_agi + $crit_percent_from_rating;
        
        // Agility to Armor
        $armor_from_agi = $input['agility'] * 2; // 1 Agi = 2 Armor
        
        return [
            'total_ap' => round($total_ap),
            'ap_from_str' => round($ap_from_str),
            'ap_from_agi' => round($ap_from_agi),
            'total_crit' => $total_crit,
            'base_crit_chance' => $base_crit_chance,
            'crit_from_agi' => $crit_from_agi,
            'crit_percent_from_rating' => $crit_percent_from_rating,
            'armor_from_agi' => round($armor_from_agi),
            'arp_percent' => $arp_percent,
            'hit_percent' => $hit_percent,
            'expertise_percent' => $expertise_percent,
            'resilience_percent' => $resilience_percent,
            'resilience_crit_reduction' => $resilience_crit_reduction,
            'resilience_pvp_damage_reduction' => $resilience_pvp_damage_reduction,
            'resilience_crit_damage_reduction' => $resilience_crit_damage_reduction,
            'health_from_stamina' => round($health_from_stamina)
        ];
    }
    
    /**
     * Validate input data
     */
    public function validateInput(array $input): array
    {
        $warnings = [];
        
        if ($input['resilience'] > 5000) {
            $warnings[] = "Warning: Resilience value seems very high. Make sure you're entering RESILIENCE rating, not armor value!";
        }
        
        if ($input['crit_rating'] > 0 && ($input['crit_rating'] / 45.91) < 5) {
            $warnings[] = "Note: Your crit rating seems low. Make sure you're entering the RATING value, not percentage.";
        }
        
        return $warnings;
    }
}