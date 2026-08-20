<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: ' . '/kamadhenu-goushala/admin/index.php');
exit;
