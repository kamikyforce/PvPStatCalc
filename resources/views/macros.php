<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Macros & Guides - PvP Calculator</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navigation">
        <ul class="navbits">
            <li><a href="/" title="Calculator">CALCULATOR</a></li>
            <li><a href="/macros" title="Macros & WoW Guides" class="active">MACROS & GUIDES</a></li>
        </ul>
    </nav>

    <!-- Visitor Counter -->
    <div class="visitor-counter">
        <div style="text-align: center; margin-bottom: 8px;">
            <strong>🌍 Visitors: <?= $total_visitors ?? 0 ?></strong>
        </div>
        <?php if (!empty($visitor_stats)): ?>
            <?php foreach ($visitor_stats as $country): ?>
                <div style="display: flex; align-items: center; margin: 2px 0;">
                    <span style="font-size: 16px; margin-right: 5px;"><?= $country['flag'] ?></span>
                    <span style="flex: 1;"><?= htmlspecialchars($country['name']) ?></span>
                    <span style="font-weight: bold; margin-left: 5px;"><?= $country['count'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="header">
            <h1>Macros & Guides</h1>
            <p class="subtitle">Coming soon: Macros and guides for WoW</p>
        </div>

        <div style="text-align: center; padding: 60px 20px; color: #888;">
            <h2 style="color: #fff; margin-bottom: 20px;">🚧 Under Development</h2>
            <p>This section will be available soon with macros and guides for World of Warcraft.</p>
            <p>Check back soon for new content!</p>
        </div>
    </div>
</body>
</html>