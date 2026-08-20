<?php
$page_title = 'Gau Seva Packages'; $page_desc = 'Support sacred Gau Seva — feed a cow, adopt a cow, or fund medical care at Kamadhenu Goushala.'; $active_nav = 'seva';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL; $sevaList = getActiveSeva();
?>
  <section class="page-hero"><div class="container">
    <div class="hero-badge"><i class="bi bi-flower1"></i> Sacred Service</div>
    <h1 class="hero-title">Gau <span>Seva Packages</span></h1>
    <p class="hero-subtitle">Choose a seva that resonates with your heart. Every contribution directly supports nutrition, medical care, and shelter for our cows.</p>
  </div></section>

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
