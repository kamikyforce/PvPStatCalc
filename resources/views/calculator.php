<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'PvP Calculator') ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/websocket-styles.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navigation">
        <ul class="navbits">
            <li><a href="/" title="Calculator" class="active">CALCULATOR</a></li>
            <!-- <li><a href="/macros" title="Macros & WoW Guides">MACROS & GUIDES</a></li> -->
        </ul>
        <div class="ws-status" title="WebSocket Connection"></div>
    </nav>

    <!-- Enhanced Visitor Counter with WebSocket -->
    <div class="visitor-counter">
        <div class="visitor-header">
            <div style="text-align: center; margin-bottom: 8px;">
                <strong>Visitors: <span class="total-visitors"><?= $total_visitors ?? 0 ?></span></strong>
                <br>
                <span class="online-status">
                    🟢 <span id="online-count"><?= $online_visitors ?? 0 ?></span> online now
                </span>
            </div>
            <div class="connection-status connected">
                🟢 Live <span class="connection-latency">0ms</span>
            </div>
            <div class="last-update">Last update: <?= date('H:i:s') ?></div>
        </div>
        
        <!-- Real-time Statistics -->
        <div class="realtime-stats">
            <div class="stat-item">
                <div class="stat-value" id="active-users">0</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="countries-online">0</div>
                <div class="stat-label">Countries</div>
            </div>
        </div>
        
        <div class="visitor-list">
            <?php if (!empty($visitor_stats)): ?>
                <?php foreach ($visitor_stats as $countryCode => $country): ?>
                    <div class="country-item" data-country="<?= $countryCode ?>">
                        <div style="display: flex; align-items: center; margin: 2px 0;">
                            <span style="font-size: 16px; margin-right: 5px;"><?= $country['flag'] ?></span>
                            <span style="flex: 1;"><?= htmlspecialchars($country['name']) ?></span>
                            <span style="font-weight: bold; margin-left: 5px;"><?= $country['count'] ?></span>
                            <?php if (isset($online_countries[$countryCode]) && $online_countries[$countryCode]['online_count'] > 0): ?>
                                <span class="online-indicator animate-pulse">🟢<?= $online_countries[$countryCode]['online_count'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
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
    <script src="/js/websocket-client.js"></script>
</body>
</html>