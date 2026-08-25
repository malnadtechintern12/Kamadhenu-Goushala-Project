<?php
// ============================================================
// Kamadhenu Goushala — Universal Admin CRUD API
// Handles Add, Edit, Delete, and Status updates for all entities
// ============================================================

session_start();
require_once __DIR__ . '/../../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    jsonResponse(false, null, 'Unauthorized. Please login.', 401);
}

// 1. DELETE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $entity = trim($_GET['entity'] ?? '');
    $id     = intInput($_GET['id'] ?? 0);

    $allowedTables = [
        'breeds', 'seva', 'products', 'blogs', 'events', 
        'gallery', 'testimonials', 'timeline', 'sponsors', 
        'contact_messages', 'newsletter_subscribers', 'orders', 'donations', 'page_banners'
    ];

    if (!in_array($entity, $allowedTables) || $id <= 0) {
        jsonResponse(false, null, 'Invalid entity or ID.', 400);
    }

    try {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM `{$entity}` WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(true, null, ucfirst($entity) . ' record deleted successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Delete failed: ' . $e->getMessage(), 500);
    }
}

// 1b. REMOVE BANNER IMAGE ACTION
if (isset($_GET['action']) && $_GET['action'] === 'remove_banner_image') {
    $id = intInput($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, null, 'Invalid banner ID.', 400);
    }
    try {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE page_banners SET banner_image = NULL WHERE id = ?");
        $stmt->execute([$id]);
        jsonResponse(true, null, 'Banner image removed successfully. Reset to default background.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to remove banner image: ' . $e->getMessage(), 500);
    }
}

// 1c. GET ORDER DETAILS ACTION (with items)
if (isset($_GET['action']) && $_GET['action'] === 'get_order') {
    $id = intInput($_GET['id'] ?? 0);
    if ($id <= 0) {
        jsonResponse(false, null, 'Invalid order ID.', 400);
    }
    try {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        if (!$order) {
            jsonResponse(false, null, 'Order not found.', 404);
        }
        $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $itemStmt->execute([$id]);
        $order['items'] = $itemStmt->fetchAll();
        jsonResponse(true, $order, 'Order retrieved successfully.');
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Failed to fetch order: ' . $e->getMessage(), 500);
    }
}

