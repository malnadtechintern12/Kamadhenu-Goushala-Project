<?php
// ============================================================
// Kamadhenu Goushala — Cow Details Page
// ============================================================
require_once __DIR__ . '/includes/functions.php';
$base = BASE_URL;
$id = intInput($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: $base/cows.php"); exit; }

$stmt = $pdo->prepare("SELECT c.*, b.name AS breed_name, b.origin, b.milk_yield, b.characteristics FROM cows c LEFT JOIN breeds b ON c.breed_id = b.id WHERE c.id = ?");
$stmt->execute([$id]);
$cow = $stmt->fetch();
if (!$cow) { header("Location: $base/cows.php"); exit; }

$page_title = $cow['name'] . ' — ' . ($cow['breed_name'] ?? 'Desi Cow');
$page_desc  = truncate($cow['story'] ?? 'Meet ' . $cow['name'] . ' at Kamadhenu Goushala.');
$active_nav = 'cows';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

// Get additional images
$imgStmt = $pdo->prepare("SELECT * FROM cow_images WHERE cow_id = ? ORDER BY created_at");
$imgStmt->execute([$id]);
$extraImages = $imgStmt->fetchAll();

// Related cows
$relStmt = $pdo->prepare("SELECT c.*, b.name AS breed_name FROM cows c LEFT JOIN breeds b ON c.breed_id = b.id WHERE c.id != ? AND c.status = 'Active' ORDER BY RAND() LIMIT 3");
$relStmt->execute([$id]);
$related = $relStmt->fetchAll();

$age = $cow['dob'] ? (new DateTime($cow['dob']))->diff(new DateTime())->y . ' years' : 'Unknown';

$banner = getPageBanner('cow-details');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>

  <section class="page-hero" <?= $bannerBg ? 'style="'.$bannerBg.'"' : '' ?>>
    <div class="container">
      <nav aria-label="breadcrumb"><ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="<?= $base ?>/index.php" class="text-white-50">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= $base ?>/cows.php" class="text-white-50">Our Cows</a></li>
        <li class="breadcrumb-item active text-warning"><?= e($cow['name']) ?></li>
      </ol></nav>
      <h1 class="hero-title"><span><?= e($cow['name']) ?></span></h1>
      <p class="hero-subtitle"><?= e($cow['breed_name'] ?? 'Indigenous Breed') ?> • Tag: <?= e($cow['tag_number']) ?></p>
    </div>
  </section>

  <section class="section-padding">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-6">
          <img src="<?= e($cow['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($cow['name']) ?>" class="rounded-4 shadow-lg w-100" style="height:450px;object-fit:cover;">
          <?php if (!empty($extraImages)): ?>
          <div class="row g-2 mt-3">
            <?php foreach ($extraImages as $img): ?>
              <div class="col-4"><img src="<?= e($img['image_url']) ?>" alt="<?= e($img['caption'] ?? $cow['name']) ?>" class="rounded-3 w-100" style="height:100px;object-fit:cover;"></div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="col-lg-6">
          <span class="cow-badge badge-healthy mb-3 d-inline-block"><?= e($cow['health_status']) ?></span>
          <h2 class="section-title mb-1"><?= e($cow['name']) ?></h2>
          <p class="text-muted mb-4"><?= e($cow['breed_name'] ?? 'Indigenous Desi Cow') ?> from <?= e($cow['origin'] ?? 'India') ?></p>

          <div class="row g-3 mb-4">
            <div class="col-6"><div class="p-3 bg-light rounded-3 border"><div class="small text-muted">Tag Number</div><div class="fw-bold"><?= e($cow['tag_number']) ?></div></div></div>
            <div class="col-6"><div class="p-3 bg-light rounded-3 border"><div class="small text-muted">Gender</div><div class="fw-bold"><?= e($cow['gender']) ?></div></div></div>
            <div class="col-6"><div class="p-3 bg-light rounded-3 border"><div class="small text-muted">Age</div><div class="fw-bold"><?= e($age) ?></div></div></div>
            <div class="col-6"><div class="p-3 bg-light rounded-3 border"><div class="small text-muted">Arrival</div><div class="fw-bold"><?= $cow['arrival_date'] ? formatDate($cow['arrival_date']) : 'N/A' ?></div></div></div>
          </div>

          <?php if ($cow['story']): ?>
          <h5 class="fw-bold mb-2"><i class="bi bi-book me-2 text-warning"></i> Story</h5>
          <p class="text-muted mb-4"><?= e($cow['story']) ?></p>
          <?php endif; ?>

          <?php if ($cow['milk_yield']): ?>
          <div class="p-3 bg-light rounded-3 border mb-4">
            <div class="small text-muted">Breed Milk Yield</div>
            <div class="fw-bold text-forest"><?= e($cow['milk_yield']) ?></div>
          </div>
          <?php endif; ?>

          <div class="d-flex gap-3">
            <a href="<?= $base ?>/donation.php?cow_id=<?= $cow['id'] ?>&cow_name=<?= urlencode($cow['name']) ?>" class="btn btn-gold py-3 px-4">
              <i class="bi bi-heart-fill me-2"></i> SPONSOR <?= e($cow['name']) ?>
            </a>
            <a href="<?= $base ?>/cows.php" class="btn btn-outline-forest py-3 px-4">
              <i class="bi bi-arrow-left me-1"></i> All Cows
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Related Cows -->
  <?php if (!empty($related)): ?>
  <section class="section-padding bg-white">
    <div class="container">
      <h3 class="section-title text-center mb-4">Other Cows You May Love</h3>
      <div class="row g-4">
        <?php foreach ($related as $r): ?>
          <div class="col-md-4">
            <div class="cow-card">
              <div class="cow-image-wrapper">
                <img src="<?= e($r['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= e($r['name']) ?>">
                <span class="cow-badge badge-healthy"><?= e($r['health_status']) ?></span>
              </div>
              <div class="cow-content">
                <h4 class="cow-name"><?= e($r['name']) ?></h4>
                <div class="cow-breed-text"><?= e($r['breed_name'] ?? 'Desi Breed') ?></div>
                <a href="<?= $base ?>/cow-details.php?id=<?= $r['id'] ?>" class="btn btn-outline-gold w-100 py-2 mt-3">VIEW DETAILS</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
