<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'orders';
$admin_title = 'Orders';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

try {
    $orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
} catch (Exception $e) {
    $orders = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Organic Store Orders (<?= count($orders) ?>)</h5>
    <p class="text-muted small mb-0">Track customer purchases for Panchagavya, vermicompost, and organic items.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#orderModal" onclick="resetOrderForm()">
    <i class="bi bi-plus-lg me-1"></i> Record New Order
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Order Number</th>
        <th>Customer</th>
        <th>Amount</th>
        <th>Payment</th>
        <th>Order Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
      <tr>
        <td><code class="fw-bold text-forest"><?= e($o['order_number'] ?? ('ORD-'.$o['id'])) ?></code></td>
        <td>
          <div class="fw-semibold fs-6"><?= e($o['customer_name'] ?? 'Guest Devotee') ?></div>
          <small class="text-muted"><?= e($o['customer_email'] ?? '') ?><?= !empty($o['customer_phone']) ? ' • '.e($o['customer_phone']) : '' ?></small>
        </td>
        <td><span class="fw-bold text-forest fs-6">₹<?= number_format((float)($o['total_amount'] ?? 0)) ?></span></td>
        <td>
          <span class="badge <?= ($o['payment_status'] ?? '') === 'Completed' ? 'bg-success' : 'bg-warning text-dark' ?>">
            <?= e($o['payment_status'] ?? 'Pending') ?>
          </span>
        </td>
        <td>
          <?php
          $os = $o['order_status'] ?? 'Processing';
          $osBadge = match($os) {
            'Delivered' => 'bg-success',
            'Shipped' => 'bg-primary',
            'Processing' => 'bg-warning text-dark',
            'Cancelled' => 'bg-danger',
            default => 'bg-secondary'
          };
          ?>
          <span class="badge <?= $osBadge ?>"><?= e($os) ?></span>
        </td>
        <td><small class="text-muted"><?= formatDate($o['created_at']) ?></small></td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest view-order-btn" data-order='<?= json_encode($o, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="View Order Details">
              <i class="bi bi-eye"></i>
            </button>
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="Change Status">
                <i class="bi bi-gear"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item order-status-item" href="#" data-id="<?= $o['id'] ?>" data-status="Processing"><i class="bi bi-hourglass-split me-2 text-warning"></i>Processing</a></li>
                <li><a class="dropdown-item order-status-item" href="#" data-id="<?= $o['id'] ?>" data-status="Shipped"><i class="bi bi-truck me-2 text-primary"></i>Shipped</a></li>
                <li><a class="dropdown-item order-status-item" href="#" data-id="<?= $o['id'] ?>" data-status="Delivered"><i class="bi bi-check-circle me-2 text-success"></i>Delivered</a></li>
                <li><a class="dropdown-item order-status-item text-danger" href="#" data-id="<?= $o['id'] ?>" data-status="Cancelled"><i class="bi bi-x-circle me-2"></i>Cancelled</a></li>
              </ul>
            </div>
            <button class="btn btn-sm btn-outline-danger delete-order-btn" data-id="<?= $o['id'] ?>" data-num="<?= e($o['order_number'] ?? $o['id']) ?>" title="Delete Order">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($orders)): ?>
        <tr>
          <td colspan="7" class="text-center py-5 text-muted">
            <i class="bi bi-bag-x fs-2 d-block mb-2 text-muted"></i>
            No orders found yet. Click "Record New Order" to create a test order.
          </td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Record Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Record New Order</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="orderForm">
          <input type="hidden" name="entity" value="orders">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Customer Name *</label>
              <input type="text" name="customer_name" class="form-control" placeholder="Ramesh Kumar" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Customer Email *</label>
              <input type="email" name="customer_email" class="form-control" placeholder="ramesh@example.com" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Customer Phone</label>
              <input type="tel" name="customer_phone" class="form-control" placeholder="+91 98765 43210">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Total Amount (₹) *</label>
              <input type="number" step="0.01" name="total_amount" class="form-control" placeholder="649.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Payment Status</label>
              <select name="payment_status" class="form-select">
                <option value="Completed">Completed</option>
                <option value="Pending">Pending</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Order Status</label>
              <select name="order_status" class="form-select">
                <option value="Processing">Processing</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Shipping Address</label>
              <textarea name="shipping_address" rows="3" class="form-control" placeholder="Delivery street address, landmark, pincode..."></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveOrderBtn">Save Order</button>
      </div>
    </div>
  </div>
</div>

<!-- Order View Modal -->
<div class="modal fade" id="orderViewModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderViewTitle">Order Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" id="orderViewBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
function resetOrderForm() {
  document.getElementById("orderForm").reset();
}

document.querySelectorAll(".view-order-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const o = JSON.parse(this.dataset.order);
    document.getElementById("orderViewTitle").textContent = "Order: " + (o.order_number || o.id);
    document.getElementById("orderViewBody").innerHTML = `
      <div class="p-3 bg-light rounded-3 mb-3 border">
        <div class="fw-bold fs-5 text-forest">₹${parseFloat(o.total_amount || 0).toLocaleString("en-IN")}</div>
        <small class="text-muted">Payment: <strong>${o.payment_status || "Pending"}</strong> via ${o.payment_method || "UPI / Direct"}</small>
      </div>
      <div class="mb-3">
        <label class="text-muted small">Customer</label>
        <div class="fw-bold">${o.customer_name || "—"}</div>
        <div>${o.customer_email || ""}</div>
        <div>${o.customer_phone || ""}</div>
      </div>
      <div class="mb-3">
        <label class="text-muted small">Shipping Address</label>
        <div class="p-2 bg-light rounded border small">${o.shipping_address || "No shipping address provided."}</div>
      </div>
      <div>
        <label class="text-muted small">Order Status</label>
        <div><span class="badge bg-primary fs-6">${o.order_status || "Processing"}</span></div>
      </div>
    `;
    new bootstrap.Modal(document.getElementById("orderViewModal")).show();
  });
});

document.querySelectorAll(".order-status-item").forEach(item => {
  item.addEventListener("click", async function(e) {
    e.preventDefault();
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=toggle_status&entity=orders&id=" + this.dataset.id + "&status=" + this.dataset.status);
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message, "success");
        setTimeout(() => location.reload(), 600);
      }
    } catch { showAdminToast("Status change failed.", "error"); }
  });
});

document.querySelectorAll(".delete-order-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete order " + this.dataset.num + "?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=orders&id=" + this.dataset.id);
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
