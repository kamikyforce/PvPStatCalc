<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feral Druid PvP Stat Calculator (WotLK 3.3.5a)</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #000000;
      color: #ffffff;
      min-height: 100vh;
      padding: 20px;
      line-height: 1.6;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      background: #111111;
      padding: 40px;
      border-radius: 8px;
      border: 1px solid #333333;
    }

    .header {
      text-align: center;
      margin-bottom: 40px;
      border-bottom: 1px solid #333333;
      padding-bottom: 20px;
    }

    h1 {
      color: #ffffff;
      font-size: 2rem;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .subtitle {
      color: #888888;
      font-size: 1rem;
      font-weight: 400;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
      margin-bottom: 40px;
    }

    .input-group {
      display: flex;
      flex-direction: column;
      position: relative;
    }

    .input-group label {
      color: #cccccc;
      font-weight: 500;
      margin-bottom: 8px;
      font-size: 0.95rem;
      cursor: help;
      position: relative;
    }

    .input-group label:hover {
      color: #ffffff;
    }

    .input-group input {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #333333;
      border-radius: 4px;
      background: #222222;
      color: #ffffff;
      font-size: 1rem;
      transition: border-color 0.2s ease;
    }

    .input-group input:focus {
      outline: none;
      border-color: #666666;
    }

    .input-group input:valid {
      border-color: #444444;
    }

    /* Tooltip Styles */
    .tooltip {
      position: relative;
      display: inline-block;
    }

    .tooltip .tooltiptext {
      visibility: hidden;
      width: 320px;
      background-color: #1a1a1a;
      color: #ffffff;
      text-align: left;
      border-radius: 6px;
      padding: 12px;
      position: absolute;
      z-index: 1000;
      bottom: 125%;
      left: 50%;
      margin-left: -160px;
      opacity: 0;
      transition: opacity 0.3s;
      border: 1px solid #444444;
      font-size: 0.85rem;
      line-height: 1.4;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    }

    .tooltip .tooltiptext::after {
      content: "";
      position: absolute;
      top: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: #444444 transparent transparent transparent;
    }

    .tooltip:hover .tooltiptext {
      visibility: visible;
      opacity: 1;
    }

    .tooltip-formula {
      color: #ffcc00;
      font-family: monospace;
      font-size: 0.8rem;
      margin: 4px 0;
    }

    .tooltip-effect {
      color: #88ff88;
      font-style: italic;
      margin: 4px 0;
    }

    .btn-container {
      text-align: center;
      margin: 40px 0;
    }

    .btn {
      background: #ffffff;
      color: #000000;
      border: none;
      padding: 14px 32px;
      border-radius: 4px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .btn:hover {
      background: #f0f0f0;
    }

    .btn:active {
      background: #e0e0e0;
    }

    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .reset-btn {
      background: transparent;
      color: #888888;
      border: 1px solid #333333;
      padding: 12px 24px;
      border-radius: 4px;
      font-size: 0.9rem;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-left: 16px;
    }

    .reset-btn:hover {
      border-color: #666666;
      color: #cccccc;
    }

    .result {
      margin-top: 40px;
      background: #1a1a1a;
      padding: 32px;
      border-radius: 4px;
      border: 1px solid #333333;
    }

    .result h3 {
      color: #ffffff;
      margin-bottom: 24px;
      font-size: 1.3rem;
      font-weight: 600;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }

    .stat-item {
      background: #222222;
      padding: 20px;
      border-radius: 4px;
      border: 1px solid #333333;
      position: relative;
    }

    .stat-item:hover {
      border-color: #555555;
      background: #252525;
    }

    .stat-label {
      color: #888888;
      font-size: 0.9rem;
      margin-bottom: 8px;
      font-weight: 400;
    }

    .stat-value {
      color: #ffffff;
      font-size: 1.4rem;
      font-weight: 600;
    }

    .alert {
      padding: 16px 20px;
      border-radius: 4px;
      margin: 20px 0;
      border-left: 4px solid;
    }

    .alert-success {
      background: #1a2e1a;
      border-left-color: #4a7c59;
      color: #ffffff;
    }

    .alert-error {
      background: #2e1a1a;
      border-left-color: #7c4a4a;
      color: #ffffff;
    }

    .alert-warning {
      background: #2e2a1a;
      border-left-color: #7c6a4a;
      color: #ffffff;
    }

    @media (max-width: 768px) {
      .container {
        padding: 24px;
        margin: 10px;
      }

      h1 {
        font-size: 1.3rem;
      }

      .form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }

      .btn-container {
        flex-direction: column;
        align-items: center;
      }

      .reset-btn {
        margin-left: 0;
        margin-top: 12px;
      }

      .tooltip .tooltiptext {
        width: 280px;
        margin-left: -140px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Feral Druid PvP Calculator</h1>
      <p class="subtitle">World of Warcraft: Wrath of the Lich King (3.3.5a)</p>
    </div>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="statForm">
      <div class="form-grid">
        <div class="input-group">
          <div class="tooltip">
            <label for="agility">Agility</label>
            <span class="tooltiptext">
              <strong>Agility (WotLK 3.3.5a)</strong><br>
              <div class="tooltip-formula">• 1 AGI = 1 Attack Power</div>
              <div class="tooltip-formula">• ~63.5 AGI = 1% Crit</div>
              <div class="tooltip-formula">• 1 AGI = 2 Armor</div>
              <div class="tooltip-effect">For Feral Druids, agility is the primary stat that increases damage, crit chance, and armor simultaneously.</div>
            </span>
          </div>
          <input type="number" id="agility" name="agility" min="0" max="9999" required>
        </div>
        
        <div class="input-group">
          <div class="tooltip">
            <label for="strength">Strength</label>
            <span class="tooltiptext">
              <strong>Strength (WotLK 3.3.5a)</strong><br>
              <div class="tooltip-formula">• 1 STR = 2 Attack Power</div>
              <div class="tooltip-effect">For Feral Druids, strength provides additional attack power but is secondary compared to agility.</div>
            </span>
          </div>
          <input type="number" id="strength" name="strength" min="0" max="9999" required>
        </div>
        
        <div class="input-group">
          <div class="tooltip">
            <label for="base_ap">Attack Power (base)</label>
            <span class="tooltiptext">
              <strong>Base Attack Power</strong><br>
              <div class="tooltip-formula">• 14 AP = 1 DPS</div>
              <div class="tooltip-effect">The character's base attack power before attribute bonuses. Directly increases physical attack damage.</div>
            </span>
          </div>
          <input type="number" id="base_ap" name="base_ap" min="0" max="99999" required>
        </div>
        
        <div class="input-group">
          <div class="tooltip">
            <label for="arp">Armor Penetration Rating</label>
            <span class="tooltiptext">
              <strong>Armor Penetration (WotLK 3.3.5a)</strong><br>
              <div class="tooltip-formula">• 13.99 Rating = 1% Penetration</div>
              <div class="tooltip-effect">Ignores a percentage of the target's armor. Very effective against high-armor targets. After patch 3.1, effectiveness increased by 25%.</div>
            </span>
          </div>
          <input type="number" id="arp" name="arp" min="0" max="9999" required>
        </div>
        
        <div class="input-group">
          <div class="tooltip">
            <label for="resilience">Resilience Rating</label>
            <span class="tooltiptext">
              <strong>Resilience (WotLK 3.3.5a)</strong><br>
              <div class="tooltip-formula">• 81.97 Rating = 1% Crit Reduction</div>
              <div class="tooltip-effect">Reduces chance to receive critical hits, reduces critical hit damage, and provides additional damage reduction against players. Essential for PvP. <strong>Note:</strong> This is NOT your armor value!</div>
            </span>
          </div>
          <input type="number" id="resilience" name="resilience" min="0" max="9999" required placeholder="e.g. 1073">
        </div>
        
        <div class="input-group">
          <div class="tooltip">
            <label for="stamina">Stamina</label>
            <span class="tooltiptext">
              <strong>Stamina (WotLK 3.3.5a)</strong><br>
              <div class="tooltip-formula">• 1 STA = 10 Health (level 80)</div>
              <div class="tooltip-effect">Increases maximum health points. Essential for survivability in both PvP and PvE.</div>
            </span>
          </div>
          <input type="number" id="stamina" name="stamina" min="0" max="9999" required>
        </div>
        
        <div class="input-group">
          <div class="tooltip">
            <label for="hit">Hit Rating</label>
            <span class="tooltiptext">
              <strong>Hit Rating (WotLK 3.3.5a)</strong><br>
              <div class="tooltip-formula">• 32.79 Rating = 1% Physical Hit</div>
              <div class="tooltip-formula">• 26.23 Rating = 1% Spell Hit</div>
              <div class="tooltip-effect">Reduces chance to miss attacks. Cap: 8% vs raid bosses (263 rating), 5% vs players (164 rating).</div>
            </span>
          </div>
          <input type="number" id="hit" name="hit" min="0" max="9999" required>
        </div>
        
        <div class="input-group">
          <div class="tooltip">
            <label for="expertise">Expertise (Points)</label>
            <span class="tooltiptext">
              <strong>Expertise Points (WotLK 3.3.5a)</strong><br>
              <div class="tooltip-formula">• 1 Expertise = 0.25% Reduction</div>
              <div class="tooltip-formula">• 32.79 Rating = 1 Expertise</div>
              <div class="tooltip-effect">Enter expertise POINTS (not rating). Reduces target's chance to dodge or parry your attacks. Cap: 26 expertise vs bosses, 26 vs players (6.5%).</div>
            </span>
          </div>
          <input type="number" id="expertise" name="expertise" min="0" max="99" required placeholder="e.g. 10">
        </div>
        
        <div class="input-group">
          <div class="tooltip">
            <label for="crit_rating">Critical Strike Rating</label>
            <span class="tooltiptext">
              <strong>Critical Strike (WotLK 3.3.5a)</strong><br>
              <div class="tooltip-formula">• 45.91 Rating = 1% Crit</div>
              <div class="tooltip-effect">Increases chance of critical hits. Combines with crit from agility. For Feral Druids, high crit is essential for maximum DPS. Enter the RATING value, not percentage!</div>
            </span>
          </div>
          <input type="number" id="crit_rating" name="crit_rating" min="0" max="9999" required placeholder="e.g. 1000">
        </div>
      </div>

      <div class="btn-container">
        <button type="submit" class="btn" id="calculateBtn">
          Calculate Stats
        </button>
        <button type="button" class="reset-btn" onclick="resetForm()">
          Reset
        </button>
      </div>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      try {
        $agility = filter_input(INPUT_POST, 'agility', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        $strength = filter_input(INPUT_POST, 'strength', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        $base_ap = filter_input(INPUT_POST, 'base_ap', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99999]]);
        $arp = filter_input(INPUT_POST, 'arp', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        $resilience = filter_input(INPUT_POST, 'resilience', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        $stamina = filter_input(INPUT_POST, 'stamina', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        $hit = filter_input(INPUT_POST, 'hit', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
        $expertise = filter_input(INPUT_POST, 'expertise', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);
        $crit_rating = filter_input(INPUT_POST, 'crit_rating', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 9999]]);
    
        if ($agility === false || $strength === false || $base_ap === false || $arp === false || 
            $resilience === false || $stamina === false || $hit === false || $expertise === false || $crit_rating === false) {
          throw new Exception("Invalid input values. Please check your entries.");
        }
    
        // WotLK 3.3.5a EXACT FORMULAS (Level 80)
        
        // Combat Rating Conversions
        $hit_percent = $hit / 32.79; // 32.79 Hit Rating = 1% Hit
        $expertise_percent = $expertise * 0.25; // 1 Expertise Point = 0.25% reduction (NOT rating conversion)
        $arp_percent = $arp / 13.99; // 13.99 ArP Rating = 1% Armor Penetration
        $crit_percent_from_rating = $crit_rating / 45.91; // 45.91 Crit Rating = 1% Crit
        
        // RESILIENCE - BOTH PvE AND PvP FORMULAS
        $resilience_crit_reduction = $resilience / 81.97; // 81.97 Resilience Rating = 1% Crit Reduction
        $resilience_pvp_damage_reduction = $resilience / 47.28; // 47.28 Resilience Rating = 1% PvP Damage Reduction
        
        // Primary Attribute Conversions
        $ap_from_str = $strength * 2; // 1 Str = 2 AP
        $ap_from_agi = $agility * 1; // 1 Agi = 1 AP for Feral Druids
        $total_ap = $base_ap + $ap_from_agi + $ap_from_str;
        
        // Stamina to Health
        $health_from_stamina = $stamina * 10; // 1 Sta = 10 Health at level 80
        
        // Agility to Crit (Feral Druid specific)
        $crit_from_agi = $agility / 63.5; // ~63.5 Agi = 1% Crit for Feral Druids
        $total_crit = $crit_from_agi + $crit_percent_from_rating;
        
        // Agility to Armor
        $armor_from_agi = $agility * 2; // 1 Agi = 2 Armor
        
        // Validation warnings
        $warnings = [];
        if ($resilience > 5000) {
          $warnings[] = "Warning: Resilience value seems very high. Make sure you're entering RESILIENCE rating, not armor value!";
        }
        if ($crit_rating > 0 && $crit_percent_from_rating < 5) {
          $warnings[] = "Note: Your crit rating seems low. Make sure you're entering the RATING value, not percentage.";
        }
        
        echo '<div class="alert alert-success">';
        echo 'Calculations performed using exact WotLK 3.3.5a formulas (level 80)!';
        echo '</div>';
        
        // Display warnings if any
        foreach ($warnings as $warning) {
          echo '<div class="alert alert-warning">';
          echo htmlspecialchars($warning);
          echo '</div>';
        }
        
        echo '<div class="result">';
        echo '<h3>Calculated Statistics (WotLK 3.3.5a)</h3>';
        echo '<div class="stats-grid">';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Total Attack Power</div>';
        echo '<div class="stat-value">' . number_format(round($total_ap)) . '</div>';
        echo '<span class="tooltiptext">Base: ' . $base_ap . ' + AGI: ' . round($ap_from_agi) . ' + STR: ' . round($ap_from_str) . '<br>14 AP = 1 DPS</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Total Critical Chance</div>';
        echo '<div class="stat-value">' . number_format($total_crit, 2) . '%</div>';
        echo '<span class="tooltiptext">AGI: ' . number_format($crit_from_agi, 2) . '% + Rating: ' . number_format($crit_percent_from_rating, 2) . '%<br>Critical hits deal 200% damage</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Crit from Agility</div>';
        echo '<div class="stat-value">' . number_format($crit_from_agi, 2) . '%</div>';
        echo '<span class="tooltiptext">' . $agility . ' AGI ÷ 63.5 = ' . number_format($crit_from_agi, 2) . '%<br>Feral Druid specific formula</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Crit from Rating</div>';
        echo '<div class="stat-value">' . number_format($crit_percent_from_rating, 2) . '%</div>';
        echo '<span class="tooltiptext">' . $crit_rating . ' Rating ÷ 45.91 = ' . number_format($crit_percent_from_rating, 2) . '%<br>45.91 rating = 1% crit at level 80</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Armor from Agility</div>';
        echo '<div class="stat-value">' . number_format(round($armor_from_agi)) . '</div>';
        echo '<span class="tooltiptext">' . $agility . ' AGI × 2 = ' . number_format(round($armor_from_agi)) . ' Armor<br>Reduces physical damage taken</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Armor Penetration</div>';
        echo '<div class="stat-value">' . number_format($arp_percent, 2) . '%</div>';
        echo '<span class="tooltiptext">' . $arp . ' Rating ÷ 13.99 = ' . number_format($arp_percent, 2) . '%<br>Ignores target armor. Very effective vs high-armor targets</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Hit Chance</div>';
        echo '<div class="stat-value">' . number_format($hit_percent, 2) . '%</div>';
        echo '<span class="tooltiptext">' . $hit . ' Rating ÷ 32.79 = ' . number_format($hit_percent, 2) . '%<br>Cap: 8% vs raid bosses, 5% vs players</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Expertise</div>';
        echo '<div class="stat-value">' . number_format($expertise_percent, 2) . '%</div>';
        echo '<span class="tooltiptext">' . $expertise . ' Expertise Points × 0.25 = ' . number_format($expertise_percent, 2) . '%<br>Reduces dodge/parry chance<br>Cap: 6.5% vs players (26 expertise)</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Expertise Points</div>';
        echo '<div class="stat-value">' . $expertise . '</div>';
        echo '<span class="tooltiptext">' . $expertise . ' Expertise Points<br>1 Expertise = 0.25% dodge/parry reduction</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Crit Reduction (Resilience)</div>';
        echo '<div class="stat-value">' . number_format($resilience_crit_reduction, 2) . '%</div>';
        echo '<span class="tooltiptext">' . $resilience . ' Rating ÷ 81.97 = ' . number_format($resilience_crit_reduction, 2) . '%<br>Reduces chance to receive critical hits</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">PvP Damage Reduction</div>';
        echo '<div class="stat-value">' . number_format($resilience_pvp_damage_reduction, 2) . '%</div>';
        echo '<span class="tooltiptext">' . $resilience . ' Rating ÷ 47.28 = ' . number_format($resilience_pvp_damage_reduction, 2) . '%<br>Reduces damage taken from players in PvP</span>';
        echo '</div>';
        
        echo '<div class="stat-item tooltip">';
        echo '<div class="stat-label">Additional Health (Stamina)</div>';
        echo '<div class="stat-value">' . number_format(round($health_from_stamina)) . ' HP</div>';
        echo '<span class="tooltiptext">' . $stamina . ' STA × 10 = ' . number_format(round($health_from_stamina)) . ' HP<br>Level 80 conversion</span>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        
      } catch (Exception $e) {
        echo '<div class="alert alert-error">';
        echo 'Error: ' . htmlspecialchars($e->getMessage());
        echo '</div>';
      }
    }
    ?>
  </div>

  <script>
    function resetForm() {
      if (confirm('Are you sure you want to clear all fields?')) {
        document.getElementById('statForm').reset();
        
        const results = document.querySelectorAll('.result, .alert');
        results.forEach(result => result.remove());
      }
    }

    document.getElementById('agility').focus();

    document.querySelectorAll('input[type="number"]').forEach(input => {
      input.addEventListener('input', function() {
        if (this.value && this.checkValidity()) {
          this.style.borderColor = '#444444';
        } else if (this.value) {
          this.style.borderColor = '#7c4a4a';
        } else {
          this.style.borderColor = '#333333';
        }
      });
    });
  </script>
</body>
</html>
