<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'gallery';
$admin_title = 'Gallery';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$photos = $pdo->query("SELECT g.*, gc.name AS category_name FROM gallery g LEFT JOIN gallery_categories gc ON g.category_id = gc.id ORDER BY g.display_order ASC, g.id DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM gallery_categories ORDER BY name ASC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Sanctuary Gallery Photos (<?= count($photos) ?>)</h5>
    <p class="text-muted small mb-0">Manage photo gallery items, categories, and display order.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#galleryModal" onclick="resetGalleryForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Photo
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Photo</th>
        <th>Title</th>
        <th>Category</th>
        <th>Display Order</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($photos as $g): ?>
      <tr>
        <td>
          <img src="<?= e($g['image_url']) ?>" class="table-thumb" alt="<?= e($g['title']) ?>">
        </td>
        <td class="fw-semibold fs-6"><?= e($g['title']) ?></td>
        <td><span class="badge bg-light text-dark border"><?= e($g['category_name'] ?: 'General') ?></span></td>
        <td><span class="badge bg-secondary"><?= $g['display_order'] ?></span></td>
        <td>
          <span class="badge <?= $g['status']==='active'?'badge-active':'badge-inactive' ?>">
            <?= ucfirst(e($g['status'])) ?>
          </span>
        </td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest edit-gallery-btn" data-photo='<?= json_encode($g, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Edit Photo">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-gallery-btn" data-id="<?= $g['id'] ?>" data-title="<?= e($g['title']) ?>" title="Delete Photo">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($photos)): ?>
        <tr><td colspan="6" class="text-center py-4 text-muted">No photos found. Click "Add Photo" to upload one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="galleryModalTitle">Add Gallery Photo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="galleryForm">
          <input type="hidden" name="entity" value="gallery">
          <input type="hidden" name="id" id="galleryId">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Photo Title / Caption *</label>
              <input type="text" name="title" id="galleryTitle" class="form-control" placeholder="e.g. Graceful Gir Mother with Newborn Calf" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Category</label>
              <select name="category_id" id="galleryCategory" class="form-select">
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Image URL *</label>
              <input type="url" name="image_url" id="galleryImageUrl" class="form-control" placeholder="https://images.unsplash.com/..." required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Display Order</label>
              <input type="number" name="display_order" id="galleryOrder" class="form-control" value="0">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="galleryStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveGalleryBtn">Save Photo</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function resetGalleryForm() {
  document.getElementById("galleryForm").reset();
  document.getElementById("galleryId").value = "";
  document.getElementById("galleryModalTitle").textContent = "Add Gallery Photo";
}

document.querySelectorAll(".edit-gallery-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const g = JSON.parse(this.dataset.photo);
    document.getElementById("galleryModalTitle").textContent = "Edit Photo: " + g.title;
    document.getElementById("galleryId").value = g.id;
    document.getElementById("galleryTitle").value = g.title || "";
    document.getElementById("galleryCategory").value = g.category_id || 1;
    document.getElementById("galleryImageUrl").value = g.image_url || "";
    document.getElementById("galleryOrder").value = g.display_order || 0;
    document.getElementById("galleryStatus").value = g.status || "active";
    new bootstrap.Modal(document.getElementById("galleryModal")).show();
  });
});

document.getElementById("saveGalleryBtn").addEventListener("click", async () => {
  const form = document.getElementById("galleryForm");
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
      showAdminToast(result.message || "Failed to save photo.", "error");
    }
  } catch { showAdminToast("Network error occurred.", "error"); }
});

document.querySelectorAll(".delete-gallery-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete photo \'" + this.dataset.title + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=gallery&id=" + this.dataset.id);
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
