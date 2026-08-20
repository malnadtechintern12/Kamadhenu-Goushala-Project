<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'seva';
$admin_title = 'Seva Packages';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';
$sevaList = $pdo->query("SELECT * FROM seva ORDER BY display_order ASC, id ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Gau Seva Packages (<?= count($sevaList) ?>)</h5>
    <p class="text-muted small mb-0">Manage donation packages, suggested amounts, icons, and descriptions.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#sevaModal" onclick="resetSevaForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Seva Package
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Icon</th>
        <th>Seva Title</th>
        <th>Suggested Amount</th>
        <th>Display Order</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($sevaList as $s): ?>
      <tr>
        <td>
          <div class="stat-icon p-2 rounded-3 text-center" style="background:#FFF9E6; color:#B3861B; width:42px; height:42px; display:flex; align-items:center; justify-content:center;">
            <i class="bi <?= e($s['icon'] ?: 'bi-heart-fill') ?> fs-5"></i>
          </div>
        </td>
        <td>
          <div class="fw-semibold fs-6"><?= e($s['title']) ?></div>
          <small class="text-muted"><?= e(truncate($s['short_desc'] ?: $s['full_desc'], 50)) ?></small>
        </td>
        <td>
          <span class="fw-bold text-forest fs-6">₹<?= number_format((float)$s['suggested_amount']) ?></span>
        </td>
        <td><span class="badge bg-light text-dark border"><?= $s['display_order'] ?></span></td>
        <td>
          <span class="badge <?= $s['status']==='active'?'badge-active':'badge-inactive' ?>">
            <?= ucfirst(e($s['status'])) ?>
          </span>
        </td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest edit-seva-btn" data-seva='<?= json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Edit Package">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-seva-btn" data-id="<?= $s['id'] ?>" data-title="<?= e($s['title']) ?>" title="Delete Package">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($sevaList)): ?>
        <tr><td colspan="6" class="text-center py-4 text-muted">No seva packages configured. Click "Add Seva Package" to create one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Seva Modal -->
<div class="modal fade" id="sevaModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sevaModalTitle">Add Gau Seva Package</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="sevaForm">
          <input type="hidden" name="entity" value="seva">
          <input type="hidden" name="id" id="sevaId">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Seva Title *</label>
              <input type="text" name="title" id="sevaTitle" class="form-control" placeholder="e.g. Feed a Cow for a Day, Medical Seva" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Suggested Amount (₹) *</label>
              <input type="number" name="suggested_amount" id="sevaAmount" class="form-control" placeholder="501" min="1" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Bootstrap Icon Class</label>
              <input type="text" name="icon" id="sevaIcon" class="form-control" placeholder="bi-heart-fill">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Display Order</label>
              <input type="number" name="display_order" id="sevaOrder" class="form-control" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="sevaStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Banner Image URL</label>
              <input type="url" name="image" id="sevaImage" class="form-control" placeholder="https://images.unsplash.com/...">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Short Summary Description</label>
              <input type="text" name="short_desc" id="sevaShortDesc" class="form-control" placeholder="Brief 1-sentence explanation of this seva">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Full Spiritual &amp; Practical Description</label>
              <textarea name="full_desc" id="sevaFullDesc" rows="3" class="form-control" placeholder="Detailed benefits and materials provided with this contribution..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveSevaBtn">Save Seva Package</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function resetSevaForm() {
  document.getElementById("sevaForm").reset();
  document.getElementById("sevaId").value = "";
  document.getElementById("sevaIcon").value = "bi-heart-fill";
  document.getElementById("sevaModalTitle").textContent = "Add Gau Seva Package";
}

document.querySelectorAll(".edit-seva-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const s = JSON.parse(this.dataset.seva);
    document.getElementById("sevaModalTitle").textContent = "Edit Seva: " + s.title;
    document.getElementById("sevaId").value = s.id;
    document.getElementById("sevaTitle").value = s.title || "";
    document.getElementById("sevaAmount").value = s.suggested_amount || "";
    document.getElementById("sevaIcon").value = s.icon || "bi-heart-fill";
    document.getElementById("sevaOrder").value = s.display_order || 0;
    document.getElementById("sevaStatus").value = s.status || "active";
    document.getElementById("sevaImage").value = s.image || "";
    document.getElementById("sevaShortDesc").value = s.short_desc || "";
    document.getElementById("sevaFullDesc").value = s.full_desc || "";
    new bootstrap.Modal(document.getElementById("sevaModal")).show();
  });
});

document.getElementById("saveSevaBtn").addEventListener("click", async () => {
  const form = document.getElementById("sevaForm");
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
      showAdminToast(result.message || "Failed to save seva package.", "error");
    }
  } catch { showAdminToast("Network error occurred.", "error"); }
});

document.querySelectorAll(".delete-seva-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete seva package \'" + this.dataset.title + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=seva&id=" + this.dataset.id);
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
