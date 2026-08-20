<?php
// API: Submit Donation
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { jsonResponse(false, null, 'Method not allowed.', 405); }
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { jsonResponse(false, null, 'Invalid input.', 400); }

$name    = trim($input['donor_name'] ?? '');
$email   = trim($input['donor_email'] ?? '');
$phone   = trim($input['donor_phone'] ?? '');
$pan     = strtoupper(trim($input['pan_number'] ?? ''));
$sevaId  = intInput($input['seva_id'] ?? 0) ?: null;
$amount  = floatval($input['amount'] ?? 0);
$message = trim($input['message'] ?? '');

if (empty($name) || empty($email) || $amount <= 0) {
    jsonResponse(false, null, 'Name, email, and valid amount are required.', 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, null, 'Invalid email address.', 400);
}

// Determine seva name
$sevaName = 'General Donation';
if ($sevaId) {
    global $pdo;
    $ss = $pdo->prepare("SELECT title FROM seva WHERE id = ?");
    $ss->execute([$sevaId]);
    $sevaRow = $ss->fetch();
    if ($sevaRow) $sevaName = $sevaRow['title'];
}

$donNum = generateDonationNumber();

try {
    global $pdo;
    $stmt = $pdo->prepare(
        "INSERT INTO donations (donation_number, donor_name, donor_email, donor_phone, pan_number, amount, seva_id, seva_name, message, payment_method, payment_status) VALUES (?,?,?,?,?,?,?,?,?,'Online Form','Pending')"
    );
    $stmt->execute([$donNum, $name, $email, $phone, $pan ?: null, $amount, $sevaId, $sevaName, $message]);
    jsonResponse(true, ['donation_number' => $donNum], "Donation ₹" . number_format($amount) . " recorded successfully! Reference: $donNum. Our team will contact you for payment confirmation. 🙏");
} catch (PDOException $e) {
    jsonResponse(false, null, 'Failed to record donation. Please try again.', 500);
}
