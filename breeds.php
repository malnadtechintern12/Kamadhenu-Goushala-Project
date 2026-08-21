<?php
$page_title = 'Indigenous Cow Breeds'; $page_desc = 'Explore India\'s sacred indigenous cow breeds preserved at Kamadhenu Goushala.'; $active_nav = 'breeds';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL; $breeds = getActiveBreeds();
$banner = getPageBanner('breeds');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>
  <section class="page-hero">
    <?php if (!empty($banner['banner_image'])): ?>
      <div class="page-hero-bg">
        <img src="<?= e(getImageUrl($banner['banner_image'])) ?>" 
             alt="<?= e($banner['page_name'] ?? 'Breeds') ?>" 
             class="page-hero-img" 
             fetchpriority="high" 
             loading="eager" 
             decoding="sync">
        <div class="page-hero-overlay"></div>
      </div>
    <?php endif; ?>
    <div class="container position-relative" style="z-index: 2;">
      <div class="hero-badge"><i class="bi bi-bookmark-star"></i> <?= e($banner['badge_text'] ?? 'Heritage Conservation') ?></div>
      <h1 class="hero-title"><?= $banner['title'] ?? 'Indigenous <span>Desi Cow Breeds</span>' ?></h1>
      <p class="hero-subtitle"><?= e($banner['subtitle'] ?? "Discover the sacred heritage, unique characteristics, and medicinal A2 milk qualities of India's native Zebu cattle.") ?></p>
    </div>
  </section>

  <section class="section-padding">
    <div class="container">
      <?php if (empty($breeds)): ?>
        <p class="text-center text-muted fs-5 py-5">No breeds found.</p>
      <?php else: ?>
        <?php foreach ($breeds as $idx => $b): ?>
        <div class="row align-items-center g-5 mb-5 <?= $idx % 2 !== 0 ? 'flex-row-reverse' : '' ?>">
          <div class="col-lg-5">
            <img src="<?= e($b['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($b['name']) ?>" class="rounded-4 shadow-lg w-100" style="height:380px;object-fit:cover;">
          </div>
          <div class="col-lg-7">
            <span class="breed-origin-badge d-inline-block mb-2"><?= e($b['origin'] ?: 'India') ?></span>
            <h2 class="section-title mb-2"><?= e($b['name']) ?></h2>
            <p class="text-muted mb-3"><?= e($b['description'] ?? '') ?></p>
            <?php if ($b['milk_yield']): ?>
            <div class="p-3 bg-light rounded-3 border mb-3"><div class="small text-muted">Milk Yield</div><div class="fw-bold text-forest"><?= e($b['milk_yield']) ?></div></div>
            <?php endif; ?>
            <?php if ($b['characteristics']): ?>
            <div class="p-3 bg-light rounded-3 border mb-3"><div class="small text-muted">Characteristics</div><div class="fw-bold"><?= e($b['characteristics']) ?></div></div>
            <?php endif; ?>
            <a href="<?= $base ?>/cows.php" class="btn btn-outline-gold px-4 py-2">See <?= e($b['name']) ?> Cows</a>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
<?php include __DIR__ . '/includes/footer.php'; ?>
