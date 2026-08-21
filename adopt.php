<?php
$page_title = 'Adopt / Sponsor a Cow'; $page_desc = 'Sponsor or adopt an indigenous cow at Kamadhenu Goushala.'; $active_nav = 'cows';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL; $cows = getActiveCows();
$banner = getPageBanner('adopt');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>
  <section class="page-hero">
    <?php if (!empty($banner['banner_image'])): ?>
      <div class="page-hero-bg">
        <img src="<?= e(getImageUrl($banner['banner_image'])) ?>" 
             alt="<?= e($banner['page_name'] ?? 'Adopt a Cow') ?>" 
             class="page-hero-img" 
             fetchpriority="high" 
             loading="eager" 
             decoding="sync">
        <div class="page-hero-overlay"></div>
      </div>
    <?php endif; ?>
    <div class="container position-relative" style="z-index: 2;">
      <div class="hero-badge"><i class="bi bi-award"></i> <?= e($banner['badge_text'] ?? 'Sacred Parenthood') ?></div>
      <h1 class="hero-title"><?= $banner['title'] ?? 'Adopt / <span>Sponsor a Cow</span>' ?></h1>
      <p class="hero-subtitle"><?= e($banner['subtitle'] ?? "Become a loving guardian. Receive monthly photo updates, health reports, and spiritual blessings in your family's name.") ?></p>
    </div>
  </section>

  <section class="section-padding">
    <div class="container">
      <div class="row g-4">
        <?php foreach ($cows as $cow): ?>
        <div class="col-md-6 col-lg-4">
          <div class="cow-card">
            <div class="cow-image-wrapper">
              <img src="<?= e($cow['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($cow['name']) ?>">
              <span class="cow-badge badge-healthy"><?= e($cow['health_status']) ?></span>
            </div>
            <div class="cow-content">
              <h4 class="cow-name"><?= e($cow['name']) ?></h4>
              <div class="cow-breed-text"><?= e($cow['breed_name'] ?? 'Desi Breed') ?></div>
              <a href="<?= $base ?>/donation.php?cow_id=<?= $cow['id'] ?>&cow_name=<?= urlencode($cow['name']) ?>&seva_id=2&amount=2501" class="btn btn-gold w-100 py-2 mt-3">
                <i class="bi bi-heart-fill me-1"></i> ADOPT <?= e($cow['name']) ?>
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php include __DIR__ . '/includes/footer.php'; ?>
