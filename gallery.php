<?php
$page_title = 'Photo Gallery'; $page_desc = 'Beautiful moments from Kamadhenu Goushala — cows, events, seva, and campus life.'; $active_nav = 'gallery';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL;
try { $categories = $pdo->query("SELECT * FROM gallery_categories ORDER BY name")->fetchAll(); } catch (Exception $e) { $categories = []; }
$photos = getGalleryPhotos();
?>
  <section class="page-hero"><div class="container">
    <div class="hero-badge"><i class="bi bi-images"></i> Sacred Moments</div>
    <h1 class="hero-title">Photo <span>Gallery</span></h1>
    <p class="hero-subtitle">Glimpses of daily life, celebrations, and divine moments at our sanctuary.</p>
  </div></section>

  <section class="section-padding">
    <div class="container">
      <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
        <button class="btn btn-sm btn-forest active" data-gal-filter="all">All</button>
        <?php foreach ($categories as $c): ?>
          <button class="btn btn-sm btn-outline-forest" data-gal-filter="<?= $c['id'] ?>"><?= e($c['name']) ?></button>
        <?php endforeach; ?>
      </div>
      <div class="row g-3" id="galleryGrid">
        <?php foreach ($photos as $g): ?>
        <div class="col-6 col-md-4 col-lg-3 gal-item" data-cat="<?= $g['category_id'] ?>">
          <div class="gallery-card">
            <img src="<?= e($g['image_url']) ?>" alt="<?= e($g['title']) ?>" loading="lazy">
            <div class="gallery-overlay"><div class="fw-bold small"><?= e($g['title']) ?></div><div class="small text-white-50"><?= e($g['category_name'] ?? '') ?></div></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php
$extra_js = '<script>
document.querySelectorAll("[data-gal-filter]").forEach(btn => {
  btn.addEventListener("click", function() {
    document.querySelectorAll("[data-gal-filter]").forEach(b => { b.classList.remove("active","btn-forest"); b.classList.add("btn-outline-forest"); });
    this.classList.add("active","btn-forest"); this.classList.remove("btn-outline-forest");
    const f = this.dataset.galFilter;
    document.querySelectorAll(".gal-item").forEach(i => { i.style.display = (f === "all" || i.dataset.cat === f) ? "" : "none"; });
  });
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
