<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'banners';
$admin_title = 'Manage Page Banners';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

try {
    $banners = $pdo->query("SELECT * FROM page_banners ORDER BY id ASC")->fetchAll();
} catch (Exception $e) {
    $banners = [];
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
  <div>
    <h5 class="fw-bold mb-1">Website Page Banners (<?= count($banners) ?> Pages)</h5>
    <p class="text-muted small mb-0">Add, change, or remove hero banner background images, titles, and badges for every page on the sanctuary website.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#bannerModal" onclick="resetBannerForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Custom Banner
  </button>
</div>

<!-- Banner Cards Grid -->
<div class="row g-4" id="bannersGrid">
  <?php foreach ($banners as $b): ?>
  <?php 
    $hasImage = !empty($b['banner_image']);
    $pageUrl = ($b['page_key'] === 'home') ? BASE_URL . '/index.php' : BASE_URL . '/' . $b['page_key'] . '.php';
  ?>
  <div class="col-md-6 col-xl-4 banner-card-col" data-page="<?= strtolower(e($b['page_name'] . ' ' . $b['page_key'])) ?>">
    <div class="card h-100 border rounded-4 shadow-sm overflow-hidden bg-white d-flex flex-column">
      
      <!-- Banner Image Preview Area -->
      <div class="position-relative text-white p-4 d-flex flex-column justify-content-between" 
           style="min-height: 180px; <?= $hasImage ? "background: var(--hero-overlay), url('" . e(getImageUrl($b['banner_image'])) . "') center/cover no-repeat;" : "background: #27272a;" ?>">
        
        <div class="d-flex justify-content-between align-items-start gap-2">
          <span class="badge bg-dark bg-opacity-75 border px-2 py-1 small">
            <i class="bi bi-file-earmark-code me-1"></i> <?= e($b['page_key']) ?>.php
          </span>
          <?php if ($hasImage): ?>
            <span class="badge bg-success px-2 py-1"><i class="bi bi-image me-1"></i> Custom Image</span>
          <?php else: ?>
            <span class="badge bg-secondary px-2 py-1"><i class="bi bi-palette me-1"></i> Default Theme</span>
          <?php endif; ?>
        </div>

        <div class="mt-3">
          <?php if (!empty($b['badge_text'])): ?>
            <span class="badge rounded-pill bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50 small mb-1">
              <?= e($b['badge_text']) ?>
            </span>
          <?php endif; ?>
          <h6 class="fw-bold text-white mb-0 text-truncate" title="<?= strip_tags($b['title'] ?? '') ?>">
            <?= strip_tags($b['title'] ?? $b['page_name']) ?>
          </h6>
        </div>
      </div>

      <!-- Card Details & Actions -->
      <div class="card-body p-3 d-flex flex-column justify-content-between flex-grow-1">
        <div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-forest mb-0"><?= e($b['page_name']) ?></h6>
            <span class="badge <?= $b['status'] === 'active' ? 'badge-active' : 'badge-inactive' ?>">
              <?= ucfirst(e($b['status'])) ?>
            </span>
          </div>
          <p class="small text-muted mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:36px;">
            <?= e($b['subtitle'] ?? 'Default page header subtitle.') ?>
          </p>
        </div>

        <div class="pt-3 border-top d-flex align-items-center justify-content-between gap-2">
          <a href="<?= $pageUrl ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View live page">
            <i class="bi bi-box-arrow-up-right me-1"></i> View Page
          </a>
          <div class="d-flex gap-1">
            <?php if ($hasImage): ?>
            <button type="button" class="btn btn-sm btn-outline-danger remove-banner-img-btn" 
                    data-id="<?= $b['id'] ?>" 
                    data-name="<?= e($b['page_name']) ?>"
                    title="Remove Banner Image (Reset to Default)">
              <i class="bi bi-x-circle me-1"></i> Remove Image
            </button>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-gold edit-banner-btn" 
                    data-banner='<?= json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'
                    title="Edit Banner Settings">
              <i class="bi bi-pencil-square me-1"></i> Edit
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Add / Edit Banner Modal -->
<div class="modal fade" id="bannerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-forest text-white">
        <h5 class="modal-title fw-bold" id="bannerModalTitle">Edit Page Banner</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="bannerForm" enctype="multipart/form-data">
          <input type="hidden" name="entity" value="page_banners">
          <input type="hidden" name="id" id="bannerId">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Page Identifier Key <span class="text-danger">*</span></label>
              <input type="text" name="page_key" id="bannerPageKey" class="form-control" placeholder="e.g. about, cows, breeds, products" required>
              <small class="text-muted">Unique page filename key (without .php).</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Page Human Name <span class="text-danger">*</span></label>
              <input type="text" name="page_name" id="bannerPageName" class="form-control" placeholder="e.g. About Us, Indigenous Breeds" required>
            </div>

            <!-- Banner Image Section -->
            <div class="col-12">
              <div class="p-3 bg-light rounded-3 border">
                <label class="form-label fw-semibold mb-2"><i class="bi bi-image text-warning me-1"></i> Banner Background Image</label>
                
                <div class="mb-3">
                  <label class="form-label small text-muted">Option A: Image URL</label>
                  <input type="url" name="banner_image" id="bannerImageUrl" class="form-control" placeholder="https://images.unsplash.com/photo-...">
                </div>

                <div>
                  <label class="form-label small text-muted">Option B: Upload Image from Computer</label>
                  <input type="file" name="image_file" id="bannerImageFile" class="form-control" accept="image/*">
                </div>

                <div id="bannerImagePreviewBox" class="mt-3 p-2 bg-white rounded-3 border d-none">
                  <div class="small text-muted mb-1">Current Preview:</div>
                  <img id="bannerImgPreview" src="" alt="Banner Preview" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px;">
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Hero Badge Tagline</label>
              <input type="text" name="badge_text" id="bannerBadgeText" class="form-control" placeholder="e.g. Vedic Heritage & Gau Seva">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="bannerStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive (Use Default)</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Banner Heading Title (HTML Supported)</label>
              <input type="text" name="title" id="bannerTitle" class="form-control" placeholder="e.g. Preserving India's &lt;span&gt;Sacred Bovine Heritage&lt;/span&gt;">
              <small class="text-muted">Use <code>&lt;span&gt;Word&lt;/span&gt;</code> to apply sacred gold gradient styling to key words.</small>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Banner Subtitle / Description</label>
              <textarea name="subtitle" id="bannerSubtitle" rows="3" class="form-control" placeholder="Brief inspiring message displayed in the page hero header..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer border-top bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4 fw-bold" id="saveBannerBtn">
          <i class="bi bi-check-lg me-1"></i> Save Banner Settings
        </button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '
<script>
function resetBannerForm() {
  document.getElementById("bannerForm").reset();
  document.getElementById("bannerId").value = "";
  document.getElementById("bannerModalTitle").textContent = "Add Custom Page Banner";
  document.getElementById("bannerPageKey").readOnly = false;
  document.getElementById("bannerImagePreviewBox").classList.add("d-none");
}

document.querySelectorAll(".edit-banner-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const b = JSON.parse(this.dataset.banner);
    document.getElementById("bannerModalTitle").textContent = "Edit Banner: " + b.page_name;
    document.getElementById("bannerId").value = b.id;
    document.getElementById("bannerPageKey").value = b.page_key || "";
    document.getElementById("bannerPageKey").readOnly = true;
    document.getElementById("bannerPageName").value = b.page_name || "";
    document.getElementById("bannerImageUrl").value = b.banner_image || "";
    document.getElementById("bannerBadgeText").value = b.badge_text || "";
    document.getElementById("bannerTitle").value = b.title || "";
    document.getElementById("bannerSubtitle").value = b.subtitle || "";
    document.getElementById("bannerStatus").value = b.status || "active";

    const previewBox = document.getElementById("bannerImagePreviewBox");
    const previewImg = document.getElementById("bannerImgPreview");
    if (b.banner_image) {
      previewImg.src = b.banner_image;
      previewBox.classList.remove("d-none");
    } else {
      previewBox.classList.add("d-none");
    }

    new bootstrap.Modal(document.getElementById("bannerModal")).show();
  });
});

