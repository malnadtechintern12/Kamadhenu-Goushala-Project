<?php
// ============================================================
// Kamadhenu Goushala — Global Configuration
// ============================================================

// Detect base URL automatically
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// The subfolder path under htdocs
define('BASE_PATH', '/kamadhenu-goushala');
define('BASE_URL',  $protocol . '://' . $host . BASE_PATH);

// File system root
define('ROOT_DIR',  dirname(__DIR__));

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
