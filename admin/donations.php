<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'donations';
$admin_title = 'Donations';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$donations = $pdo->query("SELECT * FROM donations ORDER BY created_at DESC")->fetchAll();
$totalCompleted = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE payment_status='Completed'")->fetchColumn();
$sevaList = getActiveSeva();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Donations &amp; Seva Contributions (<?= count($donations) ?>)</h5>
    <p class="text-muted small mb-0">Track donor payments, 80G tax receipt PAN numbers, and online seva offerings.</p>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#donationModal" onclick="resetDonationForm()">
      <i class="bi bi-plus-lg me-1"></i> Record Offline Donation
    </button>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-value text-forest">₹<?= number_format((float)$totalCompleted) ?></div>
      <div class="stat-label">Total Verified Donations</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-value"><?= count($donations) ?></div>
      <div class="stat-label">Total Entries Recorded</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card">
      <div class="stat-value text-warning"><?= count(array_filter($donations, fn($d) => $d['payment_status']==='Pending')) ?></div>
      <div class="stat-label">Pending Verification</div>
    </div>
  </div>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Receipt Ref</th>
        <th>Donor Details</th>
        <th>Seva Offering</th>
        <th>Amount</th>
        <th>Method</th>
        <th>Payment Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($donations as $d): ?>
      <tr>
        <td><code class="fw-bold text-forest"><?= e($d['donation_number']) ?></code></td>
        <td>
          <div class="fw-semibold fs-6"><?= e($d['donor_name']) ?></div>
          <small class="text-muted"><?= e($d['donor_email']) ?></small>
          <?= $d['donor_phone'] ? '<br><small class="text-muted">'.e($d['donor_phone']).'</small>' : '' ?>
        </td>
        <td><span class="badge bg-light text-dark border"><?= e($d['seva_name'] ?: 'General') ?></span></td>
        <td><span class="fw-bold text-forest fs-6">₹<?= number_format((float)$d['amount']) ?></span></td>
        <td><small><?= e($d['payment_method'] ?: 'UPI / Direct') ?></small></td>
        <td>
          <?php
          $sc = match($d['payment_status']) {
            'Completed' => 'bg-success',
            'Pending' => 'bg-warning text-dark',
            'Failed' => 'bg-danger',
            default => 'bg-secondary'
          };
          ?>
          <span class="badge <?= $sc ?>"><?= e($d['payment_status']) ?></span>
        </td>
        <td><small class="text-muted"><?= formatDate($d['created_at']) ?></small></td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest view-donation-btn" data-donation='<?= json_encode($d, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="View Receipt Details">
              <i class="bi bi-eye"></i>
            </button>
            <?php if ($d['payment_status'] === 'Pending'): ?>
              <button class="btn btn-sm btn-success mark-donation-btn" data-id="<?= $d['id'] ?>" data-status="Completed" title="Mark as Verified / Completed">
                <i class="bi bi-check-lg"></i>
              </button>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-danger delete-donation-btn" data-id="<?= $d['id'] ?>" data-ref="<?= e($d['donation_number']) ?>" title="Delete Entry">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($donations)): ?>
        <tr><td colspan="8" class="text-center py-4 text-muted">No donations recorded yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Record Donation Modal -->
