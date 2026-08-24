<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'cows'; $admin_title = 'Manage Cows';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';
$cows = $pdo->query("SELECT c.*, b.name AS breed_name FROM cows c LEFT JOIN breeds b ON c.breed_id = b.id ORDER BY c.created_at DESC")->fetchAll();
$breeds = getActiveBreeds();

$wpNumbersRaw = getSetting('whatsapp_numbers', '[]');
$configuredWpNumbers = json_decode($wpNumbersRaw, true) ?: [];
$primaryWpNumber = getSetting('whatsapp_number', '919845088990');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">All Cows (<?= count($cows) ?>)</h5>
    <p class="text-muted small mb-0">Manage sanctuary cows, adoption status, health, and direct WhatsApp routing.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#cowModal" onclick="resetCowForm()"><i class="bi bi-plus-lg me-1"></i> Add Cow</button>
</div>
<div class="admin-table">
  <table class="table">
    <thead><tr><th>Image</th><th>Name</th><th>Tag</th><th>Breed</th><th>Gender</th><th>Health</th><th>WhatsApp</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($cows as $c): ?>
      <tr>
        <td><img src="<?= e($c['image'] ?: 'https://via.placeholder.com/48') ?>" class="table-thumb" alt=""></td>
        <td class="fw-semibold"><?= e($c['name']) ?></td>
        <td><code><?= e($c['tag_number']) ?></code></td>
        <td><?= e($c['breed_name'] ?? '-') ?></td>
        <td><?= e($c['gender']) ?></td>
        <td><span class="badge bg-success"><?= e($c['health_status']) ?></span></td>
        <td>
          <?php if (!empty($c['whatsapp_number'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-normal">
              <i class="bi bi-whatsapp me-1"></i><?= e($c['whatsapp_number']) ?>
            </span>
          <?php else: ?>
            <span class="badge bg-light text-muted border">Default (<?= e($primaryWpNumber) ?>)</span>
          <?php endif; ?>
        </td>
        <td><span class="badge <?= $c['status']==='Active'?'badge-active':'badge-inactive' ?>"><?= e($c['status']) ?></span></td>
        <td>
          <button class="btn btn-sm btn-outline-forest edit-cow-btn" data-cow='<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger delete-cow-btn" data-id="<?= $c['id'] ?>" data-name="<?= e($c['name']) ?>"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Cow Modal -->
<div class="modal fade" id="cowModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title" id="cowModalTitle">Add Cow</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
  <div class="modal-body p-4">
    <form id="cowForm">
      <input type="hidden" name="id" id="cowId">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label fw-semibold">Name *</label><input type="text" name="name" id="cowName" class="form-control" placeholder="e.g. Ganga" required></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Tag Number *</label><input type="text" name="tag_number" id="cowTag" class="form-control" placeholder="e.g. KG-101" required></div>
        <div class="col-md-6"><label class="form-label fw-semibold">Breed</label>
          <select name="breed_id" id="cowBreed" class="form-select"><option value="">Select breed</option>
            <?php foreach ($breeds as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label fw-semibold">Gender</label>
          <select name="gender" id="cowGender" class="form-select"><option value="Female">Female</option><option value="Male">Male</option></select>
        </div>
        <div class="col-md-3"><label class="form-label fw-semibold">Date of Birth</label><input type="date" name="dob" id="cowDob" class="form-control"></div>
        <div class="col-md-4"><label class="form-label fw-semibold">Arrival Date</label><input type="date" name="arrival_date" id="cowArrival" class="form-control"></div>
        <div class="col-md-4"><label class="form-label fw-semibold">Health Status</label>
          <select name="health_status" id="cowHealth" class="form-select">
            <?php foreach (['Healthy','Under Treatment','Recovering','Special Care','Elderly Care'] as $h): ?><option value="<?= $h ?>"><?= $h ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label fw-semibold">Status</label>
          <select name="status" id="cowStatus" class="form-select">
            <?php foreach (['Active','Adopted','Transferred','Deceased'] as $s): ?><option value="<?= $s ?>"><?= $s ?></option><?php endforeach; ?>
          </select>
        </div>
        
        <!-- WhatsApp Number Setting -->
        <div class="col-md-12">
          <label class="form-label fw-semibold">
            <i class="bi bi-whatsapp text-success me-1"></i> WhatsApp Number for Adoption / Feed Enquiries
          </label>
          <div class="input-group">
            <input type="text" name="whatsapp_number" id="cowWhatsapp" class="form-control" placeholder="Leave empty for default (<?= e($primaryWpNumber) ?>) or enter custom">
            <?php if (!empty($configuredWpNumbers)): ?>
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              Select Number
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item small" href="javascript:void(0)" onclick="setCowWp('')"><em>Use Global Default (<?= e($primaryWpNumber) ?>)</em></a></li>
              <li><hr class="dropdown-divider"></li>
              <?php foreach ($configuredWpNumbers as $wp): ?>
                <li>
                  <a class="dropdown-item small" href="javascript:void(0)" onclick="setCowWp('<?= e($wp['number']) ?>')">
                    <i class="bi bi-telephone me-1"></i> <?= e($wp['number']) ?> <?= !empty($wp['label']) ? '('.e($wp['label']).')' : '' ?> <?= !empty($wp['primary']) ? '★ Primary' : '' ?>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
          </div>
          <div class="form-text small text-muted">Controlled by Admin. When visitors click "Adopt Now" or "Feed Now", requests route directly to this WhatsApp number.</div>
        </div>

        <div class="col-12"><label class="form-label fw-semibold">Image URL</label><input type="url" name="image" id="cowImage" class="form-control" placeholder="https://..."></div>
        <div class="col-12"><label class="form-label fw-semibold">Story</label><textarea name="story" id="cowStory" rows="3" class="form-control"></textarea></div>
      </div>
    </form>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-gold" id="saveCowBtn">Save Cow</button></div>
</div></div></div>

<?php
$admin_extra_js = '<script>
function setCowWp(num) {
  document.getElementById("cowWhatsapp").value = num;
}

function resetCowForm() {
  document.getElementById("cowForm").reset();
  document.getElementById("cowId").value = "";
  document.getElementById("cowWhatsapp").value = "";
  document.getElementById("cowModalTitle").textContent = "Add Cow";
}
document.querySelectorAll(".edit-cow-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const c = JSON.parse(this.dataset.cow);
    document.getElementById("cowModalTitle").textContent = "Edit " + c.name;
    document.getElementById("cowId").value = c.id;
    document.getElementById("cowName").value = c.name;
    document.getElementById("cowTag").value = c.tag_number;
    document.getElementById("cowBreed").value = c.breed_id || "";
    document.getElementById("cowGender").value = c.gender;
    document.getElementById("cowDob").value = c.dob || "";
    document.getElementById("cowArrival").value = c.arrival_date || "";
    document.getElementById("cowHealth").value = c.health_status;
    document.getElementById("cowStatus").value = c.status;
    document.getElementById("cowWhatsapp").value = c.whatsapp_number || "";
    document.getElementById("cowImage").value = c.image || "";
    document.getElementById("cowStory").value = c.story || "";
    new bootstrap.Modal(document.getElementById("cowModal")).show();
  });
});
document.getElementById("saveCowBtn").addEventListener("click", async () => {
  const form = document.getElementById("cowForm");
  if (!form.checkValidity()) { form.reportValidity(); return; }
  const fd = new FormData(form);
  const data = Object.fromEntries(fd.entries());
  try {
    const res = await fetch(BASE_URL + "/admin/api/cows_api.php", {
      method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify(data)
    });
    const result = await res.json();
    if (result.success) { showAdminToast(result.message); location.reload(); }
    else { showAdminToast(result.message, "error"); }
  } catch { showAdminToast("Failed to save.", "error"); }
});
document.querySelectorAll(".delete-cow-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete " + this.dataset.name + "? This cannot be undone.")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/cows_api.php?action=delete&id=" + this.dataset.id);
      const result = await res.json();
      if (result.success) { showAdminToast(result.message); location.reload(); }
      else { showAdminToast(result.message, "error"); }
    } catch { showAdminToast("Delete failed.", "error"); }
  });
});
</script>';
include __DIR__ . '/includes/admin_footer.php';
?>
