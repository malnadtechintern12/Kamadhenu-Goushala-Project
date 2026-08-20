<?php
$page_title = 'Contact Us'; $page_desc = 'Contact Kamadhenu Goushala — visit us, call, or send a message.'; $active_nav = 'contact';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL;
$phone = getSetting('phone_primary', SITE_PHONE); $phone2 = getSetting('phone_secondary', '');
$email_addr = getSetting('email_primary', SITE_EMAIL); $addr = getSetting('address', 'Bengaluru, Karnataka');
$mapUrl = getSetting('google_maps_url', '');
?>
  <section class="page-hero"><div class="container">
    <div class="hero-badge"><i class="bi bi-telephone"></i> Reach Us</div>
    <h1 class="hero-title">Contact <span>Kamadhenu Goushala</span></h1>
    <p class="hero-subtitle">We warmly welcome visitors, donors, volunteers, and school groups. Reach out to us anytime.</p>
  </div></section>

  <section class="section-padding">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-5">
          <span class="section-tag">Get in Touch</span>
          <h2 class="section-title">Visit Our Sanctuary</h2>
          <div class="title-ornament justify-content-start"></div>
          <div class="d-flex flex-column gap-4 mb-4 mt-4">
            <div class="d-flex align-items-start gap-3">
              <div class="brand-icon fs-5"><i class="bi bi-geo-alt-fill"></i></div>
              <div><h6 class="fw-bold mb-0">Address</h6><p class="small text-muted mb-0"><?= e($addr) ?></p></div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="brand-icon fs-5"><i class="bi bi-telephone-fill"></i></div>
              <div><h6 class="fw-bold mb-0">Phone</h6><p class="small text-muted mb-0"><?= e($phone) ?><?= $phone2 ? ' / ' . e($phone2) : '' ?></p></div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="brand-icon fs-5"><i class="bi bi-envelope-fill"></i></div>
              <div><h6 class="fw-bold mb-0">Email</h6><p class="small text-muted mb-0"><?= e($email_addr) ?></p></div>
            </div>
            <div class="d-flex align-items-start gap-3">
              <div class="brand-icon fs-5"><i class="bi bi-clock-fill"></i></div>
              <div><h6 class="fw-bold mb-0">Visiting Hours</h6><p class="small text-muted mb-0">Daily: 07:00 AM – 12:30 PM &amp; 03:30 PM – 06:30 PM</p></div>
            </div>
          </div>
          <?php if ($mapUrl): ?>
          <div class="rounded-4 overflow-hidden shadow-sm border mt-3" style="height:250px;">
            <iframe src="<?= e($mapUrl) ?>" width="100%" height="250" style="border:0;" allowfullscreen loading="lazy"></iframe>
          </div>
          <?php endif; ?>
        </div>
        <div class="col-lg-7">
          <div class="p-4 p-md-5 rounded-4 shadow-sm bg-white border">
            <h4 class="fw-bold mb-3 text-forest">Send Us a Message</h4>
            <form id="contactPageForm">
              <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Name *</label><input type="text" name="name" class="form-control py-2" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email *</label><input type="email" name="email" class="form-control py-2" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Phone</label><input type="tel" name="phone" class="form-control py-2"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Subject</label><input type="text" name="subject" class="form-control py-2"></div>
                <div class="col-12"><label class="form-label fw-semibold">Message *</label><textarea name="message" rows="5" class="form-control" required></textarea></div>
                <div class="col-12 mt-3"><button type="submit" class="btn btn-gold py-3 px-5 w-100"><i class="bi bi-send-fill me-2"></i> Send Message</button></div>
              </div>
            </form>
            <div id="contactPageMsg" class="mt-3" style="display:none;"></div>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php
$extra_js = '<script>
document.getElementById("contactPageForm")?.addEventListener("submit", async function(e) {
  e.preventDefault();
  const btn = this.querySelector("button[type=submit]"); btn.disabled = true;
  btn.innerHTML = \'<span class="spinner-border spinner-border-sm me-2"></span>Sending...\';
  try {
    const fd = new FormData(this); const data = Object.fromEntries(fd.entries());
    const res = await fetch(BASE_URL + "/api/submit_contact.php", { method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify(data) });
    const result = await res.json();
    const msg = document.getElementById("contactPageMsg"); msg.style.display = "block";
    msg.className = result.success ? "alert alert-success mt-3" : "alert alert-danger mt-3";
    msg.textContent = result.message;
    if (result.success) { this.reset(); showToast("Message sent! 🙏", "success"); }
  } catch { showToast("Network error.", "error"); }
  btn.disabled = false; btn.innerHTML = \'<i class="bi bi-send-fill me-2"></i> Send Message\';
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
