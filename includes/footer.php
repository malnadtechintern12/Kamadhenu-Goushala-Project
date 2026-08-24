<?php
// ============================================================
// Kamadhenu Goushala — Shared Footer Include
// ============================================================
$base   = BASE_URL;
$phone  = getSetting('phone_primary', SITE_PHONE);
$email  = getSetting('email_primary', SITE_EMAIL);
$addr   = getSetting('address', 'Bengaluru, Karnataka, India');
$wp_num = getSetting('whatsapp_number', '919845088990');
$fb_url = getSetting('facebook_url', '#');
$ig_url = getSetting('instagram_url', '#');
$yt_url = getSetting('youtube_url', '#');
$tw_url = getSetting('twitter_url', '#');
$upi_id = getSetting('donation_upi_id', 'kamadhenu@sbi');
$footer_about = getSetting('footer_about', 'Kamadhenu Goushala is dedicated to the ethical protection, preservation, and natural healthcare of indigenous Indian cow breeds.');
$copyright   = getSetting('footer_copyright', '© 2026 Kamadhenu Goushala Trust. All rights reserved.');
$info_80g    = getSetting('donation_80g_info', 'Donations are eligible for 50% tax exemption under Section 80G.');
?>

  <!-- Footer -->
  <footer class="site-footer main-footer">
    <div class="container">
      <div class="row g-4 mb-5">
        <!-- Brand Column -->
        <div class="col-lg-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <?php $footerLogo = getSetting('site_logo', ''); ?>
            <?php if (!empty($footerLogo)): ?>
              <img src="<?= e(getImageUrl($footerLogo)) ?>" alt="<?= e(getSetting('site_name', 'Kamadhenu Goushala')) ?>" title="<?= e(getSetting('site_name', 'Kamadhenu Goushala')) ?>" class="footer-logo-img" style="max-height: 48px; width: auto; object-fit: contain;">
            <?php else: ?>
              <div class="brand-icon"><i class="bi bi-heart-fill"></i></div>
            <?php endif; ?>
            <div>
              <div class="fw-bold fs-5 footer-brand-text"><?= e(getSetting('site_name', 'KAMADHENU')) ?></div>
              <div class="footer-brand-sub"><?= e(getSetting('site_tagline', 'GOUSHALA')) ?></div>
            </div>
          </div>
          <p class="footer-text small mb-3"><?= e($footer_about) ?></p>
          <div class="d-flex gap-2 mb-3 flex-wrap">
            <span class="badge rounded-pill footer-pill-badge"><?= __t('badge_breeds', '🐄 Indigenous Breeds') ?></span>
            <span class="badge rounded-pill footer-pill-badge"><?= __t('badge_farm', '🌿 Organic Farm') ?></span>
            <span class="badge rounded-pill footer-pill-badge"><?= __t('badge_hospital', '🏥 Vet Hospital') ?></span>
          </div>
          <div class="d-flex gap-2 mb-4">
            <a href="<?= e($fb_url) ?>" target="_blank" rel="noopener" class="social-btn"><i class="bi bi-facebook"></i></a>
            <a href="<?= e($ig_url) ?>" target="_blank" rel="noopener" class="social-btn social-insta"><i class="bi bi-instagram"></i></a>
            <a href="<?= e($yt_url) ?>" target="_blank" rel="noopener" class="social-btn social-yt"><i class="bi bi-youtube"></i></a>
            <a href="https://wa.me/<?= e($wp_num) ?>" target="_blank" rel="noopener" class="social-btn"><i class="bi bi-whatsapp"></i></a>
          </div>
          <div class="p-3 rounded-3 footer-info-box">
            <div class="fw-bold footer-box-title small"><i class="bi bi-patch-check-fill me-1"></i> <?= __t('badge_80g', '80G Tax Exemption') ?></div>
            <div class="small footer-text mt-1"><?= ($current_lang === 'kn') ? __t('footer_80g_text', $info_80g) : e($info_80g) ?></div>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="col-6 col-lg-2">
          <h5 class="footer-title"><?= __t('footer_quick_links', 'Quick Links') ?></h5>
          <ul class="footer-links">
            <li><a href="<?= $base ?>/index.php"><?= __t('page_home', 'Home') ?></a></li>
            <li><a href="<?= $base ?>/about.php"><?= __t('page_about', 'About Us') ?></a></li>
            <li><a href="<?= $base ?>/cows.php"><?= __t('page_cows', 'Our Cows') ?></a></li>
            <li><a href="<?= $base ?>/breeds.php"><?= __t('page_breeds', 'Cow Breeds') ?></a></li>
            <li><a href="<?= $base ?>/seva.php"><?= __t('page_seva', 'Gau Seva') ?></a></li>
            <li><a href="<?= $base ?>/donation.php"><?= __t('page_donation', 'Donate') ?></a></li>
          </ul>
        </div>

        <!-- Resources -->
        <div class="col-6 col-lg-2">
          <h5 class="footer-title"><?= __t('footer_resources', 'Resources') ?></h5>
          <ul class="footer-links">
            <li><a href="<?= $base ?>/products.php"><?= __t('page_products', 'Organic Store') ?></a></li>
            <li><a href="<?= $base ?>/gallery.php"><?= __t('page_gallery', 'Photo Gallery') ?></a></li>
            <li><a href="<?= $base ?>/blog.php"><?= __t('page_blog', 'Vedic Articles') ?></a></li>
            <li><a href="<?= $base ?>/events.php"><?= __t('page_events', 'Upcoming Events') ?></a></li>
            <li><a href="<?= $base ?>/privacy.php"><?= __t('page_privacy', 'Privacy Policy') ?></a></li>
            <li><a href="<?= $base ?>/terms.php"><?= __t('page_terms', 'Terms & Conditions') ?></a></li>
          </ul>
        </div>

        <!-- Newsletter & UPI -->
        <div class="col-lg-4">
          <h5 class="footer-title"><?= __t('footer_newsletter', 'Newsletter & Direct UPI') ?></h5>
          <p class="small footer-text"><?= __t('footer_subscribe_desc', 'Subscribe to receive monthly sanctuary updates and Gopashtami festival announcements.') ?></p>
          <form class="newsletter-form d-flex gap-2 mb-3" id="footerNewsletterForm">
            <input type="email" class="form-control form-control-sm" id="footerEmail" placeholder="<?= __t('Enter your email', 'Enter your email') ?>" required>
            <button type="submit" class="btn btn-gold btn-sm px-3"><?= __t('Join', 'Join') ?></button>
          </form>
          <div id="newsletterMsg" class="small mb-3" style="display:none;"></div>
          <div class="d-flex flex-column gap-2 small footer-contact-info mt-3">
            <div><i class="bi bi-telephone-fill me-2"></i> <?= e($phone) ?></div>
            <div><i class="bi bi-envelope-fill me-2"></i> <?= e($email) ?></div>
          </div>
          <div class="p-3 rounded-3 mt-3 footer-upi-box">
            <div class="fw-bold footer-box-title small"><i class="bi bi-qr-code me-1"></i> <?= __t('footer_upi_title', 'Quick UPI ID') ?></div>
            <div class="fs-6 fw-bold site-upi footer-upi-text"><?= e($upi_id) ?></div>
          </div>
        </div>
      </div>

      <div class="footer-bottom text-center">
        <p class="mb-0 footer-copyright-text"><?= ($current_lang === 'kn') ? __t('footer_copyright') : e($copyright) ?></p>
      </div>
    </div>
  </footer>

  <!-- Floating Cart Button -->
  <button class="floating-cart-btn" data-bs-toggle="modal" data-bs-target="#cartModal" title="View Cart">
    <i class="bi bi-bag-heart-fill"></i>
    <span class="cart-badge-count cart-count-badge" style="display:none;">0</span>
  </button>

  <!-- Cart Modal -->
  <div class="modal fade" id="cartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-forest text-white">
          <h5 class="modal-title fw-bold"><i class="bi bi-bag-check-fill text-warning me-2"></i> <?= __t('cart_title', 'Your Organic Cart') ?></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4" id="cartItemsContainer"></div>
        <div class="modal-footer border-top bg-light d-flex justify-content-between">
          <div>
            <span class="text-muted small"><?= __t('cart_subtotal', 'Subtotal:') ?></span>
            <div class="fw-bold fs-5 text-forest" id="cartSubtotalAmount">₹0.00</div>
          </div>
          <button type="button" class="btn btn-gold px-4 py-2" id="cartCheckoutBtn"
                  onclick="window.location.href='<?= $base ?>/products.php#checkoutSection'">
            <?= __t('btn_checkout', 'Checkout') ?> <i class="bi bi-arrow-right ms-1"></i>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Pass BASE_URL and Order Routing config to JavaScript
    const BASE_URL = '<?= $base ?>';
    const ORDER_ROUTING_MODE = '<?= e(getSetting('order_routing_mode', 'admin_panel')) ?>';
    const ORDER_WHATSAPP_NUMBER = '<?= e(getSetting('whatsapp_number', '')) ?>';
  </script>
  <script src="<?= $base ?>/assets/js/api.js?v=<?= time() ?>"></script>
  <script src="<?= $base ?>/assets/js/cart.js?v=<?= time() ?>"></script>
  <script src="<?= $base ?>/assets/js/theme.js?v=<?= time() ?>"></script>
  <script src="<?= $base ?>/assets/js/language.js?v=<?= time() ?>"></script>
  <script src="<?= $base ?>/assets/js/main.js?v=<?= time() ?>"></script>

  <!-- Newsletter form handler -->
  <script>
  document.getElementById('footerNewsletterForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const email = document.getElementById('footerEmail').value;
    const msg   = document.getElementById('newsletterMsg');
    try {
      const res = await fetch(BASE_URL + '/api/submit_newsletter.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ email })
      });
      const data = await res.json();
      msg.textContent = data.message || 'Thank you for subscribing!';
      msg.style.color = data.success ? 'var(--sacred-gold)' : '#f66';
      msg.style.display = 'block';
      if (data.success) this.reset();
    } catch {
      msg.textContent = 'Subscription failed. Please try again.';
      msg.style.color = '#f66';
      msg.style.display = 'block';
    }
  });
  </script>

  <?= $extra_js ?? '' ?>
</body>
</html>
