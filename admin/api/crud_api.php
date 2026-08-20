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
        'contact_messages', 'newsletter_subscribers', 'orders', 'donations'
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

// 2. TOGGLE STATUS ACTION
if (isset($_GET['action']) && $_GET['action'] === 'toggle_status') {
    $entity = trim($_GET['entity'] ?? '');
    $id     = intInput($_GET['id'] ?? 0);
    $status = trim($_GET['status'] ?? '');

    $allowedTables = [
        'breeds', 'seva', 'products', 'blogs', 'events', 
        'gallery', 'testimonials', 'timeline', 'newsletter_subscribers', 'contact_messages'
    ];

    if (!in_array($entity, $allowedTables) || $id <= 0 || empty($status)) {
        jsonResponse(false, null, 'Invalid request.', 400);
    }

    try {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE `{$entity}` SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        jsonResponse(true, null, 'Status updated to ' . htmlspecialchars($status));
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
                $image = $uploadedImagePath ?: trim($input['image'] ?? '');
                $status = $input['status'] ?? 'active';

                if (empty($name)) jsonResponse(false, null, 'Breed name is required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE breeds SET name=?, origin=?, description=?, milk_yield=?, characteristics=?, image=?, status=? WHERE id=?");
                    $stmt->execute([$name, $origin, $description, $milk_yield, $characteristics, $image, $status, $id]);
                    jsonResponse(true, null, "Breed '{$name}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO breeds (name, origin, description, milk_yield, characteristics, image, status) VALUES (?,?,?,?,?,?,?)");
                    $stmt->execute([$name, $origin, $description, $milk_yield, $characteristics, $image, $status]);
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
                $status = $input['status'] ?? 'active';

                if (empty($name)) jsonResponse(false, null, 'Product name is required.', 400);

                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, category_id=?, description=?, price=?, stock=?, image=?, status=? WHERE id=?");
                    $stmt->execute([$name, $slug, $category_id, $description, $price, $stock, $image, $status, $id]);
                    jsonResponse(true, null, "Product '{$name}' updated successfully.");
                } else {
                    $stmt = $pdo->prepare("INSERT INTO products (name, slug, category_id, description, price, stock, image, status) VALUES (?,?,?,?,?,?,?,?)");
                    $stmt->execute([$name, $slug, $category_id, $description, $price, $stock, $image, $status]);
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

            default:
                jsonResponse(false, null, 'Unknown entity specified.', 400);
        }
    } catch (PDOException $e) {
        jsonResponse(false, null, 'Database Error: ' . $e->getMessage(), 500);
    }
}

jsonResponse(false, null, 'Invalid API endpoint call.', 400);
