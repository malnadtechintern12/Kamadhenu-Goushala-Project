<?php
// ============================================================
// Admin Panel — Shared Header (Sidebar + Top Bar)
// $admin_page (string): active sidebar item
// $admin_title (string): page title
// ============================================================
$base = BASE_URL;
$adminName  = $_SESSION['admin_name'] ?? 'Admin';
$adminEmail = $_SESSION['admin_email'] ?? '';
$adminRole  = $_SESSION['admin_role'] ?? 'admin';

$sidebarItems = [
    'dashboard'    => ['icon'=>'bi-speedometer2',   'label'=>'Dashboard',     'href'=>'dashboard.php'],
    'cows'         => ['icon'=>'bi-heart',          'label'=>'Cows',          'href'=>'cows.php'],
    'breeds'       => ['icon'=>'bi-bookmark-star',  'label'=>'Breeds',        'href'=>'breeds.php'],
    'seva'         => ['icon'=>'bi-flower1',        'label'=>'Seva Packages', 'href'=>'seva.php'],
    'donations'    => ['icon'=>'bi-cash-stack',     'label'=>'Donations',     'href'=>'donations.php'],
    'sponsors'     => ['icon'=>'bi-people',         'label'=>'Sponsors',      'href'=>'sponsors.php'],
    'products'     => ['icon'=>'bi-bag-check',      'label'=>'Products',      'href'=>'products.php'],
    'orders'       => ['icon'=>'bi-receipt',        'label'=>'Orders',        'href'=>'orders.php'],
    'blogs'        => ['icon'=>'bi-newspaper',      'label'=>'Blog Posts',    'href'=>'blogs.php'],
    'events'       => ['icon'=>'bi-calendar-event', 'label'=>'Events',       'href'=>'events.php'],
    'gallery'      => ['icon'=>'bi-images',         'label'=>'Gallery',       'href'=>'gallery.php'],
    'testimonials' => ['icon'=>'bi-chat-quote',     'label'=>'Testimonials',  'href'=>'testimonials.php'],
    'timeline'     => ['icon'=>'bi-clock-history',  'label'=>'Timeline',      'href'=>'timeline.php'],
    'messages'     => ['icon'=>'bi-envelope',       'label'=>'Messages',      'href'=>'messages.php'],
    'newsletter'   => ['icon'=>'bi-megaphone',      'label'=>'Newsletter',    'href'=>'newsletter.php'],
    'banners'      => ['icon'=>'bi-image',          'label'=>'Page Banners',  'href'=>'banners.php'],
    'settings'     => ['icon'=>'bi-gear',           'label'=>'Settings',      'href'=>'settings.php'],
    'profile'      => ['icon'=>'bi-person-circle',  'label'=>'Profile',       'href'=>'profile.php'],
];
$admin_page = $admin_page ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($admin_title ?? 'Admin Panel') ?> | Kamadhenu Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/variables.css">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/admin.css">
</head>
<body class="admin-body">

  <!-- Sidebar -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
      <i class="bi bi-heart-fill text-warning"></i>
      <span>KAMADHENU</span>
      <small>ADMIN</small>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($sidebarItems as $key => $item): ?>
        <a href="<?= $base ?>/admin/<?= $item['href'] ?>" class="sidebar-link <?= $admin_page === $key ? 'active' : '' ?>">
          <i class="bi <?= $item['icon'] ?>"></i>
          <span><?= e($item['label']) ?></span>
        </a>
      <?php endforeach; ?>
      <hr class="mx-3 my-2 border-secondary">
      <a href="<?= $base ?>/admin/logout.php" class="sidebar-link text-danger">
        <i class="bi bi-box-arrow-left"></i>
        <span>Logout</span>
      </a>
    </nav>
  </aside>

  <!-- Main Content Area -->
  <div class="admin-main">
    <!-- Top Bar -->
    <header class="admin-topbar">
      <button class="btn btn-sm border-0 d-lg-none" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
      <h5 class="fw-bold mb-0 ms-2"><?= e($admin_title ?? 'Dashboard') ?></h5>
      <div class="ms-auto d-flex align-items-center gap-3">
        <div class="dropdown">
          <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
            <div class="admin-avatar"><i class="bi bi-person-fill"></i></div>
            <span class="d-none d-md-inline fw-semibold"><?= e($adminName) ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow border-0">
            <li><span class="dropdown-item-text small text-muted"><?= e($adminEmail) ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= $base ?>/admin/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><a class="dropdown-item" href="<?= $base ?>/admin/settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= $base ?>/admin/logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
          </ul>
        </div>
      </div>
    </header>

    <div class="admin-content">
