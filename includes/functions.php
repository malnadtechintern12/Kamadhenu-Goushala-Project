<?php
// ============================================================
// Kamadhenu Goushala — Helper Functions
// ============================================================

require_once __DIR__ . '/../config/database.php';

/**
 * Fetch all active website settings from DB as key→value map.
 * Falls back to constants if DB unavailable.
 */
function getSettings(): array {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['setting_key']] = $r['setting_value'];
        }
        return $map;
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get a single setting value with optional fallback.
 */
function getSetting(string $key, string $fallback = ''): string {
    global $_SETTINGS_CACHE;
    if (!isset($_SETTINGS_CACHE)) {
        $_SETTINGS_CACHE = getSettings();
    }
    return $_SETTINGS_CACHE[$key] ?? $fallback;
}

// Pre-load settings into global cache on first include
global $_SETTINGS_CACHE;
$_SETTINGS_CACHE = getSettings();

/**
 * Sanitize output for HTML display.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Safe integer from user input.
 */
function intInput(mixed $val, int $default = 0): int {
    return filter_var($val, FILTER_VALIDATE_INT) !== false ? (int)$val : $default;
}

/**
 * JSON API response helper.
 */
function jsonResponse(bool $success, mixed $data = null, string $message = '', int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Return all active cows with breed name joined.
 */
function getActiveCows(int $limit = 0, string $status = 'Active'): array {
    global $pdo;
    $sql = "SELECT c.*, b.name AS breed_name 
            FROM cows c 
            LEFT JOIN breeds b ON c.breed_id = b.id 
            WHERE c.status = ?
            ORDER BY c.created_at DESC";
    if ($limit > 0) $sql .= " LIMIT $limit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$status]);
    return $stmt->fetchAll();
}

/**
 * Return active seva/sponsorship packages.
 */
function getActiveSeva(int $limit = 0): array {
    global $pdo;
    $sql = "SELECT * FROM seva WHERE status = 'active' ORDER BY display_order ASC";
    if ($limit > 0) $sql .= " LIMIT $limit";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Return active testimonials.
 */
function getTestimonials(int $limit = 6): array {
    global $pdo;
    $stmt = $pdo->prepare(
        "SELECT * FROM testimonials WHERE status='active' ORDER BY display_order ASC LIMIT ?"
    );
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Return published blogs with category name.
 */
function getPublishedBlogs(int $limit = 0): array {
    global $pdo;
    $sql = "SELECT b.*, bc.name AS category_name 
            FROM blogs b 
            LEFT JOIN blog_categories bc ON b.category_id = bc.id 
            WHERE b.status = 'Published'
            ORDER BY b.published_at DESC";
    if ($limit > 0) $sql .= " LIMIT $limit";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Return upcoming and recent events.
 */
function getEvents(int $limit = 0): array {
    global $pdo;
    $sql = "SELECT * FROM events WHERE status != 'Cancelled' ORDER BY event_date ASC";
    if ($limit > 0) $sql .= " LIMIT $limit";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Return active gallery photos with category name.
 */
function getGalleryPhotos(int $limit = 0, ?int $categoryId = null): array {
    global $pdo;
    if ($categoryId) {
        $sql = "SELECT g.*, gc.name AS category_name 
                FROM gallery g 
                LEFT JOIN gallery_categories gc ON g.category_id = gc.id 
                WHERE g.status = 'active' AND g.category_id = ?
                ORDER BY g.display_order ASC";
        if ($limit > 0) $sql .= " LIMIT $limit";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll();
    }
    $sql = "SELECT g.*, gc.name AS category_name 
            FROM gallery g 
            LEFT JOIN gallery_categories gc ON g.category_id = gc.id 
            WHERE g.status = 'active'
            ORDER BY g.display_order ASC";
    if ($limit > 0) $sql .= " LIMIT $limit";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Return active products with category name.
 */
function getActiveProducts(int $limit = 0): array {
    global $pdo;
    $sql = "SELECT p.*, pc.name AS category_name 
            FROM products p 
            LEFT JOIN product_categories pc ON p.category_id = pc.id 
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC";
    if ($limit > 0) $sql .= " LIMIT $limit";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Return active cow breeds.
 */
function getActiveBreeds(int $limit = 0): array {
    global $pdo;
    $sql = "SELECT * FROM breeds WHERE status='active' ORDER BY name ASC";
    if ($limit > 0) $sql .= " LIMIT $limit";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Get donation stats for homepage counters.
 */
function getDonationStats(): array {
    global $pdo;
    try {
        $total = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE payment_status='Completed'")->fetchColumn();
        $count = $pdo->query("SELECT COUNT(*) FROM donations WHERE payment_status='Completed'")->fetchColumn();
        $cowCount = $pdo->query("SELECT COUNT(*) FROM cows WHERE status='Active'")->fetchColumn();
        $breedCount = $pdo->query("SELECT COUNT(*) FROM breeds WHERE status='active'")->fetchColumn();
        return [
            'total_donated' => number_format((float)$total),
            'donor_count'   => (int)$count,
            'cow_count'     => (int)$cowCount,
            'breed_count'   => (int)$breedCount,
        ];
    } catch (Exception $e) {
        return ['total_donated'=>'0','donor_count'=>0,'cow_count'=>0,'breed_count'=>0];
    }
}

/**
 * Generate pagination HTML.
 */
function paginate(int $total, int $perPage, int $currentPage, string $baseUrl): string {
    $totalPages = (int)ceil($total / $perPage);
    if ($totalPages <= 1) return '';
    $html = '<nav><ul class="pagination justify-content-center gap-1">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i === $currentPage) ? ' active' : '';
        $html .= "<li class=\"page-item$active\"><a class=\"page-link\" href=\"{$baseUrl}?page={$i}\">{$i}</a></li>";
    }
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Format date for display.
 */
function formatDate(string $date, string $format = 'd M Y'): string {
    return date($format, strtotime($date));
}

/**
 * Truncate text with ellipsis.
 */
function truncate(string $text, int $chars = 150): string {
    $text = strip_tags($text);
    if (strlen($text) <= $chars) return $text;
    return rtrim(substr($text, 0, $chars)) . '…';
}

/**
 * Generate a unique donation number.
 */
function generateDonationNumber(): string {
    return 'DON-' . date('Ym') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/**
 * Return fully qualified URL for images (works for external URLs and uploaded local files).
 */
function getImageUrl(?string $path, string $fallback = ''): string {
    if (empty($path)) return $fallback;
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
        return $path;
    }
    $cleanPath = ltrim($path, '/');
    return BASE_URL . '/' . $cleanPath;
}

/**
 * Return active banner information for a specific page key.
 */
function getPageBanner(string $pageKey): ?array {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM page_banners WHERE page_key = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$pageKey]);
        $row = $stmt->fetch();
        return $row ?: null;
    } catch (Exception $e) {
        return null;
    }
}


