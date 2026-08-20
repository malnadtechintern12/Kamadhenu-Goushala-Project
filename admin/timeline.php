<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'timeline';
$admin_title = 'Timeline Milestones';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$timeline = $pdo->query("SELECT * FROM timeline ORDER BY display_order ASC, year ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Sanctuary Journey Milestones (<?= count($timeline) ?>)</h5>
    <p class="text-muted small mb-0">Manage history timeline points displayed on the homepage and about page.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#timelineModal" onclick="resetTimelineForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Milestone
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Year</th>
        <th>Milestone Title</th>
        <th>Description</th>
        <th>Order</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($timeline as $t): ?>
      <tr>
        <td class="fw-bold fs-5 text-warning"><?= e($t['year']) ?></td>
        <td class="fw-semibold fs-6"><?= e($t['title']) ?></td>
        <td><small class="text-muted"><?= e(truncate($t['description'], 60)) ?></small></td>
        <td><span class="badge bg-light text-dark border"><?= $t['display_order'] ?></span></td>
        <td>
          <span class="badge <?= $t['status']==='active'?'badge-active':'badge-inactive' ?>">
            <?= ucfirst(e($t['status'])) ?>
          </span>
        </td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest edit-timeline-btn" data-timeline='<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Edit Milestone">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-timeline-btn" data-id="<?= $t['id'] ?>" data-year="<?= e($t['year']) ?>" title="Delete Milestone">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($timeline)): ?>
        <tr><td colspan="6" class="text-center py-4 text-muted">No timeline milestones found. Click "Add Milestone" to create one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Timeline Modal -->
<div class="modal fade" id="timelineModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="timelineModalTitle">Add Timeline Milestone</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="timelineForm">
          <input type="hidden" name="entity" value="timeline">
          <input type="hidden" name="id" id="timelineId">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-semibold">Year *</label>
              <input type="text" name="year" id="timelineYear" class="form-control" placeholder="e.g. 2015, 2024" required>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Milestone Title *</label>
              <input type="text" name="title" id="timelineTitle" class="form-control" placeholder="e.g. Indigenous Breed Conservation Program" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Display Order</label>
              <input type="number" name="display_order" id="timelineOrder" class="form-control" value="0">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="timelineStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Milestone Description *</label>
              <textarea name="description" id="timelineDescription" rows="4" class="form-control" placeholder="Details about this milestone in the journey of the Goushala..." required></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveTimelineBtn">Save Milestone</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function resetTimelineForm() {
  document.getElementById("timelineForm").reset();
  document.getElementById("timelineId").value = "";
  document.getElementById("timelineModalTitle").textContent = "Add Timeline Milestone";
}

document.querySelectorAll(".edit-timeline-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const t = JSON.parse(this.dataset.timeline);
    document.getElementById("timelineModalTitle").textContent = "Edit Milestone: " + t.year;
    document.getElementById("timelineId").value = t.id;
    document.getElementById("timelineYear").value = t.year || "";
    document.getElementById("timelineTitle").value = t.title || "";
    document.getElementById("timelineOrder").value = t.display_order || 0;
    document.getElementById("timelineStatus").value = t.status || "active";
    document.getElementById("timelineDescription").value = t.description || "";
    new bootstrap.Modal(document.getElementById("timelineModal")).show();
  });
});

document.getElementById("saveTimelineBtn").addEventListener("click", async () => {
  const form = document.getElementById("timelineForm");
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
      showAdminToast(result.message || "Failed to save milestone.", "error");
    }
  } catch { showAdminToast("Network error occurred.", "error"); }
});

document.querySelectorAll(".delete-timeline-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete milestone \'" + this.dataset.year + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=timeline&id=" + this.dataset.id);
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
