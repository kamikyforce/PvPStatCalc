<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'PvP Calculator') ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navigation">
        <ul class="navbits">
            <li><a href="/" title="Calculator" class="active">CALCULATOR</a></li>
            <!-- <li><a href="/macros" title="Macros & WoW Guides">MACROS & GUIDES</a></li> -->
        </ul>
    </nav>

    <!-- Visitor Counter - moved below navbar -->
    <!-- Visitor Counter -->
    <div class="visitor-counter">
        <div style="text-align: center; margin-bottom: 8px;">
            <strong>Visitors: <?= $total_visitors ?? 0 ?></strong>
            <br>
            <span style="color: #4CAF50; font-size: 12px;">
                🟢 <span id="online-count"><?= $online_visitors ?? 0 ?></span> online now
            </span>
        </div>
        
        <?php if (!empty($visitor_stats)): ?>
            <?php foreach ($visitor_stats as $countryCode => $country): ?>
                <div style="display: flex; align-items: center; margin: 2px 0;">
                    <span style="font-size: 16px; margin-right: 5px;"><?= $country['flag'] ?></span>
                    <span style="flex: 1;"><?= htmlspecialchars($country['name']) ?></span>
                    <span style="font-weight: bold; margin-left: 5px;"><?= $country['count'] ?></span>
                    <?php if (isset($online_countries[$countryCode]) && $online_countries[$countryCode]['online_count'] > 0): ?>
                        <span style="color: #4CAF50; margin-left: 5px; font-size: 10px;">🟢<?= $online_countries[$countryCode]['online_count'] ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="container">
        <div class="header">
            <h1><?= htmlspecialchars($title ?? 'PvP Calculator') ?></h1>
            <p class="subtitle"><?= htmlspecialchars($subtitle ?? '') ?></p>
        </div>

        <?php include 'partials/form.php'; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($stats)): ?>
            <?php include 'partials/results.php'; ?>
        <?php endif; ?>
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>