<form method="POST" action="/" id="statForm">
    <div class="form-grid">
        <?php 
        $fields = [
            'agility' => [
                'label' => 'Agility', 
                'tooltip' => '<strong>Agility (WotLK 3.3.5a)</strong><br><div class="tooltip-formula">• 1 AGI = 1 Attack Power</div><div class="tooltip-formula">• ~63.5 AGI = 1% Crit</div><div class="tooltip-formula">• 1 AGI = 2 Armor</div><div class="tooltip-effect">For Feral Druids, agility is the primary stat that increases damage, crit chance, and armor simultaneously.</div>',
                'placeholder' => 'e.g. 2003'
            ],
            'strength' => [
                'label' => 'Strength', 
                'tooltip' => '<strong>Strength (WotLK 3.3.5a)</strong><br><div class="tooltip-formula">• 1 STR = 2 Attack Power</div><div class="tooltip-effect">For Feral Druids, strength provides additional attack power but is secondary compared to agility.</div>',
                'placeholder' => 'e.g. 95'
            ],
            'base_ap' => [
                'label' => 'Attack Power (base)', 
                'tooltip' => '<strong>Base Attack Power</strong><br><div class="tooltip-formula">• 14 AP = 1 DPS</div><div class="tooltip-effect">The character\'s base attack power before attribute bonuses. Directly increases physical attack damage.</div>',
                'placeholder' => 'e.g. 2294'
            ],
            'arp' => [
                'label' => 'Armor Penetration Rating', 
                'tooltip' => '<strong>Armor Penetration (WotLK 3.3.5a)</strong><br><div class="tooltip-formula">• 13.99 Rating = 1% Penetration</div><div class="tooltip-effect">Ignores a percentage of the target\'s armor. Very effective against high-armor targets. After patch 3.1, effectiveness increased by 25%.</div>',
                'placeholder' => 'e.g. 415'
            ],
            'resilience' => [
                'label' => 'Resilience Rating', 
                'tooltip' => '<strong>Resilience (WotLK 3.3.5a)</strong><br><div class="tooltip-formula">• 81.97 Rating = 1% Crit Reduction</div><div class="tooltip-effect">Reduces chance to receive critical hits, reduces critical hit damage, and provides additional damage reduction against players. Essential for PvP. <strong>Note:</strong> This is NOT your armor value!</div>', 
                'placeholder' => 'e.g. 1073'
            ],
            'stamina' => [
                'label' => 'Stamina', 
                'tooltip' => '<strong>Stamina (WotLK 3.3.5a)</strong><br><div class="tooltip-formula">• 1 STA = 10 Health (level 80)</div><div class="tooltip-effect">Increases maximum health points. Essential for survivability in both PvP and PvE.</div>',
                'placeholder' => 'e.g. 1895'
            ],
            'hit' => [
                'label' => 'Hit Rating', 
                'tooltip' => '<strong>Hit Rating (WotLK 3.3.5a)</strong><br><div class="tooltip-formula">• 32.79 Rating = 1% Physical Hit</div><div class="tooltip-formula">• 26.23 Rating = 1% Spell Hit</div><div class="tooltip-effect">Reduces chance to miss attacks. Cap: 8% vs raid bosses (263 rating), 5% vs players (164 rating).</div>',
                'placeholder' => 'e.g. 168'
            ],
            'expertise' => [
                'label' => 'Expertise (Points)', 
                'tooltip' => '<strong>Expertise Points (WotLK 3.3.5a)</strong><br><div class="tooltip-formula">• 1 Expertise = 0.25% Reduction</div><div class="tooltip-formula">• 32.79 Rating = 1 Expertise</div><div class="tooltip-effect">Enter expertise POINTS (not rating). Reduces target\'s chance to dodge or parry your attacks. Cap: 26 expertise vs bosses, 26 vs players (6.5%).</div>', 
                'placeholder' => 'e.g. 10'
            ],
            'crit_rating' => [
                'label' => 'Critical Strike Rating', 
                'tooltip' => '<strong>Critical Strike (WotLK 3.3.5a)</strong><br><div class="tooltip-formula">• 45.91 Rating = 1% Crit</div><div class="tooltip-effect">Increases chance of critical hits. Combines with crit from agility. For Feral Druids, high crit is essential for maximum DPS. Enter the RATING value, not percentage!</div>', 
                'placeholder' => 'e.g. 1000'
            ]
        ];
        
        foreach ($fields as $name => $field): ?>
            <div class="input-group">
                <div class="tooltip">
                    <label for="<?= $name ?>"><?= $field['label'] ?></label>
                    <span class="tooltiptext"><?= $field['tooltip'] ?></span>
                </div>
                <input type="number" 
                       id="<?= $name ?>" 
                       name="<?= $name ?>" 
                       min="0" 
                       max="<?= $name === 'base_ap' ? '99999' : ($name === 'expertise' ? '99' : '9999') ?>" 
                       step="any"
                       value="<?= isset($input[$name]) ? htmlspecialchars($input[$name]) : '' ?>"
                       <?= isset($field['placeholder']) ? 'placeholder="' . htmlspecialchars($field['placeholder']) . '"' : '' ?>
                       required>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="btn-container">
        <button type="submit" class="btn" id="calculateBtn">
            Calculate Stats
        </button>
        <button type="button" class="btn btn-secondary" id="exportBtn" onclick="exportToJson()" style="display: none;">
            ⬇ Export JSON
        </button>
        <button type="button" class="reset-btn" onclick="resetForm()">
            Reset
        </button>
    </div>
</form>