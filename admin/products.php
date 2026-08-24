<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'products';
$admin_title = 'Manage Products';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$products = $pdo->query("SELECT p.*, pc.name AS category_name FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.id ORDER BY p.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM product_categories WHERE status='active' ORDER BY name ASC")->fetchAll();

$wpNumbersRaw = getSetting('whatsapp_numbers', '[]');
$configuredWpNumbers = json_decode($wpNumbersRaw, true) ?: [];
$primaryWpNumber = getSetting('whatsapp_number', '919845088990');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Organic Store Products (<?= count($products) ?>)</h5>
    <p class="text-muted small mb-0">Manage Panchagavya, vermicompost, cow dung diyas, and direct WhatsApp routing.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetProductForm()">
    <i class="bi bi-plus-lg me-1"></i> Add Product
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Image</th>
        <th>Product Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock Units</th>
        <th>WhatsApp</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
      <tr>
        <td>
          <img src="<?= e($p['image'] ?: 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=150&q=80') ?>" class="table-thumb" alt="<?= e($p['name']) ?>">
        </td>
        <td>
          <div class="fw-semibold fs-6"><?= e($p['name']) ?></div>
          <small class="text-muted"><?= e(truncate($p['description'] ?? '', 45)) ?></small>
        </td>
        <td><span class="badge bg-light text-dark border"><?= e($p['category_name'] ?: 'Organic') ?></span></td>
        <td><span class="fw-bold text-forest fs-6">₹<?= number_format((float)$p['price']) ?></span></td>
        <td>
          <?php if ($p['stock'] > 10): ?>
            <span class="badge bg-success"><?= $p['stock'] ?> in stock</span>
          <?php elseif ($p['stock'] > 0): ?>
            <span class="badge bg-warning text-dark"><?= $p['stock'] ?> low stock</span>
          <?php else: ?>
            <span class="badge bg-danger">Out of stock</span>
          <?php endif; ?>
        </td>
        <td>
          <?php if (!empty($p['whatsapp_number'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-normal">
              <i class="bi bi-whatsapp me-1"></i><?= e($p['whatsapp_number']) ?>
            </span>
          <?php else: ?>
            <span class="badge bg-light text-muted border">Default (<?= e($primaryWpNumber) ?>)</span>
          <?php endif; ?>
        </td>
        <td>
          <span class="badge <?= $p['status']==='active'?'badge-active':'badge-inactive' ?>">
            <?= ucfirst(e($p['status'])) ?>
          </span>
        </td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest edit-product-btn" data-product='<?= json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="Edit Product">
              <i class="bi bi-pencil-square"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger delete-product-btn" data-id="<?= $p['id'] ?>" data-name="<?= e($p['name']) ?>" title="Delete Product">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($products)): ?>
        <tr><td colspan="8" class="text-center py-4 text-muted">No products found. Click "Add Product" to create one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalTitle">Add Organic Product</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="productForm">
          <input type="hidden" name="entity" value="products">
          <input type="hidden" name="id" id="productId">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label fw-semibold">Product Name *</label>
              <input type="text" name="name" id="productName" class="form-control" placeholder="e.g. Organic Cow Dung Manure (5 kg)" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Category</label>
              <select name="category_id" id="productCategory" class="form-select">
                <option value="">Select category</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Price (₹) *</label>
              <input type="number" step="0.01" name="price" id="productPrice" class="form-control" placeholder="199.00" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Stock Quantity *</label>
              <input type="number" name="stock" id="productStock" class="form-control" placeholder="100" required>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="productStatus" class="form-select">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

            <!-- WhatsApp Number Setting -->
            <div class="col-md-12">
              <label class="form-label fw-semibold">
                <i class="bi bi-whatsapp text-success me-1"></i> WhatsApp Number for Product Orders / Enquiries
              </label>
              <div class="input-group">
                <input type="text" name="whatsapp_number" id="productWhatsapp" class="form-control" placeholder="Leave empty for default (<?= e($primaryWpNumber) ?>) or enter custom">
                <?php if (!empty($configuredWpNumbers)): ?>
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Select Number
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a class="dropdown-item small" href="javascript:void(0)" onclick="setProductWp('')"><em>Use Global Default (<?= e($primaryWpNumber) ?>)</em></a></li>
                  <li><hr class="dropdown-divider"></li>
                  <?php foreach ($configuredWpNumbers as $wp): ?>
                    <li>
                      <a class="dropdown-item small" href="javascript:void(0)" onclick="setProductWp('<?= e($wp['number']) ?>')">
                        <i class="bi bi-telephone me-1"></i> <?= e($wp['number']) ?> <?= !empty($wp['label']) ? '('.e($wp['label']).')' : '' ?> <?= !empty($wp['primary']) ? '★ Primary' : '' ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
                <?php endif; ?>
              </div>
              <div class="form-text small text-muted">Controlled by Admin. When customers click "WhatsApp" on this product, enquiries route directly to this WhatsApp number.</div>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Product Image URL</label>
              <input type="url" name="image" id="productImage" class="form-control" placeholder="https://images.unsplash.com/...">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Product Description &amp; Benefits</label>
              <textarea name="description" id="productDescription" rows="4" class="form-control" placeholder="100% aged, sun-cured, and microbially rich cow dung manure..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveProductBtn">Save Product</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function setProductWp(num) {
  document.getElementById("productWhatsapp").value = num;
}

function resetProductForm() {
  document.getElementById("productForm").reset();
  document.getElementById("productId").value = "";
  document.getElementById("productWhatsapp").value = "";
  document.getElementById("productModalTitle").textContent = "Add Organic Product";
}

document.querySelectorAll(".edit-product-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const p = JSON.parse(this.dataset.product);
    document.getElementById("productModalTitle").textContent = "Edit Product: " + p.name;
    document.getElementById("productId").value = p.id;
    document.getElementById("productName").value = p.name || "";
    document.getElementById("productCategory").value = p.category_id || "";
    document.getElementById("productPrice").value = p.price || "";
    document.getElementById("productStock").value = p.stock || 0;
    document.getElementById("productStatus").value = p.status || "active";
    document.getElementById("productWhatsapp").value = p.whatsapp_number || "";
    document.getElementById("productImage").value = p.image || "";
    document.getElementById("productDescription").value = p.description || "";
    new bootstrap.Modal(document.getElementById("productModal")).show();
  });
});

document.getElementById("saveProductBtn").addEventListener("click", async () => {
  const form = document.getElementById("productForm");
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
      showAdminToast(result.message || "Failed to save product.", "error");
    }
  } catch { showAdminToast("Network error occurred.", "error"); }
});

document.querySelectorAll(".delete-product-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Are you sure you want to delete product \'" + this.dataset.name + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=products&id=" + this.dataset.id);
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
