<?php
// ============================================================
// Kamadhenu Goushala — Our Cows Page
// ============================================================
$page_title = 'Our Cows';
$page_desc  = 'Meet the rescued indigenous cows living at Kamadhenu Goushala. Sponsor or adopt a cow today.';
$active_nav = 'cows';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

$base = BASE_URL;
$cows = getActiveCows();
$breeds = getActiveBreeds();
$wp_num = getSetting('whatsapp_number', '919845088990');

$banner = getPageBanner('cows');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>

  <section class="page-hero">
    <?php if (!empty($banner['banner_image'])): ?>
      <div class="page-hero-bg">
        <img src="<?= e(getImageUrl($banner['banner_image'])) ?>" 
             alt="<?= e($banner['page_name'] ?? 'Our Cows') ?>" 
             class="page-hero-img" 
             fetchpriority="high" 
             loading="eager" 
             decoding="sync">
        <div class="page-hero-overlay"></div>
      </div>
    <?php endif; ?>
    <div class="container position-relative" style="z-index: 2;">
      <div class="hero-badge"><i class="bi bi-heart"></i> <?= e($banner['badge_text'] ?? 'Our Sacred Residents') ?></div>
      <h1 class="hero-title"><?= $banner['title'] ?? 'Meet Our <span>Rescued Cows</span>' ?></h1>
      <p class="hero-subtitle"><?= e($banner['subtitle'] ?? 'Every cow has a name, a story, and a loving home at Kamadhenu Goushala. Sponsor or adopt a cow to support their lifelong care.') ?></p>
    </div>
  </section>

  <section class="section-padding">
    <div class="container">
      <!-- Filter -->
      <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
        <button class="btn btn-sm btn-forest active" data-filter="all">All Cows</button>
        <?php foreach ($breeds as $b): ?>
          <button class="btn btn-sm btn-outline-forest" data-filter="<?= $b['id'] ?>"><?= e($b['name']) ?></button>
        <?php endforeach; ?>
      </div>

      <div class="row g-4" id="cowsGrid">
        <?php if (empty($cows)): ?>
          <div class="col-12 text-center py-5"><p class="text-muted fs-5">No cows found. Check back soon!</p></div>
        <?php else: ?>
          <?php foreach ($cows as $cow): ?>
            <div class="col-md-6 col-lg-4 cow-item" data-breed="<?= $cow['breed_id'] ?>">
              <div class="cow-card">
                <div class="cow-image-wrapper">
                  <img src="<?= e($cow['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($cow['name']) ?>">
                  <span class="cow-badge badge-healthy"><?= e($cow['health_status']) ?></span>
                  <span class="cow-tag-badge"><?= e($cow['tag_number']) ?></span>
                </div>
                <div class="cow-content">
                  <h4 class="cow-name"><?= e($cow['name']) ?></h4>
                  <div class="cow-breed-text"><?= e($cow['breed_name'] ?? 'Desi Breed') ?></div>
                  <div class="cow-meta-grid">
                    <div class="cow-meta-item"><i class="bi bi-gender-ambiguous text-warning"></i> <?= e($cow['gender']) ?></div>
                    <div class="cow-meta-item"><i class="bi bi-shield-check text-success"></i> <?= e($cow['status']) ?></div>
                  </div>
                  <p class="small text-muted mb-4" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                    <?= e($cow['story'] ?: 'A gentle resident cow at Kamadhenu Goushala.') ?>
                  </p>
                  <?php 
                  $cowWp = !empty($cow['whatsapp_number']) ? preg_replace('/[^0-9]/', '', $cow['whatsapp_number']) : preg_replace('/[^0-9]/', '', $wp_num);
                  if (empty($cowWp)) { $cowWp = '919845088990'; }
                  $cowBreedName = $cow['breed_name'] ?? 'Desi Indigenous Breed';
                  $cowImgUrl    = !empty($cow['image']) ? getImageUrl($cow['image']) : '';
                  $cowPageUrl   = $base . '/cow-details.php?id=' . $cow['id'];

                  // 1. Adopt Now Message with Text, Photo, and Link
                  $adoptMsg = "🐮 *COW ADOPTION ENQUIRY — Kamadhenu Goushala*\n"
                            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                            . "🏷️ *Cow Name:* " . $cow['name'] . "\n"
                            . "🔖 *Tag Number:* " . $cow['tag_number'] . "\n"
                            . "🐂 *Breed:* " . $cowBreedName . "\n"
                            . "⚤ *Gender:* " . $cow['gender'] . "\n"
                            . "🩺 *Health:* " . $cow['health_status'] . "\n";
                  if (!empty($cowImgUrl)) {
                      $adoptMsg .= "🖼️ *Photo:* " . $cowImgUrl . "\n";
                  }
                  $adoptMsg .= "🔗 *Profile Link:* " . $cowPageUrl . "\n"
                            . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                            . "Namaste! I would like to adopt / sponsor " . $cow['name'] . " at Kamadhenu Goushala. Please share the adoption procedure and details. 🙏";

                  // 2. Feed Now Message with Text, Photo, and Link
                  $feedMsg = "🌿 *SPONSOR COW FEED / FODDER — Kamadhenu Goushala*\n"
                           . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                           . "🏷️ *Cow Name:* " . $cow['name'] . "\n"
                           . "🔖 *Tag Number:* " . $cow['tag_number'] . "\n"
                           . "🐂 *Breed:* " . $cowBreedName . "\n"
                           . "⚤ *Gender:* " . $cow['gender'] . "\n"
                           . "🩺 *Health:* " . $cow['health_status'] . "\n";
                  if (!empty($cowImgUrl)) {
                      $feedMsg .= "🖼️ *Photo:* " . $cowImgUrl . "\n";
                  }
                  $feedMsg .= "🔗 *Profile Link:* " . $cowPageUrl . "\n"
                           . "━━━━━━━━━━━━━━━━━━━━━━━━\n"
                           . "Namaste! I would like to sponsor nutritious green fodder & feed for " . $cow['name'] . " at Kamadhenu Goushala. Please guide me on how to contribute. 🙏";
                  ?>
                  <div class="mt-auto d-flex flex-column gap-2">
                    <a href="<?= $cowPageUrl ?>" class="btn btn-outline-forest w-100 py-2 fw-semibold">
                      <i class="bi bi-eye me-1"></i> View Full Profile
                    </a>
                    <div class="d-flex gap-2">
                      <a href="https://wa.me/<?= $cowWp ?>?text=<?= rawurlencode($adoptMsg) ?>" target="_blank" rel="noopener" class="btn btn-gold flex-fill py-2 fw-bold text-nowrap" style="font-size: 0.85rem;">
                        <i class="bi bi-whatsapp me-1"></i> Adopt Now
                      </a>
                      <a href="https://wa.me/<?= $cowWp ?>?text=<?= rawurlencode($feedMsg) ?>" target="_blank" rel="noopener" class="btn btn-forest flex-fill py-2 fw-bold text-nowrap" style="font-size: 0.85rem;">
                        <i class="bi bi-whatsapp me-1"></i> Feed Now
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

<?php
$extra_js = '<script>
document.querySelectorAll("[data-filter]").forEach(btn => {
  btn.addEventListener("click", function() {
    document.querySelectorAll("[data-filter]").forEach(b => b.classList.remove("active","btn-forest"));
    document.querySelectorAll("[data-filter]").forEach(b => b.classList.add("btn-outline-forest"));
    this.classList.add("active","btn-forest");
    this.classList.remove("btn-outline-forest");
    const filter = this.dataset.filter;
    document.querySelectorAll(".cow-item").forEach(item => {
      item.style.display = (filter === "all" || item.dataset.breed === filter) ? "" : "none";
    });
  });
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
