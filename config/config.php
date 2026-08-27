<?php
// ============================================================
// Kamadhenu Goushala — Global Configuration
// ============================================================

// Detect protocol and host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            ? 'https' : 'http';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// File system root
define('ROOT_DIR', dirname(__DIR__));

// Auto-detect base path dynamically (empty string if files are directly in htdocs / web root)
$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']) : '';
$rootDirNorm = str_replace('\\', '/', realpath(ROOT_DIR) ?: ROOT_DIR);

$basePath = '';
if (!empty($docRoot) && str_starts_with($rootDirNorm, $docRoot)) {
    $basePath = substr($rootDirNorm, strlen($docRoot));
}
$basePath = rtrim(str_replace('\\', '/', $basePath), '/');

define('BASE_PATH', $basePath);
define('BASE_URL',  rtrim($protocol . '://' . $host . BASE_PATH, '/'));

// Upload directories
define('UPLOADS_DIR', ROOT_DIR . '/assets/uploads/');
define('UPLOADS_URL', BASE_URL . '/assets/uploads/');

// Admin
define('ADMIN_URL', BASE_URL . '/admin');
define('SESSION_NAME', 'kg_admin_session');

// Site defaults (overridden by DB settings)
define('SITE_NAME',    'Kamadhenu Goushala');
define('SITE_TAGLINE', 'Serving Gau Mata With Pure Devotion');
define('SITE_EMAIL',   'info@kamadhenugoushala.org');
define('SITE_PHONE',   '+91 98450 88990');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting (set to 0 on production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
