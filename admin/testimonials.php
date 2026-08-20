<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'testimonials';
$admin_title = 'Testimonials';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY display_order ASC, id DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Donor &amp; Devotee Testimonials (<?= count($testimonials) ?>)</h5>
    <p class="text-muted small mb-0">Manage devotee reviews, quotes, ratings, and avatars.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#testModal" onclick="resetTestForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Testimonial
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Avatar</th>
        <th>Author Name</th>
        <th>Designation / City</th>
        <th>Rating</th>
        <th>Message Quote</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($testimonials as $t): ?>
      <tr>
        <td>
          <img src="<?= e($t['avatar'] ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80') ?>" class="table-thumb rounded-circle" alt="<?= e($t['author_name']) ?>">
        </td>
        <td class="fw-semibold fs-6"><?= e($t['author_name']) ?></td>
        <td><small class="text-muted"><?= e($t['designation'] ?: 'Devotee / Donor') ?></small></td>
        <td><span class="text-warning fs-6"><?= str_repeat('★', (int)($t['rating'] ?? 5)) ?></span></td>
        <td><small class="text-muted"><?= e(truncate($t['message'], 50)) ?></small></td>
        <td>
          <span class="badge <?= $t['status']==='active'?'badge-active':'badge-inactive' ?>">
            <?= ucfirst(e($t['status'])) ?>
          </span>
        </td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest edit-test-btn" data-test='<?= json_encode($t, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Edit Testimonial">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-test-btn" data-id="<?= $t['id'] ?>" data-name="<?= e($t['author_name']) ?>" title="Delete Testimonial">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($testimonials)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">No testimonials found. Click "Add Testimonial" to add one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Testimonial Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="testModalTitle">Add Devotee Testimonial</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="testForm">
          <input type="hidden" name="entity" value="testimonials">
          <input type="hidden" name="id" id="testId">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Author Name *</label>
              <input type="text" name="author_name" id="testAuthor" class="form-control" placeholder="e.g. Dr. Rameshwar Sharma" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Designation / Location</label>
              <input type="text" name="designation" id="testDesignation" class="form-control" placeholder="e.g. Ayurvedic Physician & Donor, Bengaluru">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Rating (1 to 5 Stars)</label>
              <select name="rating" id="testRating" class="form-select">
                <option value="5">★★★★★ (5 Stars)</option>
                <option value="4">★★★★☆ (4 Stars)</option>
                <option value="3">★★★☆☆ (3 Stars)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Display Order</label>
              <input type="number" name="display_order" id="testOrder" class="form-control" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="testStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Avatar Photo URL</label>
              <input type="url" name="avatar" id="testAvatar" class="form-control" placeholder="https://images.unsplash.com/...">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Testimonial Review Message *</label>
              <textarea name="message" id="testMessage" rows="4" class="form-control" placeholder="Visiting Kamadhenu Goushala was a deeply serene and purifying experience..." required></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveTestBtn">Save Testimonial</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function resetTestForm() {
  document.getElementById("testForm").reset();
  document.getElementById("testId").value = "";
  document.getElementById("testModalTitle").textContent = "Add Devotee Testimonial";
}

document.querySelectorAll(".edit-test-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const t = JSON.parse(this.dataset.test);
    document.getElementById("testModalTitle").textContent = "Edit Testimonial: " + t.author_name;
    document.getElementById("testId").value = t.id;
    document.getElementById("testAuthor").value = t.author_name || "";
    document.getElementById("testDesignation").value = t.designation || "";
    document.getElementById("testRating").value = t.rating || 5;
    document.getElementById("testOrder").value = t.display_order || 0;
    document.getElementById("testStatus").value = t.status || "active";
    document.getElementById("testAvatar").value = t.avatar || "";
    document.getElementById("testMessage").value = t.message || "";
    new bootstrap.Modal(document.getElementById("testModal")).show();
  });
});

document.getElementById("saveTestBtn").addEventListener("click", async () => {
  const form = document.getElementById("testForm");
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
      showAdminToast(result.message || "Failed to save testimonial.", "error");
    }
  } catch { showAdminToast("Network error occurred.", "error"); }
});

document.querySelectorAll(".delete-test-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete testimonial from \'" + this.dataset.name + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=testimonials&id=" + this.dataset.id);
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