// 2. TOGGLE STATUS ACTION
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status') {
    $entity = trim($_GET['entity'] ?? '');
    $id     = intInput($_GET['id'] ?? 0);
    $status = trim($_GET['status'] ?? '');
    $field  = trim($_GET['field'] ?? '');

    $allowedTables = [
        'breeds', 'seva', 'products', 'blogs', 'events', 
        'gallery', 'testimonials', 'timeline', 'newsletter_subscribers', 
        'contact_messages', 'orders', 'donations', 'page_banners'
    ];

    if (!in_array($entity, $allowedTables) || $id <= 0 || empty($status)) {
        jsonResponse(false, null, 'Invalid request.', 400);
    }

    try {
        global $pdo;

        if ($entity === 'orders') {
            if ($field === 'payment_status' || in_array($status, ['Completed', 'Paid', 'Pending', 'Failed'])) {
                $stmt = $pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                jsonResponse(true, null, 'Order payment status updated to ' . htmlspecialchars($status));
            } else {
                $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
                $stmt->execute([$status, $id]);
                jsonResponse(true, null, 'Order status updated to ' . htmlspecialchars($status));
            }
        } elseif ($entity === 'donations') {
            $stmt = $pdo->prepare("UPDATE donations SET payment_status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            jsonResponse(true, null, 'Donation payment status updated to ' . htmlspecialchars($status));
        } else {
            $stmt = $pdo->prepare("UPDATE `{$entity}` SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            jsonResponse(true, null, 'Status updated to ' . htmlspecialchars($status));
        }
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Status update failed: ' . $e->getMessage(), 500);
    }
}

// 3. POST / SAVE (INSERT or UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $entity = trim($input['entity'] ?? '');
    $id     = intInput($input['id'] ?? 0);

    global $pdo;

    // Helper: handle direct file upload
    $handleFileUpload = function(string $fileKey, string $subfolder = 'events'): ?string {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $file = $_FILES[$fileKey];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
        if (!in_array($ext, $allowed)) {
            return null;
        }
        $targetDir = ROOT_DIR . '/assets/uploads/' . $subfolder . '/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = $targetDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return 'assets/uploads/' . $subfolder . '/' . $filename;
        }
        return null;
    };

    // Check if an image file was uploaded
    $uploadedImagePath = $handleFileUpload('image_file', $entity) ?? $handleFileUpload('image', $entity);

    // Helper: create slug from title
    $slugify = function(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text ?: 'item-' . time());
    };

    try {
        switch ($entity) {
            case 'breeds':
                $name = trim($input['name'] ?? '');
                $origin = trim($input['origin'] ?? '');
                $description = trim($input['description'] ?? '');
                $milk_yield = trim($input['milk_yield'] ?? '');
                $characteristics = trim($input['characteristics'] ?? '');
                $whatsapp_number = trim($input['whatsapp_number'] ?? '');
                $image = $uploadedImagePath ?: trim($input['image'] ?? '');
                $status = $input['status'] ?? 'active';

                if (empty($name)) jsonResponse(false, null, 'Breed name is required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE breeds SET name=?, origin=?, description=?, milk_yield=?, characteristics=?, whatsapp_number=?, image=?, status=? WHERE id=?");
                    $stmt->execute([$name, $origin, $description, $milk_yield, $characteristics, $whatsapp_number, $image, $status, $id]);
                    jsonResponse(true, null, "Breed '{$name}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO breeds (name, origin, description, milk_yield, characteristics, whatsapp_number, image, status) VALUES (?,?,?,?,?,?,?,?)");
                    $stmt->execute([$name, $origin, $description, $milk_yield, $characteristics, $whatsapp_number, $image, $status]);
                    jsonResponse(true, null, "Breed '{$name}' created successfully.");
                }
                break;

            case 'seva':
                $title = trim($input['title'] ?? '');
                $slug = trim($input['slug'] ?? '') ?: $slugify($title);
                $short_desc = trim($input['short_desc'] ?? '');
                $full_desc = trim($input['full_desc'] ?? '');
                $suggested_amount = floatval($input['suggested_amount'] ?? 501);
                $icon = trim($input['icon'] ?? 'bi-heart-fill');
                $image = $uploadedImagePath ?: trim($input['image'] ?? '');
                $display_order = intInput($input['display_order'] ?? 0);
                $status = $input['status'] ?? 'active';

                if (empty($title)) jsonResponse(false, null, 'Seva title is required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE seva SET title=?, slug=?, short_desc=?, full_desc=?, suggested_amount=?, icon=?, image=?, display_order=?, status=? WHERE id=?");
                    $stmt->execute([$title, $slug, $short_desc, $full_desc, $suggested_amount, $icon, $image, $display_order, $status, $id]);
                    jsonResponse(true, null, "Seva '{$title}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO seva (title, slug, short_desc, full_desc, suggested_amount, icon, image, display_order, status) VALUES (?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([$title, $slug, $short_desc, $full_desc, $suggested_amount, $icon, $image, $display_order, $status]);
                    jsonResponse(true, null, "Seva '{$title}' created successfully.");
                }
                break;

            case 'products':
                $name = trim($input['name'] ?? '');
                $slug = trim($input['slug'] ?? '') ?: $slugify($name);
                $category_id = intInput($input['category_id'] ?? 0) ?: null;
                $description = trim($input['description'] ?? '');
                $price = floatval($input['price'] ?? 0);
                $stock = intInput($input['stock'] ?? 0);
                $image = $uploadedImagePath ?: trim($input['image'] ?? '');
                $whatsapp = trim($input['whatsapp_number'] ?? '') ?: null;
                $status = $input['status'] ?? 'active';

                if (empty($name)) jsonResponse(false, null, 'Product name is required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, category_id=?, description=?, price=?, stock=?, image=?, whatsapp_number=?, status=? WHERE id=?");
                    $stmt->execute([$name, $slug, $category_id, $description, $price, $stock, $image, $whatsapp, $status, $id]);
                    jsonResponse(true, null, "Product '{$name}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO products (name, slug, category_id, description, price, stock, image, whatsapp_number, status) VALUES (?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([$name, $slug, $category_id, $description, $price, $stock, $image, $whatsapp, $status]);
                    jsonResponse(true, null, "Product '{$name}' added successfully.");
                }
                break;

            case 'blogs':
                $title = trim($input['title'] ?? '');
                $slug = trim($input['slug'] ?? '') ?: $slugify($title);
                $category_id = intInput($input['category_id'] ?? 0) ?: null;
                $excerpt = trim($input['excerpt'] ?? '');
                $content = trim($input['content'] ?? '');
                $featured_image = $uploadedImagePath ?: trim($input['featured_image'] ?? '');
                $author = trim($input['author'] ?? 'Kamadhenu Team');
                $status = $input['status'] ?? 'Published';
                $published_at = $input['published_at'] ?? date('Y-m-d');

                if (empty($title)) jsonResponse(false, null, 'Blog title is required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE blogs SET title=?, slug=?, category_id=?, excerpt=?, content=?, featured_image=?, author=?, status=?, published_at=? WHERE id=?");
                    $stmt->execute([$title, $slug, $category_id, $excerpt, $content, $featured_image, $author, $status, $published_at, $id]);
                    jsonResponse(true, null, "Blog post updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, category_id, excerpt, content, featured_image, author, status, published_at) VALUES (?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([$title, $slug, $category_id, $excerpt, $content, $featured_image, $author, $status, $published_at]);
                    jsonResponse(true, null, "Blog post created successfully.");
                }
                break;

            case 'events':
                $title = trim($input['title'] ?? '');
                $slug = trim($input['slug'] ?? '') ?: $slugify($title);
                $description = trim($input['description'] ?? '');
                $event_date = $input['event_date'] ?? date('Y-m-d');
                $start_time = $input['start_time'] ?? '09:00:00';
                $end_time = $input['end_time'] ?? '17:00:00';
                $location = trim($input['location'] ?? 'Kamadhenu Goushala Main Grounds');
                $registration_url = trim($input['registration_url'] ?? '#');
                $image = $uploadedImagePath ?: trim($input['image'] ?? '');
                $status = $input['status'] ?? 'Upcoming';

                if (empty($title)) jsonResponse(false, null, 'Event title is required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE events SET title=?, slug=?, description=?, event_date=?, start_time=?, end_time=?, location=?, registration_url=?, image=?, status=? WHERE id=?");
                    $stmt->execute([$title, $slug, $description, $event_date, $start_time, $end_time, $location, $registration_url, $image, $status, $id]);
                    jsonResponse(true, null, "Event '{$title}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO events (title, slug, description, event_date, start_time, end_time, location, registration_url, image, status) VALUES (?,?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([$title, $slug, $description, $event_date, $start_time, $end_time, $location, $registration_url, $image, $status]);
                    jsonResponse(true, null, "Event '{$title}' created successfully.");
                }
                break;

            case 'gallery':
                $title = trim($input['title'] ?? '');
                $category_id = intInput($input['category_id'] ?? 0) ?: 1;
                $image_url = $uploadedImagePath ?: trim($input['image_url'] ?? '');
                $display_order = intInput($input['display_order'] ?? 0);
                $status = $input['status'] ?? 'active';

                if (empty($title) || empty($image_url)) jsonResponse(false, null, 'Title and Image URL are required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE gallery SET title=?, category_id=?, image_url=?, display_order=?, status=? WHERE id=?");
                    $stmt->execute([$title, $category_id, $image_url, $display_order, $status, $id]);
                    jsonResponse(true, null, "Gallery item updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO gallery (title, category_id, image_url, display_order, status) VALUES (?,?,?,?,?)");
                    $stmt->execute([$title, $category_id, $image_url, $display_order, $status]);
                    jsonResponse(true, null, "Gallery item added successfully.");
                }
                break;

            case 'testimonials':
                $author_name = trim($input['author_name'] ?? '');
                $designation = trim($input['designation'] ?? 'Devotee / Donor');
                $message = trim($input['message'] ?? '');
                $rating = intInput($input['rating'] ?? 5);
                $avatar = trim($input['avatar'] ?? '');
                $display_order = intInput($input['display_order'] ?? 0);
                $status = $input['status'] ?? 'active';

                if (empty($author_name) || empty($message)) jsonResponse(false, null, 'Author name and message are required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE testimonials SET author_name=?, designation=?, message=?, rating=?, avatar=?, display_order=?, status=? WHERE id=?");
                    $stmt->execute([$author_name, $designation, $message, $rating, $avatar, $display_order, $status, $id]);
                    jsonResponse(true, null, "Testimonial from '{$author_name}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO testimonials (author_name, designation, message, rating, avatar, display_order, status) VALUES (?,?,?,?,?,?,?)");
                    $stmt->execute([$author_name, $designation, $message, $rating, $avatar, $display_order, $status]);
                    jsonResponse(true, null, "Testimonial added successfully.");
                }
                break;

            case 'timeline':
                $year = trim($input['year'] ?? '');
                $title = trim($input['title'] ?? '');
                $description = trim($input['description'] ?? '');
                $display_order = intInput($input['display_order'] ?? 0);
                $status = $input['status'] ?? 'active';

                if (empty($year) || empty($title)) jsonResponse(false, null, 'Year and title are required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE timeline SET year=?, title=?, description=?, display_order=?, status=? WHERE id=?");
                    $stmt->execute([$year, $title, $description, $display_order, $status, $id]);
                    jsonResponse(true, null, "Milestone '{$year}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO timeline (year, title, description, display_order, status) VALUES (?,?,?,?,?)");
                    $stmt->execute([$year, $title, $description, $display_order, $status]);
                    jsonResponse(true, null, "Milestone '{$year}' created successfully.");
                }
                break;

            case 'sponsors':
                $name = trim($input['name'] ?? '');
                $email = trim($input['email'] ?? '');
                $phone = trim($input['phone'] ?? '');
                $pan_number = strtoupper(trim($input['pan_number'] ?? ''));
                $address = trim($input['address'] ?? '');

                if (empty($name) || empty($email)) jsonResponse(false, null, 'Sponsor name and email are required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE sponsors SET name=?, email=?, phone=?, pan_number=?, address=? WHERE id=?");
                    $stmt->execute([$name, $email, $phone, $pan_number, $address, $id]);
                    jsonResponse(true, null, "Sponsor '{$name}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO sponsors (name, email, phone, pan_number, address) VALUES (?,?,?,?,?)");
                    $stmt->execute([$name, $email, $phone, $pan_number, $address]);
                    jsonResponse(true, null, "Sponsor '{$name}' added successfully.");
                }
                break;

            case 'page_banners':
            case 'banners':
                $page_key = trim($input['page_key'] ?? '');
                $page_name = trim($input['page_name'] ?? '');
                $banner_image = $uploadedImagePath ?: trim($input['banner_image'] ?? $input['image'] ?? '');
                $badge_text = trim($input['badge_text'] ?? '');
                $title = trim($input['title'] ?? '');
                $subtitle = trim($input['subtitle'] ?? '');
                $status = $input['status'] ?? 'active';

                if (empty($page_key) || empty($page_name)) {
                    jsonResponse(false, null, 'Page identifier and name are required.', 400);
                }

                // Auto-cache external remote image to local storage for instant 0ms load time
                if (!empty($banner_image) && (str_starts_with($banner_image, 'http://') || str_starts_with($banner_image, 'https://'))) {
                    $bannerDir = ROOT_DIR . '/assets/uploads/banners/';
                    if (!file_exists($bannerDir)) {
                        mkdir($bannerDir, 0777, true);
                    }
                    $opts = [
                        'http' => [
                            'timeout' => 8,
                            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
                        ]
                    ];
                    $context = stream_context_create($opts);
                    $imgData = @file_get_contents($banner_image, false, $context);
                    if ($imgData && strlen($imgData) > 100) {
                        $ext = 'jpg';
                        $pathInfo = pathinfo(parse_url($banner_image, PHP_URL_PATH));
                        if (!empty($pathInfo['extension'])) {
                            $ext = strtolower(explode('?', $pathInfo['extension'])[0]);
                        }
                        $filename = 'banner_' . $page_key . '_' . time() . '.' . $ext;
                        if (file_put_contents($bannerDir . $filename, $imgData)) {
                            $banner_image = 'assets/uploads/banners/' . $filename;
                        }
                    }
                }

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE page_banners SET page_key=?, page_name=?, banner_image=?, badge_text=?, title=?, subtitle=?, status=? WHERE id=?");
                    $stmt->execute([$page_key, $page_name, $banner_image ?: null, $badge_text ?: null, $title ?: null, $subtitle ?: null, $status, $id]);
                    jsonResponse(true, null, "Banner for '{$page_name}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO page_banners (page_key, page_name, banner_image, badge_text, title, subtitle, status) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE page_name=VALUES(page_name), banner_image=VALUES(banner_image), badge_text=VALUES(badge_text), title=VALUES(title), subtitle=VALUES(subtitle), status=VALUES(status)");
                    $stmt->execute([$page_key, $page_name, $banner_image ?: null, $badge_text ?: null, $title ?: null, $subtitle ?: null, $status]);
                    jsonResponse(true, null, "Banner for '{$page_name}' saved successfully.");
                }
                break;

            case 'orders':
                $customer_name    = trim($input['customer_name'] ?? '');
                $customer_email   = trim($input['customer_email'] ?? '');
                $customer_phone   = trim($input['customer_phone'] ?? '');
                $total_amount     = floatval($input['total_amount'] ?? 0);
                $shipping_address = trim($input['shipping_address'] ?? '');
                $payment_status   = trim($input['payment_status'] ?? 'Pending');
                $order_status     = trim($input['order_status'] ?? 'Processing');
                $payment_method   = trim($input['payment_method'] ?? 'Direct / Admin Record');
                $notes            = trim($input['notes'] ?? '');

                if (empty($customer_name) || empty($customer_email) || $total_amount <= 0) {
                    jsonResponse(false, null, 'Customer name, email, and valid total amount are required.', 400);
                }

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE orders SET customer_name=?, customer_email=?, customer_phone=?, total_amount=?, shipping_address=?, payment_status=?, order_status=?, payment_method=?, notes=? WHERE id=?");
                    $stmt->execute([$customer_name, $customer_email, $customer_phone, $total_amount, $shipping_address, $payment_status, $order_status, $payment_method, $notes, $id]);
                    jsonResponse(true, null, "Order updated successfully.");
                } else {
                    $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
                    $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, total_amount, shipping_address, payment_status, order_status, payment_method, notes) VALUES (?,?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([$order_number, $customer_name, $customer_email, $customer_phone, $total_amount, $shipping_address, $payment_status, $order_status, $payment_method, $notes]);
                    jsonResponse(true, null, "Order '{$order_number}' recorded successfully.");
                }
                break;

            default:
                jsonResponse(false, null, 'Unknown entity specified.', 400);
        }
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database Error: ' . $e->getMessage(), 500);
    }
}

jsonResponse(false, null, 'Invalid API endpoint call.', 400);
