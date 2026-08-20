<?php
session_start(); require_once __DIR__ . '/../../includes/functions.php';
if (!isset($_SESSION['admin_id'])) jsonResponse(false,null,'Unauthorized.',401);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intInput($input['id'] ?? 0);
    $status = $input['payment_status'] ?? '';
    if ($id <= 0 || !in_array($status, ['Completed','Pending','Failed','Refunded'])) jsonResponse(false,null,'Invalid data.',400);
    global $pdo;
    $pdo->prepare("UPDATE donations SET payment_status = ? WHERE id = ?")->execute([$status, $id]);
    jsonResponse(true, null, 'Donation status updated to ' . $status);
}
jsonResponse(false,null,'Invalid request.',400);
