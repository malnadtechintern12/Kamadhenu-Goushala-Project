<?php
// API: Submit Newsletter Subscription
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Method not allowed.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, null, 'Please provide a valid email address.', 400);
}

try {
    global $pdo;
    // Check if already subscribed
    $check = $pdo->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $check->execute([$email]);
    $existing = $check->fetch();

    if ($existing) {
        if ($existing['status'] === 'Active') {
            jsonResponse(true, null, 'You are already subscribed! 🙏');
        }
        // Re-activate
        $upd = $pdo->prepare("UPDATE newsletter_subscribers SET status = 'Active' WHERE id = ?");
        $upd->execute([$existing['id']]);
        jsonResponse(true, null, 'Welcome back! Your subscription has been re-activated. 🙏');
    }

    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, status) VALUES (?, 'Active')");
    $stmt->execute([$email]);
    jsonResponse(true, null, 'Thank you for subscribing! You will receive our monthly Goushala updates. 🙏');
} catch (PDOException $e) {
    jsonResponse(false, null, 'Subscription failed. Please try again.', 500);
}
