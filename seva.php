<?php
$page_title = 'Gau Seva Packages'; $page_desc = 'Support sacred Gau Seva — feed a cow, adopt a cow, or fund medical care at Kamadhenu Goushala.'; $active_nav = 'seva';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL; $sevaList = getActiveSeva();
$banner = getPageBanner('seva');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>
  <section class="page-hero">
    <?php if (!empty($banner['banner_image'])): ?>
      <div class="page-hero-bg">
        <img src="<?= e(getImageUrl($banner['banner_image'])) ?>" 
             alt="<?= e($banner['page_name'] ?? 'Gau Seva') ?>" 
             class="page-hero-img" 
             fetchpriority="high" 
             loading="eager" 
             decoding="sync">
        <div class="page-hero-overlay"></div>
      </div>
    <?php endif; ?>
    <div class="container position-relative" style="z-index: 2;">
      <div class="hero-badge"><i class="bi bi-flower1"></i> <?= e($banner['badge_text'] ?? 'Sacred Service') ?></div>
      <h1 class="hero-title"><?= $banner['title'] ?? 'Gau <span>Seva Packages</span>' ?></h1>
      <p class="hero-subtitle"><?= e($banner['subtitle'] ?? 'Choose a seva that resonates with your heart. Every contribution directly supports nutrition, medical care, and shelter for our cows.') ?></p>
    </div>
  </section>

  <section class="section-padding">
    <div class="container">
      <div class="row g-4">
        <?php if (empty($sevaList)): ?>
          <p class="text-center text-muted fs-5 py-5">No seva packages available right now.</p>
        <?php else: ?>
          <?php foreach ($sevaList as $s): ?>
          <div class="col-md-6 col-lg-4">
            <div class="seva-card h-100">
              <?php if ($s['image']): ?>
              <img src="<?= e($s['image']) ?>" alt="<?= e($s['title']) ?>" class="rounded-3 mb-3 w-100" style="height:200px;object-fit:cover;">
              <?php endif; ?>
              <div class="seva-icon"><i class="bi <?= e($s['icon'] ?: 'bi-heart-fill') ?>"></i></div>
              <h4 class="seva-title"><?= e($s['title']) ?></h4>
              <div class="seva-amount">₹<?= number_format((float)$s['suggested_amount']) ?></div>
              <p class="seva-desc"><?= e($s['full_desc'] ?: $s['short_desc']) ?></p>
              <a href="<?= $base ?>/donation.php?seva_id=<?= $s['id'] ?>&amount=<?= $s['suggested_amount'] ?>" class="btn btn-gold w-100 py-2 mt-auto">OFFER THIS SEVA</a>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
<?php include __DIR__ . '/includes/footer.php'; ?>
