<?php
// ============================================================
// Kamadhenu Goushala — Database Setup & Seeder
// ============================================================

require_once __DIR__ . '/../config/config.php';

// Direct connection without selecting database first
$db_host = $_ENV['DB_HOST'] ?? 'localhost';
$db_port = $_ENV['DB_PORT'] ?? '3306';
$db_name = $_ENV['DB_NAME'] ?? 'kamadhenu_goushala';
$db_user = $_ENV['DB_USER'] ?? 'root';
$db_pass = $_ENV['DB_PASSWORD'] ?? '';

echo "<h2>Kamadhenu Goushala — Database Setup & Seeder</h2>";

try {
    // 1. Connect to MySQL server
    $pdoInit = new PDO("mysql:host={$db_host};port={$db_port};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Create database if not exists
    $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color:green;'>✓ Database `{$db_name}` verified/created.</p>";
    
    // 2. Connect to the specific database
    $pdo = new PDO("mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 3. Execute schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (file_exists($schemaFile)) {
        $schemaSql = file_get_contents($schemaFile);
        $pdo->exec($schemaSql);
        echo "<p style='color:green;'>✓ Database schema imported successfully.</p>";
    } else {
        echo "<p style='color:red;'>✗ schema.sql not found.</p>";
    }

    // 4. Execute seed.sql
    $seedFile = __DIR__ . '/seed.sql';
    if (file_exists($seedFile)) {
        $seedSql = file_get_contents($seedFile);
        $pdo->exec($seedSql);
        echo "<p style='color:green;'>✓ Demo seed data imported successfully.</p>";
    }

    // 5. Update admin password to guaranteed hash for Admin@123
    $adminPassword = 'Admin@123';
    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $adminEmail = 'admin@kamadhenugoushala.org';
    
    $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE email = ?");
    $stmt->execute([$passwordHash, $adminEmail]);
    
    if ($stmt->rowCount() === 0) {
        $stmtInsert = $pdo->prepare("INSERT INTO admins (name, email, password_hash, role, status) VALUES (?, ?, ?, 'superadmin', 'active')");
        $stmtInsert->execute(['Goushala Administrator', $adminEmail, $passwordHash]);
    }
    
    echo "<p style='color:green;'>✓ Superadmin account ready:</p>";
    echo "<ul>
        <li><strong>Email:</strong> admin@kamadhenugoushala.org</li>
        <li><strong>Password:</strong> Admin@123</li>
    </ul>";
    
    echo "<p><a href='" . BASE_URL . "/' style='padding:8px 16px; background:#173B2A; color:#fff; text-decoration:none; border-radius:6px;'>Go to Website</a> &nbsp; <a href='" . BASE_URL . "/admin/' style='padding:8px 16px; background:#D4A72C; color:#173B2A; font-weight:bold; text-decoration:none; border-radius:6px;'>Go to Admin Panel</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red;'><strong>Setup Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
