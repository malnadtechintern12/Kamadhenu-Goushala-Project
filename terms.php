<?php
$page_title = 'Terms & Conditions'; $page_desc = 'Terms and conditions for Kamadhenu Goushala website.'; $active_nav = '';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php'; $base = BASE_URL;
$banner = getPageBanner('terms');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>
  <section class="page-hero">
    <?php if (!empty($banner['banner_image'])): ?>
      <div class="page-hero-bg">
        <img src="<?= e(getImageUrl($banner['banner_image'])) ?>" 
             alt="<?= e($banner['page_name'] ?? 'Terms & Conditions') ?>" 
             class="page-hero-img" 
             fetchpriority="high" 
             loading="eager" 
             decoding="sync">
        <div class="page-hero-overlay"></div>
      </div>
    <?php endif; ?>
    <div class="container position-relative" style="z-index: 2;">
      <div class="hero-badge"><i class="bi bi-file-earmark-text"></i> <?= e($banner['badge_text'] ?? 'Legal & Policies') ?></div>
      <h1 class="hero-title"><?= $banner['title'] ?? 'Terms &amp; <span>Conditions</span>' ?></h1>
      <p class="hero-subtitle"><?= e($banner['subtitle'] ?? 'Rules and guidelines for website use, donations, and organic store purchases.') ?></p>
    </div>
  </section>
  <section class="section-padding"><div class="container"><div class="row justify-content-center"><div class="col-lg-8">
    <div class="bg-white p-5 rounded-4 shadow-sm border">
      <p class="text-muted">Last updated: August 2026</p>
      <h5 class="fw-bold mt-4">1. Donations</h5>
      <p class="text-muted">All donations are voluntary and non-refundable. Donation receipts with 80G exemption details are issued within 7 working days.</p>
      <h5 class="fw-bold mt-4">2. Cow Sponsorship</h5>
      <p class="text-muted">Sponsoring or adopting a cow provides financial support for that cow's care. It does not confer physical ownership or custody rights.</p>
      <h5 class="fw-bold mt-4">3. Products</h5>
      <p class="text-muted">Organic products are sold as-is. Returns are accepted within 7 days for unopened items. Shipping charges are non-refundable.</p>
      <h5 class="fw-bold mt-4">4. Content</h5>
      <p class="text-muted">All website content including text, images, and design is the intellectual property of Kamadhenu Goushala Trust.</p>
      <h5 class="fw-bold mt-4">5. Liability</h5>
      <p class="text-muted">Kamadhenu Goushala Trust is not liable for any damages arising from the use of this website or its services.</p>
      <h5 class="fw-bold mt-4">6. Governing Law</h5>
      <p class="text-muted">These terms are governed by the laws of India. Disputes shall be subject to the jurisdiction of Bengaluru courts.</p>
    </div>
  </div></div></div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
