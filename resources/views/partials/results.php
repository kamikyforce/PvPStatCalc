<div class="alert alert-success">
    Calculations performed using exact WotLK 3.3.5a formulas (level 80)!
</div>

<?php if (isset($warnings) && !empty($warnings)): ?>
    <?php foreach ($warnings as $warning): ?>
        <div class="alert alert-warning">
            <?= htmlspecialchars($warning) ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="result">
    <h3>Calculated Statistics (WotLK 3.3.5a)</h3>
    <div class="stats-grid">
        <?php 
        $statItems = [
            ['label' => 'Total Attack Power', 'value' => number_format($stats['total_ap']), 'tooltip' => "Base: {$input['base_ap']} + AGI: {$stats['ap_from_agi']} + STR: {$stats['ap_from_str']}<br>14 AP = 1 DPS"],
            ['label' => 'Total Critical Chance', 'value' => number_format($stats['total_crit'], 2) . '%', 'tooltip' => "Base: " . number_format($stats['base_crit_chance'], 2) . "% + AGI: " . number_format($stats['crit_from_agi'], 2) . "% + Rating: " . number_format($stats['crit_percent_from_rating'], 2) . "%<br>Critical hits deal 200% damage"],
            ['label' => 'Crit from Agility', 'value' => number_format($stats['crit_from_agi'], 2) . '%', 'tooltip' => "{$input['agility']} AGI ÷ 83.3 = " . number_format($stats['crit_from_agi'], 2) . "%<br>Feral Druid specific formula"],
            ['label' => 'Armor from Agility', 'value' => number_format($stats['armor_from_agi']), 'tooltip' => "{$input['agility']} AGI × 2 = {$stats['armor_from_agi']} Armor<br>Reduces physical damage taken"],
            ['label' => 'Armor Penetration', 'value' => number_format($stats['arp_percent'], 2) . '%', 'tooltip' => "{$input['arp']} Rating ÷ 13.99 = " . number_format($stats['arp_percent'], 2) . "%<br>Ignores target armor"],
            ['label' => 'Hit Chance', 'value' => number_format($stats['hit_percent'], 2) . '%', 'tooltip' => "{$input['hit']} Rating ÷ 32.79 = " . number_format($stats['hit_percent'], 2) . "%<br>Cap: 8% vs raid bosses, 5% vs players"],
            ['label' => 'Expertise', 'value' => number_format($stats['expertise_percent'], 2) . '%', 'tooltip' => "{$input['expertise']} Expertise Points × 0.25 = " . number_format($stats['expertise_percent'], 2) . "%<br>Reduces dodge/parry chance"],
            ['label' => 'Crit Reduction (Resilience)', 'value' => number_format($stats['resilience_crit_reduction'], 2) . '%', 'tooltip' => "{$input['resilience']} Rating ÷ 94.3 = " . number_format($stats['resilience_crit_reduction'], 2) . "%<br>Reduces chance to receive critical hits"],
            ['label' => 'PvP Damage Reduction', 'value' => number_format($stats['resilience_pvp_damage_reduction'], 2) . '%', 'tooltip' => "{$input['resilience']} Rating ÷ 47.15 = " . number_format($stats['resilience_pvp_damage_reduction'], 2) . "%<br>Reduces damage from players"],
            ['label' => 'Health from Stamina', 'value' => number_format($stats['health_from_stamina']), 'tooltip' => "{$input['stamina']} STA × 10 = {$stats['health_from_stamina']} Health<br>Total health points from stamina"]
        ];
        
        foreach ($statItems as $item): ?>
            <div class="stat-item tooltip">
                <div class="stat-label"><?= $item['label'] ?></div>
                <div class="stat-value"><?= $item['value'] ?></div>
                <span class="tooltiptext"><?= $item['tooltip'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>