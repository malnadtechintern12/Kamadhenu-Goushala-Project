<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'sponsors';
$admin_title = 'Sponsors';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$sponsors = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM sponsorships sp WHERE sp.sponsor_id = s.id) AS total_sponsorships FROM sponsors s ORDER BY s.created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Cow Guardians &amp; Sponsors (<?= count($sponsors) ?>)</h5>
    <p class="text-muted small mb-0">Manage dedicated devotees sponsoring long-term resident cow care.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#sponsorModal" onclick="resetSponsorForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Sponsor
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Guardian Name</th>
        <th>Email Address</th>
        <th>Phone</th>
        <th>PAN Number</th>
        <th>Address</th>
        <th>Active Sponsorships</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($sponsors as $s): ?>
      <tr>
        <td>
          <div class="fw-semibold fs-6"><?= e($s['name']) ?></div>
          <small class="text-muted">Member since <?= formatDate($s['created_at']) ?></small>
        </td>
        <td><a href="mailto:<?= e($s['email']) ?>" class="text-forest"><?= e($s['email']) ?></a></td>
        <td><?= e($s['phone'] ?: '—') ?></td>
        <td><code><?= e($s['pan_number'] ?: '—') ?></code></td>
        <td><small class="text-muted"><?= e(truncate($s['address'] ?? '', 30)) ?></small></td>
        <td><span class="badge bg-forest fs-6"><?= $s['total_sponsorships'] ?> cows</span></td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest edit-sponsor-btn" data-sponsor='<?= json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Edit Sponsor">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-sponsor-btn" data-id="<?= $s['id'] ?>" data-name="<?= e($s['name']) ?>" title="Delete Sponsor">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($sponsors)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">No sponsors found. Click "Add Sponsor" to register one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Sponsor Modal -->
<div class="modal fade" id="sponsorModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sponsorModalTitle">Register Sponsor</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="sponsorForm">
          <input type="hidden" name="entity" value="sponsors">
          <input type="hidden" name="id" id="sponsorId">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Guardian / Sponsor Full Name *</label>
              <input type="text" name="name" id="sponsorName" class="form-control" placeholder="e.g. Arjun Nambiar" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email Address *</label>
              <input type="email" name="email" id="sponsorEmail" class="form-control" placeholder="arjun@example.com" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Phone Number</label>
              <input type="tel" name="phone" id="sponsorPhone" class="form-control" placeholder="+91 98450 12345">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">PAN Number (for 80G tax exemption)</label>
              <input type="text" name="pan_number" id="sponsorPan" class="form-control" placeholder="ABCDE1234F" maxlength="10">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Address / City</label>
              <textarea name="address" id="sponsorAddress" rows="3" class="form-control" placeholder="Residential or company address for 80G receipt delivery..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveSponsorBtn">Save Sponsor</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function resetSponsorForm() {
  document.getElementById("sponsorForm").reset();
  document.getElementById("sponsorId").value = "";
  document.getElementById("sponsorModalTitle").textContent = "Register Sponsor";
}

document.querySelectorAll(".edit-sponsor-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const s = JSON.parse(this.dataset.sponsor);
    document.getElementById("sponsorModalTitle").textContent = "Edit Sponsor: " + s.name;
    document.getElementById("sponsorId").value = s.id;
    document.getElementById("sponsorName").value = s.name || "";
    document.getElementById("sponsorEmail").value = s.email || "";
    document.getElementById("sponsorPhone").value = s.phone || "";
    document.getElementById("sponsorPan").value = s.pan_number || "";
    document.getElementById("sponsorAddress").value = s.address || "";
    new bootstrap.Modal(document.getElementById("sponsorModal")).show();
  });
});

document.getElementById("saveSponsorBtn").addEventListener("click", async () => {
  const form = document.getElementById("sponsorForm");
  if (!form.checkValidity()) { form.reportValidity(); return; }
  const fd = new FormData(form);
  const data = Object.fromEntries(fd.entries());
  try {
    const res = await fetch(BASE_URL + "/admin/api/crud_api.php", {
      method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) {
      showAdminToast(result.message, "success");
      setTimeout(() => location.reload(), 600);
    } else {
      showAdminToast(result.message || "Failed to save sponsor.", "error");
    }
  } catch { showAdminToast("Network error occurred.", "error"); }
});

document.querySelectorAll(".delete-sponsor-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete sponsor \'" + this.dataset.name + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=sponsors&id=" + this.dataset.id);
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
