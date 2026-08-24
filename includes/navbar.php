<?php
// ============================================================
// Kamadhenu Goushala — Navbar Include
// $active_nav (string): 'home','about','cows','breeds','seva',
//                       'gallery','products','blog','events','contact'
// ============================================================
$base    = BASE_URL;
$current_lang = getCurrentLang();

$navItems = [
    'home'     => ['label' => __t('nav_home', 'HOME'),         'href' => 'index.php'],
    'about'    => ['label' => __t('nav_about', 'ABOUT'),       'href' => 'about.php'],
    'cows'     => ['label' => __t('nav_cows', 'OUR COWS'),     'href' => 'cows.php'],
    'breeds'   => ['label' => __t('nav_breeds', 'BREEDS'),     'href' => 'breeds.php'],
    'seva'     => ['label' => __t('nav_seva', 'GAU SEVA'),     'href' => 'seva.php'],
    'gallery'  => ['label' => __t('nav_gallery', 'GALLERY'),   'href' => 'gallery.php'],
    'products' => ['label' => __t('nav_products', 'PRODUCTS'), 'href' => 'products.php'],
    'blog'     => ['label' => __t('nav_blog', 'BLOG'),         'href' => 'blog.php'],
    'events'   => ['label' => __t('nav_events', 'EVENTS'),     'href' => 'events.php'],
    'contact'  => ['label' => __t('nav_contact', 'CONTACT'),   'href' => 'contact.php'],
];
$active_nav = $active_nav ?? 'home';

$site_logo        = getSetting('site_logo', '');
$site_name        = ($current_lang === 'kn' && (getSetting('site_name') === 'KAMADHENU' || getSetting('site_name') === SITE_NAME)) ? __t('brand_title', 'ಕಾಮಧೇನು') : getSetting('site_name', SITE_NAME);
$site_tagline     = ($current_lang === 'kn' && (getSetting('site_tagline') === 'GOUSHALA' || getSetting('site_tagline') === SITE_TAGLINE)) ? __t('brand_subtitle', 'ಗೋಶಾಲೆ') : getSetting('site_tagline', SITE_TAGLINE);

$brand_display    = getSetting('brand_display', 'both');
$logo_height      = intval(getSetting('logo_height', '34'));
if ($logo_height < 24 || $logo_height > 70) { $logo_height = 34; }

$title_size_key   = getSetting('brand_title_size', 'compact');
$title_font_size  = match($title_size_key) {
    'compact' => '1.08rem',
    'small'   => '1.15rem',
    'large'   => '1.35rem',
    default   => '1.2rem'
};

