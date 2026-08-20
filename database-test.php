<?php
// ============================================================
// Kamadhenu Goushala — Database Connectivity & Sanity Test
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Test — Kamadhenu Goushala</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f8f9fa; line-height: 1.6; }
        .card { background: white; padding: 25px; border-radius: 12px; max-width: 700px; margin: 0 auto; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .badge-ok { background: #28a745; color: white; padding: 4px 10px; border-radius: 6px; font-weight: bold; }
        .badge-err { background: #dc3545; color: white; padding: 4px 10px; border-radius: 6px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 8px 12px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Kamadhenu Goushala — System Check</h2>
        <p>Database Status: <span class="badge-ok">CONNECTED</span></p>
        <p>Base URL: <code><?= BASE_URL ?></code></p>
        
        <h3>Table Records Summary:</h3>
        <table>
            <tr><th>Entity</th><th>Record Count</th><th>Status</th></tr>
            <?php
            $tables = ['admins', 'breeds', 'cows', 'seva', 'donations', 'testimonials', 'timeline', 'products', 'blogs', 'events', 'gallery', 'contact_messages', 'newsletter_subscribers', 'settings'];
            foreach ($tables as $t) {
                try {
                    $cnt = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
                    echo "<tr><td><strong>$t</strong></td><td>$cnt</td><td><span class='badge-ok'>OK</span></td></tr>";
                } catch (Exception $e) {
                    echo "<tr><td><strong>$t</strong></td><td>-</td><td><span class='badge-err'>FAIL</span></td></tr>";
                }
            }
            ?>
        </table>

        <div style="margin-top: 25px;">
            <a href="<?= BASE_URL ?>/" style="display:inline-block; padding:10px 18px; background:#173B2A; color:white; text-decoration:none; border-radius:8px; font-weight:bold;">Visit Website Homepage</a>
            <a href="<?= BASE_URL ?>/admin/" style="display:inline-block; margin-left: 10px; padding:10px 18px; background:#D4A72C; color:#173B2A; text-decoration:none; border-radius:8px; font-weight:bold;">Open Admin Portal</a>
        </div>
    </div>
</body>
</html>