<div class="modal fade" id="donationModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Record Offline Donation</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="recordDonationForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Donor Name *</label>
              <input type="text" name="donor_name" id="donName" class="form-control" placeholder="e.g. Ramesh Kumar" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Donor Email *</label>
              <input type="email" name="donor_email" id="donEmail" class="form-control" placeholder="ramesh@example.com" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Phone Number</label>
              <input type="tel" name="donor_phone" id="donPhone" class="form-control" placeholder="+91 98765 43210">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">PAN Number (80G Exemption)</label>
              <input type="text" name="pan_number" id="donPan" class="form-control" placeholder="ABCDE1234F" maxlength="10">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Seva Type</label>
              <select name="seva_id" id="donSeva" class="form-select">
                <option value="">General Donation</option>
                <?php foreach ($sevaList as $s): ?>
                  <option value="<?= $s['id'] ?>"><?= e($s['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Donation Amount (₹) *</label>
              <input type="number" name="amount" id="donAmount" class="form-control" placeholder="2501" min="1" required>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Donor Notes / Prayer Message</label>
              <textarea name="message" id="donMessage" rows="2" class="form-control" placeholder="In memory of / on birthday / prayer request..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveDonationBtn">Record Donation</button>
      </div>
    </div>
  </div>
</div>

<!-- View Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="receiptModalTitle">Donation Receipt</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" id="receiptModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function resetDonationForm() {
  document.getElementById("recordDonationForm").reset();
}

document.querySelectorAll(".view-donation-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const d = JSON.parse(this.dataset.donation);
    document.getElementById("receiptModalTitle").textContent = "Receipt: " + d.donation_number;
    document.getElementById("receiptModalBody").innerHTML = `
      <div class="p-3 bg-light rounded-3 mb-3 border text-center">
        <div class="fw-bold fs-4 text-forest">₹${parseFloat(d.amount).toLocaleString("en-IN")}</div>
        <div class="text-muted small">${d.seva_name || "General Donation"}</div>
        <span class="badge ${d.payment_status===\'Completed\'?\'bg-success\':\'bg-warning text-dark\'} mt-2">${d.payment_status}</span>
      </div>
      <table class="table table-sm">
        <tr><td class="text-muted">Receipt Ref:</td><td class="fw-bold">${d.donation_number}</td></tr>
        <tr><td class="text-muted">Donor Name:</td><td class="fw-bold">${d.donor_name}</td></tr>
        <tr><td class="text-muted">Email:</td><td>${d.donor_email}</td></tr>
        <tr><td class="text-muted">Phone:</td><td>${d.donor_phone || "—"}</td></tr>
        <tr><td class="text-muted">PAN:</td><td><code>${d.pan_number || "Not Provided"}</code></td></tr>
        <tr><td class="text-muted">Date:</td><td>${new Date(d.created_at).toLocaleString()}</td></tr>
        <tr><td class="text-muted">Message:</td><td><em>${d.message || "—"}</em></td></tr>
      </table>
    `;
    new bootstrap.Modal(document.getElementById("receiptModal")).show();
  });
});

document.getElementById("saveDonationBtn").addEventListener("click", async () => {
  const form = document.getElementById("recordDonationForm");
  if (!form.checkValidity()) { form.reportValidity(); return; }
  const fd = new FormData(form);
  const data = Object.fromEntries(fd.entries());
  try {
    const res = await fetch(BASE_URL + "/api/submit_donation.php", {
      method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
      showAdminToast(result.message, "success");
      setTimeout(() => location.reload(), 600);
    } else {
      showAdminToast(result.message || "Failed to record donation.", "error");
    }
  } catch { showAdminToast("Network error.", "error"); }
});

document.querySelectorAll(".mark-donation-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    try {
      const res = await fetch(BASE_URL + "/admin/api/donations_api.php", {
        method: "POST", headers: {"Content-Type":"application/json"},
        body: JSON.stringify({ id: this.dataset.id, payment_status: this.dataset.status })
      });
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message, "success");
        setTimeout(() => location.reload(), 600);
      }
    } catch { showAdminToast("Update failed.", "error"); }
  });
});

document.querySelectorAll(".delete-donation-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete donation entry " + this.dataset.ref + "?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=donations&id=" + this.dataset.id);
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message, "success");
        setTimeout(() => location.reload(), 600);
      } else {
        showAdminToast(result.message || "Delete failed.", "error");
      }
    } catch { showAdminToast("Network error.", "error"); }
  });
});
</script>';
include __DIR__ . '/includes/admin_footer.php';
?>