$show_nav_tagline = getSetting('show_nav_tagline', 'yes');
?>
<body class="<?= $current_lang === 'kn' ? 'lang-kn' : '' ?>">
  <!-- Main Header & Navbar -->
  <header class="main-header">
    <nav class="navbar navbar-expand-xl">
      <div class="container-fluid px-3 px-lg-4 px-xxl-5">
        <!-- Dark / Light Theme Toggle Button (Left of Brand Logo) -->
        <div class="d-flex align-items-center me-1 me-lg-2 flex-shrink-0">
          <button type="button" class="btn theme-toggle-btn" id="themeToggleBtn" title="Switch Dark / Light Theme" aria-label="Toggle Theme">
            <i class="bi bi-moon-stars-fill theme-icon-moon"></i>
            <i class="bi bi-sun-fill theme-icon-sun d-none"></i>
          </button>
        </div>

        <a class="navbar-brand d-flex align-items-center gap-2 me-1 me-xxl-3 flex-shrink-0" href="<?= $base ?>/index.php">
          <?php if (!empty($site_logo) && $brand_display !== 'text_only'): ?>
            <img src="<?= e(getImageUrl($site_logo)) ?>" 
                 alt="<?= e($site_name) ?>" 
                 title="<?= e($site_name) ?>" 
                 class="site-logo-img" 
                 style="height: <?= $logo_height ?>px; max-height: <?= $logo_height ?>px; width: auto; object-fit: contain; flex-shrink: 0;">
          <?php elseif ($brand_display !== 'logo_only' || empty($site_logo)): ?>
            <div class="brand-icon" style="width: 30px; height: 30px; font-size: 1rem; border-radius: 8px; flex-shrink: 0;"><i class="bi bi-heart-fill"></i></div>
          <?php endif; ?>

          <?php if ($brand_display !== 'logo_only'): ?>
            <div class="d-flex flex-column justify-content-center">
              <div class="fw-bold lh-1 brand-title-text" style="font-size: <?= $title_font_size ?>; color: var(--primary-dark); letter-spacing: 0.2px;"><?= e($site_name) ?></div>
              <?php if ($show_nav_tagline === 'yes' && !empty($site_tagline)): ?>
                <div class="brand-subtitle-text d-none d-xxl-block" style="font-size: 0.6rem; letter-spacing: 1.2px; color: var(--sacred-gold-dark); font-weight: 700; margin-top: 2px;"><?= e($site_tagline) ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </a>

        <div class="d-flex align-items-center gap-2 d-xl-none">
          <button type="button" class="btn theme-toggle-btn theme-mobile-btn p-1" title="Switch Theme" aria-label="Toggle Theme">
            <i class="bi bi-moon-stars-fill theme-icon-moon"></i>
            <i class="bi bi-sun-fill theme-icon-sun d-none"></i>
          </button>
          <button class="btn btn-outline-forest btn-sm py-1 px-2 fw-bold lang-quick-toggle-btn" type="button" title="Switch Language / ಭಾಷೆ">
            <i class="bi bi-translate text-gold me-1"></i><span class="lang-quick-label"><?= $current_lang === 'kn' ? 'English' : 'ಕನ್ನಡ' ?></span>
          </button>
          <a href="<?= $base ?>/donation.php" class="btn btn-gold btn-sm px-3 py-2"><?= __t('nav_donate', 'DONATE') ?></a>
          <button class="navbar-toggler border-0 shadow-none" type="button"
                  data-bs-toggle="offcanvas" data-bs-target="#mobileNavMenu">
            <span class="navbar-toggler-icon"></span>
          </button>
        </div>

        <div class="collapse navbar-collapse" id="desktopNav">
          <ul class="navbar-nav ms-auto me-1 me-xxl-2 mb-0 flex-nowrap align-items-center">
            <?php foreach ($navItems as $key => $item): ?>
              <li class="nav-item">
                <a class="nav-link <?= ($active_nav === $key ? 'active' : '') ?>"
                   href="<?= $base . '/' . $item['href'] ?>">
                  <?= e($item['label']) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="d-flex align-items-center gap-1 gap-xxl-2 flex-shrink-0">
            <div class="dropdown lang-header-dropdown">
              <button class="btn btn-outline-forest btn-sm dropdown-toggle lang-current-btn px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Language">
                <i class="bi bi-translate text-gold me-1"></i> <span class="lang-current-label"><?= $current_lang === 'kn' ? 'ಕನ್ನಡ' : 'English' ?></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                <li><a class="dropdown-item lang-select-item <?= $current_lang === 'en' ? 'active' : '' ?>" href="#" data-lang="en">🇬🇧 English</a></li>
                <li><a class="dropdown-item lang-select-item <?= $current_lang === 'kn' ? 'active' : '' ?>" href="#" data-lang="kn">🇮🇳 ಕನ್ನಡ (Kannada)</a></li>
              </ul>
            </div>
            <a href="<?= $base ?>/donation.php" class="btn btn-gold text-nowrap px-2 px-xxl-3 py-2">
              <i class="bi bi-heart-fill"></i> <?= __t('nav_donate_now', 'DONATE NOW') ?>
            </a>
          </div>
        </div>
      </div>
    </nav>
  </header>

  <!-- Mobile Offcanvas Menu -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNavMenu">
    <div class="offcanvas-header border-bottom">
      <div class="d-flex align-items-center gap-2">
        <?php if (!empty($site_logo) && $brand_display !== 'text_only'): ?>
          <img src="<?= e(getImageUrl($site_logo)) ?>" alt="<?= e($site_name) ?>" title="<?= e($site_name) ?>" style="height: 32px; max-height: 32px; width: auto; object-fit: contain;">
        <?php else: ?>
          <div class="brand-icon" style="width: 30px; height: 30px; font-size: 1rem; border-radius: 8px;"><i class="bi bi-heart-fill"></i></div>
        <?php endif; ?>
        <div>
          <div class="fw-bold lh-1 text-forest" style="font-size: 1.05rem;"><?= e($site_name) ?></div>
          <?php if ($show_nav_tagline === 'yes' && !empty($site_tagline)): ?>
            <div style="font-size: 0.6rem; letter-spacing: 1.5px; color: var(--sacred-gold-dark); font-weight: 700;"><?= e($site_tagline) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <button type="button" class="btn-close" data-bs-target="#mobileNavMenu" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <!-- Language Switcher in Drawer -->
      <div class="p-2 px-3 bg-light rounded-3 mb-3 border">
        <div class="small fw-bold text-muted mb-2 text-uppercase d-flex justify-content-between align-items-center">
          <span><i class="bi bi-translate text-gold me-1"></i> <?= __t('Language / ಭಾಷೆ', 'Language / ಭಾಷೆ') ?></span>
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm flex-fill lang-drawer-btn <?= $current_lang === 'en' ? 'btn-gold active' : 'btn-outline-forest' ?>" data-lang="en">English</button>
          <button type="button" class="btn btn-sm flex-fill lang-drawer-btn <?= $current_lang === 'kn' ? 'btn-gold active' : 'btn-outline-forest' ?>" data-lang="kn">ಕನ್ನಡ</button>
        </div>
      </div>
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
              <?= $item['label'] ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
      <a href="<?= $base ?>/donation.php" class="btn btn-gold w-100 py-3 mb-2">
        <i class="bi bi-heart-fill me-2"></i> <?= __t('btn_sponsor', 'SUPPORT GAU SEVA') ?>
      </a>
    </div>
  </div>
