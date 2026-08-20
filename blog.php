<?php
$page_title = 'Blog — Vedic Knowledge'; $page_desc = 'Articles on indigenous cow breeds, A2 milk, organic farming, and Goushala stories.'; $active_nav = 'blog';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL; $blogs = getPublishedBlogs();
try { $blogCats = $pdo->query("SELECT * FROM blog_categories WHERE status='active' ORDER BY name")->fetchAll(); } catch (Exception $e) { $blogCats = []; }
?>
  <section class="page-hero"><div class="container">
    <div class="hero-badge"><i class="bi bi-newspaper"></i> Vedic Knowledge</div>
    <h1 class="hero-title">Blog &amp; <span>Articles</span></h1>
    <p class="hero-subtitle">Insights on indigenous cow heritage, A2 milk science, organic agriculture, and inspiring Goushala stories.</p>
  </div></section>

  <section class="section-padding">
    <div class="container">
      <div class="row g-4">
        <?php if (empty($blogs)): ?>
          <p class="text-center text-muted fs-5 py-5">No articles published yet.</p>
        <?php else: ?>
          <?php foreach ($blogs as $b): ?>
          <div class="col-md-6 col-lg-4">
            <div class="blog-card h-100">
              <div class="blog-img-box">
                <img src="<?= e($b['featured_image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=600&q=80') ?>" alt="<?= e($b['title']) ?>">
              </div>
              <div class="blog-body d-flex flex-column">
                <div class="d-flex gap-2 mb-2 flex-wrap">
                  <span class="blog-date"><i class="bi bi-calendar3 me-1"></i> <?= formatDate($b['published_at'] ?? $b['created_at']) ?></span>
                  <?php if ($b['category_name']): ?><span class="badge bg-forest"><?= e($b['category_name']) ?></span><?php endif; ?>
                </div>
                <h4 class="blog-title"><?= e($b['title']) ?></h4>
                <p class="text-muted small mb-4" style="display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                  <?= e($b['excerpt'] ?? '') ?>
                </p>
                <div class="mt-auto">
                  <a href="<?= $base ?>/blog-details.php?slug=<?= e($b['slug']) ?>" class="fw-bold text-forest text-decoration-none">
                    Read Full Article <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
<?php include __DIR__ . '/includes/footer.php'; ?>
