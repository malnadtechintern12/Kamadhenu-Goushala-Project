<?php
// ============================================================
// Admin Authentication Guard
// Include at the top of every admin page (except login)
// ============================================================
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

// Refresh admin data (check if still active)
$adminStmt = $pdo->prepare("SELECT id, name, email, role, status FROM admins WHERE id = ? AND status = 'active'");
$adminStmt->execute([$_SESSION['admin_id']]);
$currentAdmin = $adminStmt->fetch();

if (!$currentAdmin) {
    session_destroy();
    header('Location: ' . BASE_URL . '/admin/index.php?error=session_expired');
    exit;
}

$_SESSION['admin_name']  = $currentAdmin['name'];
$_SESSION['admin_email'] = $currentAdmin['email'];
$_SESSION['admin_role']  = $currentAdmin['role'];
