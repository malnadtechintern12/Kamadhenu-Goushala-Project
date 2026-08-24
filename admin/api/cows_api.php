<?php
// Admin API: Cows CRUD
session_start();
require_once __DIR__ . '/../../includes/functions.php';
if (!isset($_SESSION['admin_id'])) { jsonResponse(false, null, 'Unauthorized.', 401); }

// DELETE
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intInput($_GET['id'] ?? 0);
    if ($id <= 0) jsonResponse(false, null, 'Invalid ID.', 400);
    try {
        global $pdo;
        $pdo->prepare("DELETE FROM cows WHERE id = ?")->execute([$id]);
        jsonResponse(true, null, 'Cow deleted successfully.');
    } catch (PDOException $e) { jsonResponse(false, null, 'Delete failed: ' . $e->getMessage(), 500); }
}

// CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) jsonResponse(false, null, 'Invalid JSON.', 400);

    $id       = intInput($input['id'] ?? 0);
    $name     = trim($input['name'] ?? '');
    $tag      = trim($input['tag_number'] ?? '');
    $breedId  = intInput($input['breed_id'] ?? 0) ?: null;
    $gender   = $input['gender'] ?? 'Female';
    $dob      = $input['dob'] ?? null;
    $arrival  = $input['arrival_date'] ?? null;
    $health   = $input['health_status'] ?? 'Healthy';
    $status   = $input['status'] ?? 'Active';
    $image    = trim($input['image'] ?? '');
    $story    = trim($input['story'] ?? '');
    $whatsapp = trim($input['whatsapp_number'] ?? '') ?: null;

    if (empty($name) || empty($tag)) jsonResponse(false, null, 'Name and tag number are required.', 400);

    try {
        global $pdo;
        if ($id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE cows SET name=?, tag_number=?, breed_id=?, gender=?, dob=?, arrival_date=?, health_status=?, status=?, image=?, story=?, whatsapp_number=? WHERE id=?");
            $stmt->execute([$name, $tag, $breedId, $gender, $dob ?: null, $arrival ?: null, $health, $status, $image, $story, $whatsapp, $id]);
            jsonResponse(true, null, "Cow '$name' updated successfully.");
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO cows (name, tag_number, breed_id, gender, dob, arrival_date, health_status, status, image, story, whatsapp_number) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$name, $tag, $breedId, $gender, $dob ?: null, $arrival ?: null, $health, $status, $image, $story, $whatsapp]);
            jsonResponse(true, null, "Cow '$name' added successfully.");
        }
    } catch (PDOException $e) {
        $msg = str_contains($e->getMessage(), 'Duplicate') ? 'Tag number already exists.' : 'Database error.';
        jsonResponse(false, null, $msg, 500);
    }
}

jsonResponse(false, null, 'Invalid request.', 400);