document.getElementById("saveBannerBtn").addEventListener("click", async () => {
  const form = document.getElementById("bannerForm");
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const saveBtn = document.getElementById("saveBannerBtn");
  const origText = saveBtn.innerHTML;
  saveBtn.disabled = true;
  saveBtn.innerHTML = "<span class=\"spinner-border spinner-border-sm me-2\"></span>Saving...";

  const fd = new FormData(form);

  try {
    const res = await fetch(BASE_URL + "/admin/api/crud_api.php", {
      method: "POST",
      body: fd
    });
    const result = await res.json();
    if (result.success) {
      showAdminToast(result.message, "success");
      setTimeout(() => location.reload(), 600);
    } else {
      showAdminToast(result.message || "Failed to save banner.", "error");
    }
  } catch (err) {
    showAdminToast("Network error occurred while saving banner.", "error");
  } finally {
    saveBtn.disabled = false;
    saveBtn.innerHTML = origText;
  }
});

// Remove Banner Image Handler
document.querySelectorAll(".remove-banner-img-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    const id = this.dataset.id;
    const name = this.dataset.name;
    if (!confirm("Are you sure you want to remove the banner image for \'" + name + "\'? It will reset to the default sacred gradient background.")) {
      return;
    }

    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=remove_banner_image&id=" + id);
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message, "success");
        setTimeout(() => location.reload(), 600);
      } else {
        showAdminToast(result.message || "Failed to remove banner image.", "error");
      }
    } catch {
      showAdminToast("Network error occurred.", "error");
    }
  });
});
</script>
';
include __DIR__ . '/includes/admin_footer.php';
?>
