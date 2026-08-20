<?php
// ============================================================
// Kamadhenu Goushala — Navbar Include
// $active_nav (string): 'home','about','cows','breeds','seva',
//                       'gallery','products','blog','events','contact'
// ============================================================
$base    = BASE_URL;
$phone   = getSetting('phone_primary', SITE_PHONE);
$email   = getSetting('email_primary', SITE_EMAIL);
$wp_num  = getSetting('whatsapp_number', '919845088990');
$address = getSetting('address', 'Bengaluru, Karnataka, India');
$loc     = explode(',', $address);
$city    = trim($loc[count($loc)-2] ?? 'Karnataka') . ', India';

$navItems = [
    'home'     => ['label' => 'HOME',     'href' => 'index.php'],
    'about'    => ['label' => 'ABOUT',    'href' => 'about.php'],
    'cows'     => ['label' => 'OUR COWS','href' => 'cows.php'],
    'breeds'   => ['label' => 'BREEDS',  'href' => 'breeds.php'],
    'seva'     => ['label' => 'GAU SEVA','href' => 'seva.php'],
    'gallery'  => ['label' => 'GALLERY', 'href' => 'gallery.php'],
    'products' => ['label' => 'PRODUCTS','href' => 'products.php'],
    'blog'     => ['label' => 'BLOG',    'href' => 'blog.php'],
    'events'   => ['label' => 'EVENTS',  'href' => 'events.php'],
    'contact'  => ['label' => 'CONTACT', 'href' => 'contact.php'],
];
$active_nav = $active_nav ?? 'home';
?>
<body>

  <!-- Top Information Bar -->
  <div class="top-infobar d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="d-flex gap-4">
        <span><i class="bi bi-telephone-fill"></i> <a href="tel:<?= e(preg_replace('/[^0-9+]/','',$phone)) ?>" class="site-phone"><?= e($phone) ?></a></span>
        <span><i class="bi bi-envelope-fill"></i> <a href="mailto:<?= e($email) ?>" class="site-email"><?= e($email) ?></a></span>
        <span><i class="bi bi-geo-alt-fill"></i> <span class="site-address"><?= e($city) ?></span></span>
      </div>
      <div class="d-flex gap-3 align-items-center">
        <a href="https://wa.me/<?= e($wp_num) ?>" target="_blank" rel="noopener" class="whatsapp-link text-white">
          <i class="bi bi-whatsapp"></i> WhatsApp
        </a>
      </div>
    </div>
  </div>

  <!-- Main Header & Navbar -->
  <header class="main-header">
    <nav class="navbar navbar-expand-lg">
      <div class="container">
        <a class="navbar-brand" href="<?= $base ?>/index.php">
          <div class="brand-icon"><i class="bi bi-heart-fill"></i></div>
          <div>
            <div class="fw-bold lh-1" style="font-size:1.35rem;color:var(--primary-dark);">KAMADHENU</div>
            <div style="font-size:0.75rem;letter-spacing:2px;color:var(--sacred-gold-dark);font-weight:700;">GOUSHALA</div>
          </div>
        </a>

        <div class="d-flex align-items-center gap-2 d-lg-none">
          <a href="<?= $base ?>/donation.php" class="btn btn-gold btn-sm px-3 py-2">DONATE</a>
          <button class="navbar-toggler border-0 shadow-none" type="button"
                  data-bs-toggle="offcanvas" data-bs-target="#mobileNavMenu">
            <span class="navbar-toggler-icon"></span>
          </button>
        </div>

        <div class="collapse navbar-collapse" id="desktopNav">
          <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1">
            <?php foreach ($navItems as $key => $item): ?>
              <li class="nav-item">
                <a class="nav-link <?= ($active_nav === $key ? 'active' : '') ?>"
                   href="<?= $base . '/' . $item['href'] ?>">
                  <?= e($item['label']) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="d-flex align-items-center gap-3">
            <a href="<?= $base ?>/donation.php" class="btn btn-gold">
              <i class="bi bi-heart-fill"></i> DONATE NOW
            </a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- Mobile Offcanvas Menu -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNavMenu">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title fw-bold text-forest">Kamadhenu Goushala</h5>
      <button type="button" class="btn-close" data-bs-target="#mobileNavMenu" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="nav flex-column gap-2 mb-4">
        <?php
        $mobileIcons = [
          'home'=>'bi-house','about'=>'bi-info-circle','cows'=>'bi-heart',
          'breeds'=>'bi-bookmark-star','seva'=>'bi-flower1','gallery'=>'bi-images',
          'products'=>'bi-bag-check','blog'=>'bi-newspaper','events'=>'bi-calendar-event',
          'contact'=>'bi-telephone'
        ];
        foreach ($navItems as $key => $item): ?>
          <li class="nav-item">
            <a class="nav-link fs-6 fw-bold <?= ($active_nav === $key ? 'active' : '') ?>"
               href="<?= $base . '/' . $item['href'] ?>">
              <i class="bi <?= $mobileIcons[$key] ?> me-2"></i>
              <?= ucfirst(strtolower($item['label'])) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <a href="<?= $base ?>/donation.php" class="btn btn-gold w-100 py-3 mb-2">
        <i class="bi bi-heart-fill me-2"></i> SUPPORT GAU SEVA
      </a>
    </div>
  </div>
