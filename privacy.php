<?php
$page_title = 'Privacy Policy'; $page_desc = 'Privacy Policy for Kamadhenu Goushala website.'; $active_nav = '';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php'; $base = BASE_URL;
$banner = getPageBanner('privacy');
$bannerBg = !empty($banner['banner_image']) ? "background: var(--hero-overlay), url('" . e(getImageUrl($banner['banner_image'])) . "') center/cover no-repeat;" : "";
?>
  <section class="page-hero">
    <?php if (!empty($banner['banner_image'])): ?>
      <div class="page-hero-bg">
        <img src="<?= e(getImageUrl($banner['banner_image'])) ?>" 
             alt="<?= e($banner['page_name'] ?? 'Privacy Policy') ?>" 
             class="page-hero-img" 
             fetchpriority="high" 
             loading="eager" 
             decoding="sync">
        <div class="page-hero-overlay"></div>
      </div>
    <?php endif; ?>
    <div class="container position-relative" style="z-index: 2;">
      <div class="hero-badge"><i class="bi bi-shield-check"></i> <?= e($banner['badge_text'] ?? 'Data Privacy') ?></div>
      <h1 class="hero-title"><?= $banner['title'] ?? 'Privacy <span>Policy</span>' ?></h1>
      <p class="hero-subtitle"><?= e($banner['subtitle'] ?? 'How we handle, protect, and respect your personal information.') ?></p>
    </div>
  </section>
  <section class="section-padding"><div class="container"><div class="row justify-content-center"><div class="col-lg-8">
    <div class="bg-white p-5 rounded-4 shadow-sm border">
      <h4 class="fw-bold mb-3">Your Privacy Matters</h4>
      <p class="text-muted">Last updated: August 2026</p>
      <h5 class="fw-bold mt-4">1. Information We Collect</h5>
      <p class="text-muted">We collect personal information (name, email, phone, PAN) only when you voluntarily submit donation forms, contact forms, or newsletter subscriptions.</p>
      <h5 class="fw-bold mt-4">2. How We Use Your Information</h5>
      <p class="text-muted">Your data is used solely for: processing donations, issuing 80G receipts, sending seva updates, and responding to inquiries. We never sell or share your data with third parties.</p>
      <h5 class="fw-bold mt-4">3. Data Security</h5>
      <p class="text-muted">We implement industry-standard security measures to protect your personal data, including encrypted connections and secure database storage.</p>
      <h5 class="fw-bold mt-4">4. Cookies</h5>
      <p class="text-muted">We use essential cookies for session management. No tracking or advertising cookies are used.</p>
      <h5 class="fw-bold mt-4">5. Your Rights</h5>
      <p class="text-muted">You may request access to, correction of, or deletion of your personal data by emailing <?= e(getSetting('email_primary', SITE_EMAIL)) ?>.</p>
      <h5 class="fw-bold mt-4">6. Contact</h5>
      <p class="text-muted">For privacy-related queries: <a href="<?= $base ?>/contact.php">Contact Us</a></p>
    </div>
  </div></div></div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
