<?php
// API: Submit Contact Form Message
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Method not allowed.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(false, null, 'Invalid JSON input.', 400);
}

$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');
$phone   = trim($input['phone'] ?? '');
$subject = trim($input['subject'] ?? 'General Inquiry');
$message = trim($input['message'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($message)) {
    jsonResponse(false, null, 'Name, email, and message are required.', 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, null, 'Please provide a valid email address.', 400);
}

try {
    global $pdo;
    $stmt = $pdo->prepare(
        "INSERT INTO contact_messages (name, email, phone, subject, message, status) 
         VALUES (?, ?, ?, ?, ?, 'New')"
    );
    $stmt->execute([$name, $email, $phone, $subject, $message]);
    jsonResponse(true, null, 'Thank you! Your message has been received. We will respond shortly.');
} catch (PDOException $e) {
    jsonResponse(false, null, 'Failed to save message. Please try again.', 500);
}
