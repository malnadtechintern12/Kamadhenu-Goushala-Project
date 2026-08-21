<?php
require_once __DIR__ . '/includes/functions.php';
$base = BASE_URL;
$slug = trim($_GET['slug'] ?? '');
if (empty($slug)) { header("Location: $base/blog.php"); exit; }
$stmt = $pdo->prepare("SELECT b.*, bc.name AS category_name FROM blogs b LEFT JOIN blog_categories bc ON b.category_id = bc.id WHERE b.slug = ? AND b.status = 'Published'");
$stmt->execute([$slug]);
$blog = $stmt->fetch();
if (!$blog) { header("Location: $base/blog.php"); exit; }

$page_title = $blog['title']; $page_desc = truncate($blog['excerpt'] ?? $blog['title'], 160); $active_nav = 'blog';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';

$relStmt = $pdo->prepare("SELECT * FROM blogs WHERE id != ? AND status = 'Published' ORDER BY published_at DESC LIMIT 3");
$relStmt->execute([$blog['id']]);
$related = $relStmt->fetchAll();

$banner = getPageBanner('blog-details');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>
  <section class="page-hero" <?= $bannerBg ? 'style="'.$bannerBg.'"' : '' ?>><div class="container">
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-2">
      <li class="breadcrumb-item"><a href="<?= $base ?>/index.php" class="text-white-50">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= $base ?>/blog.php" class="text-white-50">Blog</a></li>
      <li class="breadcrumb-item active text-warning"><?= e(truncate($blog['title'], 40)) ?></li>
    </ol></nav>
    <h1 class="hero-title" style="font-size:2rem;"><?= e($blog['title']) ?></h1>
    <div class="d-flex gap-3 mt-3 flex-wrap">
      <span class="text-white-50"><i class="bi bi-calendar3 me-1"></i> <?= formatDate($blog['published_at'] ?? $blog['created_at']) ?></span>
      <span class="text-white-50"><i class="bi bi-person me-1"></i> <?= e($blog['author'] ?? 'Kamadhenu Team') ?></span>
      <?php if ($blog['category_name']): ?><span class="badge bg-warning text-dark"><?= e($blog['category_name']) ?></span><?php endif; ?>
    </div>
  </div></section>

  <section class="section-padding">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-8">
          <?php if ($blog['featured_image']): ?>
            <img src="<?= e($blog['featured_image']) ?>" alt="<?= e($blog['title']) ?>" class="rounded-4 shadow-sm w-100 mb-4" style="max-height:450px;object-fit:cover;">
          <?php endif; ?>
          <div class="blog-content fs-6 lh-lg"><?= $blog['content'] ?></div>
          <hr class="my-5">
          <a href="<?= $base ?>/blog.php" class="btn btn-outline-forest px-4 py-2"><i class="bi bi-arrow-left me-2"></i> Back to Blog</a>
        </div>
        <div class="col-lg-4">
          <div class="p-4 bg-light rounded-4 border mb-4">
            <h5 class="fw-bold mb-3">About the Author</h5>
            <p class="text-muted small"><?= e($blog['author'] ?? 'Kamadhenu Team') ?> shares insights on cow heritage, organic living, and spiritual traditions.</p>
          </div>
          <?php if (!empty($related)): ?>
          <h5 class="fw-bold mb-3">Related Articles</h5>
          <?php foreach ($related as $r): ?>
          <div class="d-flex gap-3 mb-3">
            <img src="<?= e($r['featured_image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=200&q=80') ?>" class="rounded-3" style="width:80px;height:60px;object-fit:cover;">
            <div>
              <a href="<?= $base ?>/blog-details.php?slug=<?= e($r['slug']) ?>" class="fw-bold text-forest small d-block"><?= e(truncate($r['title'], 50)) ?></a>
              <small class="text-muted"><?= formatDate($r['published_at'] ?? $r['created_at']) ?></small>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
<?php include __DIR__ . '/includes/footer.php'; ?>
