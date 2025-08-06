<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'PvP Calculator') ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
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
    <!-- Add this somewhere in your calculator.php view -->
    <div class="visitor-counter" style="position: fixed; top: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 8px; font-size: 12px; z-index: 1000;">
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
</body>
</html>