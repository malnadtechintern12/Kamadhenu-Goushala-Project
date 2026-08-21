<?php
// API: Submit Organic Store Order
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Method not allowed.', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(false, null, 'Invalid input.', 400);
}

$name     = trim($input['customer_name'] ?? '');
$email    = trim($input['customer_email'] ?? '');
$phone    = trim($input['customer_phone'] ?? '');
$address  = trim($input['shipping_address'] ?? '');
$payMethod= trim($input['payment_method'] ?? 'Cash on Delivery');
$notes    = trim($input['notes'] ?? '');
$items    = $input['items'] ?? [];

// Validation
if (empty($name) || empty($email) || empty($phone) || empty($address)) {
    jsonResponse(false, null, 'Customer name, email, phone number, and delivery address are required.', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, null, 'Please enter a valid email address.', 400);
}

if (empty($items) || !is_array($items)) {
    jsonResponse(false, null, 'Your order must contain at least one item.', 400);
}

global $pdo;

try {
    $pdo->beginTransaction();

    $validatedItems = [];
    $totalAmount = 0.00;

    foreach ($items as $item) {
        $prodId = intval($item['product_id'] ?? $item['id'] ?? 0);
        $qty    = max(1, intval($item['quantity'] ?? 1));

        if ($prodId > 0) {
            $stmt = $pdo->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
            $stmt->execute([$prodId]);
            $product = $stmt->fetch();

            if ($product) {
                $unitPrice = floatval($product['price']);
                $subtotal  = $unitPrice * $qty;
                $totalAmount += $subtotal;

                $validatedItems[] = [
                    'product_id'   => $product['id'],
                    'product_name' => $product['name'],
                    'unit_price'   => $unitPrice,
                    'quantity'     => $qty,
                    'subtotal'     => $subtotal
                ];

                // Update stock if tracked
                $upd = $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");
                $upd->execute([$qty, $product['id']]);
            }
        } elseif (!empty($item['name']) && floatval($item['price'] ?? 0) > 0) {
            $unitPrice = floatval($item['price']);
            $subtotal  = $unitPrice * $qty;
            $totalAmount += $subtotal;

            $validatedItems[] = [
                'product_id'   => null,
                'product_name' => sanitize($item['name']),
                'unit_price'   => $unitPrice,
                'quantity'     => $qty,
                'subtotal'     => $subtotal
            ];
        }
    }

    if (empty($validatedItems) || $totalAmount <= 0) {
        $pdo->rollBack();
        jsonResponse(false, null, 'No valid items found in order.', 400);
    }

    $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

    // Insert Order
    $stmtOrder = $pdo->prepare(
        "INSERT INTO orders (
            order_number, customer_name, customer_email, customer_phone,
            shipping_address, total_amount, payment_method, payment_status,
            order_status, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending', ?)"
    );

    $stmtOrder->execute([
        $orderNumber,
        $name,
        $email,
        $phone,
        $address,
        $totalAmount,
        $payMethod,
        $notes ?: null
    ]);

    $orderId = $pdo->lastInsertId();

    // Insert Order Items
    $stmtItem = $pdo->prepare(
        "INSERT INTO order_items (
            order_id, product_id, product_name, unit_price, quantity, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?)"
    );

    foreach ($validatedItems as $vItem) {
        $stmtItem->execute([
            $orderId,
            $vItem['product_id'],
            $vItem['product_name'],
            $vItem['unit_price'],
            $vItem['quantity'],
            $vItem['subtotal']
        ]);
    }

    $pdo->commit();

    jsonResponse(true, [
        'order_number' => $orderNumber,
        'order_id'     => $orderId,
        'total_amount' => $totalAmount
    ], "Order placed successfully! Reference: $orderNumber. Thank you for supporting our Goushala! 🌿");

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(false, null, 'Failed to process your order. Please try again.', 500);
}
