<?php
$page_title = 'Adopt / Sponsor a Cow'; $page_desc = 'Sponsor or adopt an indigenous cow at Kamadhenu Goushala.'; $active_nav = 'cows';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL; $cows = getActiveCows();
?>
  <section class="page-hero"><div class="container">
    <div class="hero-badge"><i class="bi bi-award"></i> Sacred Parenthood</div>
    <h1 class="hero-title">Adopt / <span>Sponsor a Cow</span></h1>
    <p class="hero-subtitle">Become a loving guardian. Receive monthly photo updates, health reports, and spiritual blessings in your family's name.</p>
  </div></section>

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
