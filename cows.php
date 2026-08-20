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
?>

  <section class="page-hero">
    <div class="container">
      <div class="hero-badge"><i class="bi bi-heart"></i> Our Sacred Residents</div>
      <h1 class="hero-title">Meet Our <span>Rescued Cows</span></h1>
      <p class="hero-subtitle">Every cow has a name, a story, and a loving home at Kamadhenu Goushala. Sponsor or adopt a cow to support their lifelong care.</p>
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
                  <div class="mt-auto">
                    <a href="<?= $base ?>/cow-details.php?id=<?= $cow['id'] ?>" class="btn btn-outline-gold w-100 py-2">VIEW DETAILS &amp; ADOPT</a>
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
