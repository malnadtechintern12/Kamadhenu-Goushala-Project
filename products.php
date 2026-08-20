<?php
$page_title = 'Organic Products'; $page_desc = 'Shop organic cow products — vermicompost, Panchagavya, diyas, and more from Kamadhenu Goushala.'; $active_nav = 'products';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL;
$products = getActiveProducts();
try { $prodCats = $pdo->query("SELECT * FROM product_categories WHERE status='active' ORDER BY name")->fetchAll(); } catch (Exception $e) { $prodCats = []; }
?>
  <section class="page-hero"><div class="container">
    <div class="hero-badge"><i class="bi bi-bag-check"></i> Natural &amp; Organic</div>
    <h1 class="hero-title">Organic <span>Products</span></h1>
    <p class="hero-subtitle">100% natural products crafted from sacred cow resources — vermicompost, Panchagavya, diyas, and herbal formulations.</p>
  </div></section>

  <section class="section-padding">
    <div class="container">
      <div class="d-flex flex-wrap gap-2 mb-4 justify-content-center">
        <button class="btn btn-sm btn-forest active" data-prod-filter="all">All Products</button>
        <?php foreach ($prodCats as $c): ?>
          <button class="btn btn-sm btn-outline-forest" data-prod-filter="<?= $c['id'] ?>"><?= e($c['name']) ?></button>
        <?php endforeach; ?>
      </div>
      <div class="row g-4" id="productsGrid">
        <?php if (empty($products)): ?>
          <p class="text-center text-muted fs-5 py-5">No products available right now.</p>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
          <div class="col-md-6 col-lg-4 prod-item" data-cat="<?= $p['category_id'] ?>">
            <div class="product-card h-100">
              <div class="product-img-box">
                <img src="<?= e($p['image'] ?: 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=600&q=80') ?>" alt="<?= e($p['name']) ?>">
                <?php if ($p['stock'] > 0): ?><span class="badge bg-success position-absolute top-0 end-0 m-2">In Stock</span>
                <?php else: ?><span class="badge bg-danger position-absolute top-0 end-0 m-2">Sold Out</span><?php endif; ?>
              </div>
              <div class="product-body d-flex flex-column">
                <span class="small text-muted"><?= e($p['category_name'] ?? 'Organic') ?></span>
                <h5 class="fw-bold mb-1"><?= e($p['name']) ?></h5>
                <p class="small text-muted mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= e($p['description'] ?? '') ?></p>
                <div class="d-flex align-items-center justify-content-between mt-auto">
                  <div class="fs-5 fw-bold text-forest">₹<?= number_format((float)$p['price']) ?></div>
                  <?php if ($p['stock'] > 0): ?>
                  <button class="btn btn-sm btn-gold add-to-cart-btn"
                          data-id="<?= $p['id'] ?>" data-name="<?= e($p['name']) ?>"
                          data-price="<?= $p['price'] ?>" data-image="<?= e($p['image'] ?? '') ?>">
                    <i class="bi bi-cart-plus me-1"></i> Add
                  </button>
                  <?php endif; ?>
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
document.querySelectorAll("[data-prod-filter]").forEach(btn => {
  btn.addEventListener("click", function() {
    document.querySelectorAll("[data-prod-filter]").forEach(b => { b.classList.remove("active","btn-forest"); b.classList.add("btn-outline-forest"); });
    this.classList.add("active","btn-forest"); this.classList.remove("btn-outline-forest");
    const f = this.dataset.prodFilter;
    document.querySelectorAll(".prod-item").forEach(i => { i.style.display = (f === "all" || i.dataset.cat === f) ? "" : "none"; });
  });
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
