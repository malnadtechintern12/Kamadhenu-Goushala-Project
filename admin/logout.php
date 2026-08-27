<?php
require_once __DIR__ . '/../config/config.php';
session_start();
$_SESSION = [];
session_destroy();
header('Location: ' . ADMIN_URL . '/index.php');
exit;

