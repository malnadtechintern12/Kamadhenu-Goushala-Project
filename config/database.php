<?php
// ============================================================
// Kamadhenu Goushala — PDO Database Connection
// ============================================================

require_once __DIR__ . '/config.php';

// Load .env values if available (manual parsing for XAMPP)
function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val, " \t\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

loadEnv(ROOT_DIR . '/.env');

// DB credentials — fall back to XAMPP defaults
$db_host = $_ENV['DB_HOST']     ?? 'localhost';
$db_port = $_ENV['DB_PORT']     ?? '3306';
$db_name = $_ENV['DB_NAME']     ?? 'kamadhenu_goushala';
$db_user = $_ENV['DB_USER']     ?? 'root';
$db_pass = $_ENV['DB_PASSWORD'] ?? '';

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $pdo_options);
} catch (PDOException $e) {
    // In API context return JSON; otherwise show error page
    if (
        (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) ||
        (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], '/api/'))
    ) {
        http_response_code(503);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }
    // HTML context
    http_response_code(503);
    die('
        <html><body style="font-family:sans-serif;text-align:center;padding:60px;background:#FBF7ED">
        <h2 style="color:#173B2A">Database Connection Error</h2>
        <p>Could not connect to MySQL. Please check:<br>
        1. XAMPP MySQL service is running.<br>
        2. Database <strong>kamadhenu_goushala</strong> exists.<br>
        3. Credentials in <code>.env</code> are correct.</p>
        <p style="color:#888;font-size:12px">' . htmlspecialchars($e->getMessage()) . '</p>
        </body></html>
    ');
}
