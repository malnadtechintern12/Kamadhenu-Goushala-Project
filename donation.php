<?php
$page_title = 'Donate — Support Gau Seva'; $page_desc = 'Make a charitable donation to Kamadhenu Goushala. Support cow feeding, medical care, and shelter. 80G tax exempt.'; $active_nav = 'home';
include __DIR__ . '/includes/header.php'; include __DIR__ . '/includes/navbar.php';
$base = BASE_URL;
$sevaList = getActiveSeva();
$presetSeva = intInput($_GET['seva_id'] ?? 0);
$presetAmount = floatval($_GET['amount'] ?? 0);
$cowName = htmlspecialchars($_GET['cow_name'] ?? '', ENT_QUOTES);
$bankName = getSetting('donation_bank_name', 'State Bank of India');
$accName  = getSetting('donation_account_name', 'Kamadhenu Goushala Seva Trust');
$accNo    = getSetting('donation_account_no', '3899010045678');
$ifsc     = getSetting('donation_ifsc_code', 'SBIN0040123');
$upiId    = getSetting('donation_upi_id', 'kamadhenu@sbi');
$info80g  = getSetting('donation_80g_info', 'Donations eligible for 50% tax exemption under Section 80G.');
?>
  <section class="page-hero"><div class="container">
    <div class="hero-badge"><i class="bi bi-heart-fill"></i> Support Gau Seva</div>
    <h1 class="hero-title">Make a <span>Donation</span></h1>
    <p class="hero-subtitle">Your generous contribution provides food, shelter, and medical care for rescued indigenous cows. Every rupee counts.</p>
  </div></section>

  <section class="section-padding">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-7">
          <div class="p-4 p-md-5 rounded-4 shadow-sm bg-white border">
            <h4 class="fw-bold mb-4 text-forest"><i class="bi bi-heart-fill text-warning me-2"></i> Donation Form</h4>
            <?php if ($cowName): ?>
              <div class="alert alert-success"><i class="bi bi-award me-2"></i> Sponsoring: <strong><?= $cowName ?></strong></div>
            <?php endif; ?>
            <form id="donationForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Full Name *</label>
                  <input type="text" name="donor_name" class="form-control py-2" placeholder="e.g. Ramesh Kumar" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email *</label>
                  <input type="email" name="donor_email" class="form-control py-2" placeholder="name@example.com" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Phone</label>
                  <input type="tel" name="donor_phone" class="form-control py-2" placeholder="+91 98765 43210">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">PAN (for 80G)</label>
                  <input type="text" name="pan_number" class="form-control py-2" placeholder="ABCDE1234F" maxlength="10">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Seva Type</label>
                  <select name="seva_id" class="form-select py-2" id="sevaSelect">
                    <option value="">General Donation</option>
                    <?php foreach ($sevaList as $s): ?>
                      <option value="<?= $s['id'] ?>" data-amount="<?= $s['suggested_amount'] ?>" <?= ($presetSeva == $s['id']) ? 'selected' : '' ?>><?= e($s['title']) ?> (₹<?= number_format((float)$s['suggested_amount']) ?>)</option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Amount (₹) *</label>
                  <input type="number" name="amount" class="form-control py-2" min="1" placeholder="501" value="<?= $presetAmount ?: '' ?>" required id="amountInput">
                </div>
                <div class="col-12">
                  <div class="d-flex gap-2 flex-wrap">
                    <?php foreach ([501, 1001, 2501, 5001, 10001] as $amt): ?>
                      <button type="button" class="btn btn-sm btn-outline-gold quick-amt" data-amount="<?= $amt ?>">₹<?= number_format($amt) ?></button>
                    <?php endforeach; ?>
                  </div>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Message (optional)</label>
                  <textarea name="message" rows="3" class="form-control" placeholder="Any special prayer request or message..."></textarea>
                </div>
                <div class="col-12 mt-3">
                  <button type="submit" class="btn btn-gold py-3 px-5 w-100 fs-5">
                    <i class="bi bi-heart-fill me-2"></i> SUBMIT DONATION
                  </button>
                </div>
              </div>
            </form>
            <div id="donationMsg" class="mt-3" style="display:none;"></div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="p-4 rounded-4 shadow-sm bg-light border mb-4">
            <h5 class="fw-bold text-forest mb-3"><i class="bi bi-bank me-2"></i> Bank Transfer Details</h5>
            <table class="table table-sm mb-0">
              <tr><td class="text-muted">Bank</td><td class="fw-bold"><?= e($bankName) ?></td></tr>
              <tr><td class="text-muted">Account Name</td><td class="fw-bold"><?= e($accName) ?></td></tr>
              <tr><td class="text-muted">Account No</td><td class="fw-bold"><?= e($accNo) ?></td></tr>
              <tr><td class="text-muted">IFSC Code</td><td class="fw-bold"><?= e($ifsc) ?></td></tr>
              <tr><td class="text-muted">UPI ID</td><td class="fw-bold text-forest"><?= e($upiId) ?></td></tr>
            </table>
          </div>
          <div class="p-4 rounded-4 shadow-sm border" style="background:rgba(212,167,44,.08);">
            <h5 class="fw-bold text-forest mb-2"><i class="bi bi-patch-check-fill text-warning me-2"></i> Tax Benefit</h5>
            <p class="text-muted small mb-0"><?= e($info80g) ?></p>
          </div>
        </div>
      </div>
    </div>
  </section>
<?php
$extra_js = '<script>
document.querySelectorAll(".quick-amt").forEach(btn => {
  btn.addEventListener("click", () => document.getElementById("amountInput").value = btn.dataset.amount);
});
document.getElementById("sevaSelect")?.addEventListener("change", function() {
  const opt = this.options[this.selectedIndex];
  const amt = opt.dataset.amount;
  if (amt) document.getElementById("amountInput").value = amt;
});
document.getElementById("donationForm")?.addEventListener("submit", async function(e) {
  e.preventDefault();
  const btn = this.querySelector("button[type=submit]");
  btn.disabled = true;
  btn.innerHTML = \'<span class="spinner-border spinner-border-sm me-2"></span>Processing...\';
  try {
    const fd = new FormData(this);
    const data = Object.fromEntries(fd.entries());
    const res = await fetch(BASE_URL + "/api/submit_donation.php", {
      method: "POST", headers: {"Content-Type": "application/json"}, body: JSON.stringify(data)
    });
    const result = await res.json();
    const msg = document.getElementById("donationMsg");
    msg.style.display = "block";
    if (result.success) {
      msg.className = "alert alert-success mt-3";
      msg.innerHTML = "<i class=\'bi bi-check-circle-fill me-2\'></i>" + result.message;
      this.reset();
      showToast("Donation recorded successfully! 🙏", "success");
    } else {
      msg.className = "alert alert-danger mt-3";
      msg.textContent = result.message;
    }
  } catch { showToast("Network error. Please try again.", "error"); }
  btn.disabled = false;
  btn.innerHTML = \'<i class="bi bi-heart-fill me-2"></i> SUBMIT DONATION\';
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
