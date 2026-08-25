<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'breeds';
$admin_title = 'Manage Breeds';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';
$breeds = $pdo->query("SELECT * FROM breeds ORDER BY name ASC")->fetchAll();

$wpNumbersRaw = getSetting('whatsapp_numbers', '[]');
$configuredWpNumbers = json_decode($wpNumbersRaw, true) ?: [];
$primaryWpNumber = getSetting('whatsapp_number', '919845088990');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Indigenous Cow Breeds (<?= count($breeds) ?>)</h5>
    <p class="text-muted small mb-0">Manage heritage breeds catalog, descriptions, characteristics, and WhatsApp routing.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#breedModal" onclick="resetBreedForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Breed
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Image</th>
        <th>Breed Name</th>
        <th>Origin / Region</th>
        <th>Milk Yield</th>
        <th>WhatsApp</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($breeds as $b): ?>
      <tr>
        <td>
          <img src="<?= e($b['image'] ?: 'https://images.unsplash.com/photo-1546445317-29f4545e9d53?auto=format&fit=crop&w=150&q=80') ?>" class="table-thumb" alt="<?= e($b['name']) ?>">
        </td>
        <td>
          <div class="fw-semibold fs-6"><?= e($b['name']) ?></div>
          <small class="text-muted"><?= e(truncate($b['description'] ?? '', 50)) ?></small>
        </td>
        <td><span class="badge bg-light text-dark border"><?= e($b['origin'] ?: 'India') ?></span></td>
        <td><small class="text-forest fw-semibold"><?= e($b['milk_yield'] ?: '—') ?></small></td>
        <td>
          <?php if (!empty($b['whatsapp_number'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-normal">
              <i class="bi bi-whatsapp me-1"></i><?= e($b['whatsapp_number']) ?>
            </span>
          <?php else: ?>
            <span class="badge bg-light text-muted border">Default (<?= e($primaryWpNumber) ?>)</span>
          <?php endif; ?>
        </td>
        <td>
          <span class="badge <?= $b['status']==='active'?'badge-active':'badge-inactive' ?>">
            <?= ucfirst(e($b['status'])) ?>
          </span>
        </td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest edit-breed-btn" data-breed='<?= json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Edit Breed">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-breed-btn" data-id="<?= $b['id'] ?>" data-name="<?= e($b['name']) ?>" title="Delete Breed">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($breeds)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">No breeds found. Click "Add Breed" to create one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Breed Modal (Add/Edit) -->
<div class="modal fade" id="breedModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="breedModalTitle">Add Indigenous Breed</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="breedForm">
          <input type="hidden" name="entity" value="breeds">
          <input type="hidden" name="id" id="breedId">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Breed Name *</label>
              <input type="text" name="name" id="breedName" class="form-control" placeholder="e.g. Gir, Hallikar, Ongole" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Origin / Region</label>
              <input type="text" name="origin" id="breedOrigin" class="form-control" placeholder="e.g. Saurashtra, Gujarat">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Milk Yield Description</label>
              <input type="text" name="milk_yield" id="breedYield" class="form-control" placeholder="e.g. 12 - 18 Liters / day (A2 Milk)">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="breedStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            
            <!-- WhatsApp Number Setting -->
            <div class="col-md-12">
              <label class="form-label fw-semibold">
                <i class="bi bi-whatsapp text-success me-1"></i> WhatsApp Number for Breed Enquiries
              </label>
              <div class="input-group">
                <input type="text" name="whatsapp_number" id="breedWhatsapp" class="form-control" placeholder="Leave empty for default (<?= e($primaryWpNumber) ?>) or enter custom">
                <?php if (!empty($configuredWpNumbers)): ?>
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Select Number
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item small" href="javascript:void(0)" onclick="setBreedWp('')"><em>Use Global Default (<?= e($primaryWpNumber) ?>)</em></a></li>
                  <li><hr class="dropdown-divider"></li>
                  <?php foreach ($configuredWpNumbers as $wp): ?>
                    <li>
                      <a class="dropdown-item small" href="javascript:void(0)" onclick="setBreedWp('<?= e($wp['number']) ?>')">
                        <i class="bi bi-telephone me-1"></i> <?= e($wp['number']) ?> <?= !empty($wp['label']) ? '('.e($wp['label']).')' : '' ?> <?= !empty($wp['primary']) ? '★ Primary' : '' ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
                <?php endif; ?>
              </div>
              <div class="form-text">Direct WhatsApp enquiries for this breed will route to this admin number.</div>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Image URL</label>
              <input type="url" name="image" id="breedImage" class="form-control" placeholder="https://images.unsplash.com/...">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Physical Characteristics</label>
              <textarea name="characteristics" id="breedCharacteristics" rows="2" class="form-control" placeholder="Convex forehead, long hanging ears, reddish coat..."></textarea>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Detailed Description</label>
              <textarea name="description" id="breedDescription" rows="3" class="form-control" placeholder="History, Vedic heritage, and significance of this breed..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveBreedBtn">Save Breed</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function setBreedWp(val) {
  document.getElementById("breedWhatsapp").value = val;
}

function resetBreedForm() {
  document.getElementById("breedForm").reset();
  document.getElementById("breedId").value = "";
  document.getElementById("breedWhatsapp").value = "";
  document.getElementById("breedModalTitle").textContent = "Add Indigenous Breed";
}

document.querySelectorAll(".edit-breed-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const b = JSON.parse(this.dataset.breed);
    document.getElementById("breedModalTitle").textContent = "Edit Breed: " + b.name;
    document.getElementById("breedId").value = b.id;
    document.getElementById("breedName").value = b.name || "";
    document.getElementById("breedOrigin").value = b.origin || "";
    document.getElementById("breedYield").value = b.milk_yield || "";
    document.getElementById("breedStatus").value = b.status || "active";
    document.getElementById("breedWhatsapp").value = b.whatsapp_number || "";
    document.getElementById("breedImage").value = b.image || "";
    document.getElementById("breedCharacteristics").value = b.characteristics || "";
    document.getElementById("breedDescription").value = b.description || "";
    new bootstrap.Modal(document.getElementById("breedModal")).show();
  });
});

document.getElementById("saveBreedBtn").addEventListener("click", async () => {
  const form = document.getElementById("breedForm");
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
      showAdminToast(result.message || "Failed to save breed.", "error");
    }
  } catch { showAdminToast("Network error occurred.", "error"); }
});

document.querySelectorAll(".delete-breed-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Are you sure you want to delete the breed \'" + this.dataset.name + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=breeds&id=" + this.dataset.id);
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
