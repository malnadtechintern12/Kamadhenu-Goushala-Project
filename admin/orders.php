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

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
  <div>
    <h5 class="fw-bold mb-1">Organic Store Orders (<?= count($orders) ?>)</h5>
    <p class="text-muted small mb-0">Track devotee purchases for Panchagavya, vermicompost, dhoop, and sacred organic items.</p>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#orderModal" onclick="resetOrderForm()">
      <i class="bi bi-plus-lg me-1"></i> Record New Order
    </button>
  </div>
</div>

<div class="admin-table">
  <table class="table align-middle">
    <thead>
      <tr>
        <th>Order Number</th>
        <th>Customer</th>
        <th>Amount</th>
        <th>Payment</th>
        <th>Order Status</th>
        <th>Date</th>
        <th class="text-end pe-3">Actions &amp; Settings</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
      <?php
        $os = $o['order_status'] ?? 'Processing';
        $osBadge = match(strtolower($os)) {
          'delivered'  => 'bg-success',
          'shipped'    => 'bg-primary',
          'processing' => 'bg-warning text-dark',
          'confirmed'  => 'bg-info text-dark',
          'pending'    => 'bg-secondary',
          'cancelled'  => 'bg-danger',
          default      => 'bg-secondary'
        };

        $ps = $o['payment_status'] ?? 'Pending';
        $psBadge = match(strtolower($ps)) {
          'completed', 'paid' => 'bg-success',
          'failed'            => 'bg-danger',
          default             => 'bg-warning text-dark'
        };
      ?>
      <tr>
        <td>
          <code class="fw-bold text-forest fs-6"><?= e($o['order_number'] ?? ('ORD-'.$o['id'])) ?></code>
          <?php if (!empty($o['notes'])): ?>
            <span class="d-block text-muted text-truncate small" style="max-width: 160px;" title="<?= e($o['notes']) ?>">
              <i class="bi bi-chat-left-text me-1"></i><?= e($o['notes']) ?>
            </span>
          <?php endif; ?>
        </td>
        <td>
          <div class="fw-semibold fs-6 text-dark"><?= e($o['customer_name'] ?? 'Guest Devotee') ?></div>
          <small class="text-muted"><?= e($o['customer_email'] ?? '') ?><?= !empty($o['customer_phone']) ? ' • '.e($o['customer_phone']) : '' ?></small>
        </td>
        <td>
          <span class="fw-bold text-forest fs-6">₹<?= number_format((float)($o['total_amount'] ?? 0), 2) ?></span>
        </td>
        <td>
          <span class="badge <?= $psBadge ?> px-2 py-1">
            <?= e($ps) ?>
          </span>
          <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
            <?= e($o['payment_method'] ?? 'Direct / UPI') ?>
          </small>
        </td>
        <td>
          <span class="badge <?= $osBadge ?> px-2.5 py-1.5 fs-7">
            <?= e($os) ?>
          </span>
        </td>
        <td>
          <small class="text-muted"><?= formatDate($o['created_at']) ?></small>
        </td>
        <td class="text-end pe-3">
          <div class="d-inline-flex gap-1 align-items-center">
            <!-- View Details Button -->
            <button class="btn btn-sm btn-outline-forest view-order-btn" 
                    data-order='<?= json_encode($o, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' 
                    title="View Order Details &amp; Items">
              <i class="bi bi-eye"></i>
            </button>

            <!-- Edit Order Button -->
            <button class="btn btn-sm btn-outline-primary edit-order-btn" 
                    data-order='<?= json_encode($o, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' 
                    title="Edit Order Info">
              <i class="bi bi-pencil-square"></i>
            </button>

            <!-- Full Settings & Options Dropdown -->
            <div class="dropdown d-inline-block">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-1" 
                      data-bs-toggle="dropdown" 
                      data-bs-popper-config='{"strategy":"fixed"}' 
                      aria-expanded="false" 
                      title="Full Order Settings &amp; Status Options">
                <i class="bi bi-gear-fill text-secondary"></i>
                <span class="d-none d-md-inline small fw-semibold">Options</span>
              </button>
              
              <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" style="min-width: 260px; max-height: 480px; overflow-y: auto;">
                <!-- Section 1: Order Actions -->
                <li class="dropdown-header text-uppercase small fw-bold text-forest px-3 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                  <i class="bi bi-sliders me-1"></i> Order Management
                </li>
                <li>
                  <a class="dropdown-item py-2 view-order-btn" href="#" data-order='<?= json_encode($o, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    <i class="bi bi-eye text-forest me-2"></i> View Order Details
                  </a>
                </li>
                <li>
                  <a class="dropdown-item py-2 edit-order-btn" href="#" data-order='<?= json_encode($o, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    <i class="bi bi-pencil-square text-primary me-2"></i> Edit Order Info
                  </a>
                </li>
                <li>
                  <a class="dropdown-item py-2 print-receipt-btn" href="#" data-order='<?= json_encode($o, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                    <i class="bi bi-printer text-secondary me-2"></i> Print Tax Invoice / Receipt
                  </a>
                </li>

                <!-- Section 2: Order Status Options -->
                <li><hr class="dropdown-divider my-2"></li>
                <li class="dropdown-header text-uppercase small fw-bold text-muted px-3 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                  <i class="bi bi-truck me-1"></i> Update Order Status
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 order-status-item <?= strtolower($os) === 'pending' ? 'active' : '' ?>" href="#" data-id="<?= $o['id'] ?>" data-status="Pending">
                    <span><i class="bi bi-clock me-2 text-secondary"></i>Pending</span>
                    <?php if (strtolower($os) === 'pending'): ?><i class="bi bi-check2 fw-bold text-forest"></i><?php endif; ?>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 order-status-item <?= strtolower($os) === 'confirmed' ? 'active' : '' ?>" href="#" data-id="<?= $o['id'] ?>" data-status="Confirmed">
                    <span><i class="bi bi-patch-check me-2 text-info"></i>Confirmed</span>
                    <?php if (strtolower($os) === 'confirmed'): ?><i class="bi bi-check2 fw-bold text-forest"></i><?php endif; ?>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 order-status-item <?= strtolower($os) === 'processing' ? 'active' : '' ?>" href="#" data-id="<?= $o['id'] ?>" data-status="Processing">
                    <span><i class="bi bi-hourglass-split me-2 text-warning"></i>Processing</span>
                    <?php if (strtolower($os) === 'processing'): ?><i class="bi bi-check2 fw-bold text-forest"></i><?php endif; ?>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 order-status-item <?= strtolower($os) === 'shipped' ? 'active' : '' ?>" href="#" data-id="<?= $o['id'] ?>" data-status="Shipped">
                    <span><i class="bi bi-truck me-2 text-primary"></i>Shipped</span>
                    <?php if (strtolower($os) === 'shipped'): ?><i class="bi bi-check2 fw-bold text-forest"></i><?php endif; ?>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 order-status-item <?= strtolower($os) === 'delivered' ? 'active' : '' ?>" href="#" data-id="<?= $o['id'] ?>" data-status="Delivered">
                    <span><i class="bi bi-check2-all me-2 text-success"></i>Delivered</span>
                    <?php if (strtolower($os) === 'delivered'): ?><i class="bi bi-check2 fw-bold text-forest"></i><?php endif; ?>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 order-status-item text-danger <?= strtolower($os) === 'cancelled' ? 'active' : '' ?>" href="#" data-id="<?= $o['id'] ?>" data-status="Cancelled">
                    <span><i class="bi bi-x-circle me-2"></i>Cancelled</span>
                    <?php if (strtolower($os) === 'cancelled'): ?><i class="bi bi-check2 fw-bold"></i><?php endif; ?>
                  </a>
                </li>

                <!-- Section 3: Payment Status Options -->
                <li><hr class="dropdown-divider my-2"></li>
                <li class="dropdown-header text-uppercase small fw-bold text-muted px-3 py-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                  <i class="bi bi-credit-card me-1"></i> Update Payment Status
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 order-payment-item <?= in_array(strtolower($ps), ['completed', 'paid']) ? 'active' : '' ?>" href="#" data-id="<?= $o['id'] ?>" data-payment-status="Completed">
                    <span><i class="bi bi-check-circle-fill me-2 text-success"></i>Mark Paid / Completed</span>
                    <?php if (in_array(strtolower($ps), ['completed', 'paid'])): ?><i class="bi bi-check2 fw-bold text-forest"></i><?php endif; ?>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 order-payment-item <?= strtolower($ps) === 'pending' ? 'active' : '' ?>" href="#" data-id="<?= $o['id'] ?>" data-payment-status="Pending">
                    <span><i class="bi bi-clock-history me-2 text-warning"></i>Mark Payment Pending</span>
                    <?php if (strtolower($ps) === 'pending'): ?><i class="bi bi-check2 fw-bold text-forest"></i><?php endif; ?>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 order-payment-item <?= strtolower($ps) === 'failed' ? 'active' : '' ?>" href="#" data-id="<?= $o['id'] ?>" data-payment-status="Failed">
                    <span><i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>Mark Payment Failed</span>
                    <?php if (strtolower($ps) === 'failed'): ?><i class="bi bi-check2 fw-bold"></i><?php endif; ?>
                  </a>
                </li>

                <!-- Section 4: Delete Action -->
                <li><hr class="dropdown-divider my-2"></li>
                <li>
                  <a class="dropdown-item py-2 text-danger delete-order-btn" href="#" data-id="<?= $o['id'] ?>" data-num="<?= e($o['order_number'] ?? $o['id']) ?>">
                    <i class="bi bi-trash me-2"></i> Delete Order
                  </a>
                </li>
              </ul>
            </div>

            <!-- Direct Delete Button -->
            <button class="btn btn-sm btn-outline-danger delete-order-btn" 
                    data-id="<?= $o['id'] ?>" 
                    data-num="<?= e($o['order_number'] ?? $o['id']) ?>" 
                    title="Delete Order">
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

<!-- Record / Edit Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderModalTitle">Record New Order</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="orderForm">
          <input type="hidden" name="entity" value="orders">
          <input type="hidden" name="id" id="orderId" value="">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Customer Name *</label>
              <input type="text" name="customer_name" id="orderCustomerName" class="form-control" placeholder="Ramesh Kumar" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Customer Email *</label>
              <input type="email" name="customer_email" id="orderCustomerEmail" class="form-control" placeholder="ramesh@example.com" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Customer Phone</label>
              <input type="tel" name="customer_phone" id="orderCustomerPhone" class="form-control" placeholder="+91 98765 43210">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Total Amount (₹) *</label>
              <input type="number" step="0.01" name="total_amount" id="orderTotalAmount" class="form-control" placeholder="649.00" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Payment Status</label>
              <select name="payment_status" id="orderPaymentStatus" class="form-select">
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
                <option value="Failed">Failed</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Payment Method</label>
              <input type="text" name="payment_method" id="orderPaymentMethod" class="form-control" placeholder="UPI / Cash on Delivery / Direct">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Order Status</label>
              <select name="order_status" id="orderOrderStatus" class="form-select">
                <option value="Pending">Pending</option>
                <option value="Confirmed">Confirmed</option>
                <option value="Processing" selected>Processing</option>
                <option value="Shipped">Shipped</option>
                <option value="Delivered">Delivered</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Order Notes</label>
              <input type="text" name="notes" id="orderNotes" class="form-control" placeholder="Special delivery instructions or remarks">
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Shipping Address</label>
              <textarea name="shipping_address" id="orderShippingAddress" rows="3" class="form-control" placeholder="Delivery street address, landmark, pincode..."></textarea>
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between align-items-center">
        <h5 class="modal-title" id="orderViewTitle">Order Details</h5>
        <div class="d-flex gap-2 align-items-center">
          <button type="button" class="btn btn-sm btn-outline-light" id="orderViewPrintBtn">
            <i class="bi bi-printer me-1"></i> Print Invoice
          </button>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body p-4" id="orderViewBody"></div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-outline-primary" id="orderViewEditBtn">
          <i class="bi bi-pencil-square me-1"></i> Edit Order
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
// Reset or initialize order form
function resetOrderForm() {
  const form = document.getElementById("orderForm");
  if (form) form.reset();
  const idEl = document.getElementById("orderId");
  if (idEl) idEl.value = "";
  const titleEl = document.getElementById("orderModalTitle");
  if (titleEl) titleEl.textContent = "Record New Order";
}

// Global order data cache for current modal
let currentModalOrder = null;
let currentModalItems = [];

// Print Invoice Function
function printOrderReceipt(orderData, items) {
  const printWin = window.open("", "_blank", "width=850,height=900");
  if (!printWin) {
    showAdminToast("Pop-up window was blocked. Please allow pop-ups to print invoice.", "error");
    return;
  }
  let itemsRows = "";
  if (items && items.length > 0) {
    items.forEach((it, idx) => {
      itemsRows += `
        <tr>
          <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">${idx + 1}</td>
          <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;"><strong>${it.product_name || "Product"}</strong></td>
          <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">${it.quantity || 1}</td>
          <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">₹${parseFloat(it.unit_price || 0).toLocaleString("en-IN", {minimumFractionDigits: 2})}</td>
          <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right; font-weight: bold; color: #28563C;">₹${parseFloat(it.subtotal || 0).toLocaleString("en-IN", {minimumFractionDigits: 2})}</td>
        </tr>
      `;
    });
  } else {
    itemsRows = `
      <tr>
        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">1</td>
        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0;"><strong>Organic Store Purchase</strong></td>
        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center;">1</td>
        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right;">₹${parseFloat(orderData.total_amount || 0).toLocaleString("en-IN", {minimumFractionDigits: 2})}</td>
        <td style="padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: right; font-weight: bold; color: #28563C;">₹${parseFloat(orderData.total_amount || 0).toLocaleString("en-IN", {minimumFractionDigits: 2})}</td>
      </tr>
    `;
  }

  printWin.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Invoice - ${orderData.order_number || orderData.id}</title>
      <style>
        body { font-family: "Segoe UI", Arial, sans-serif; padding: 36px; color: #2d3748; line-height: 1.5; background: #fff; }
        .invoice-box { max-width: 800px; margin: auto; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #28563C; padding-bottom: 18px; margin-bottom: 24px; }
        .brand-name { font-size: 24px; font-weight: 800; color: #28563C; letter-spacing: 0.5px; }
        .brand-sub { font-size: 13px; color: #718096; }
        .invoice-title { text-align: right; }
        .invoice-title h2 { margin: 0; font-size: 20px; color: #173B2A; }
        .info-grid { display: flex; justify-content: space-between; margin-bottom: 24px; }
        .info-col { width: 48%; background: #f8faf9; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .info-title { font-size: 11px; text-transform: uppercase; font-weight: bold; color: #28563C; letter-spacing: 1px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 24px; }
        th { background: #28563C; color: #fff; padding: 10px; text-align: left; font-size: 13px; }
        .totals-section { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .totals-table { width: 300px; }
        .totals-table td { padding: 6px 12px; }
        .grand-total { font-size: 18px; font-weight: 800; color: #28563C; border-top: 2px solid #28563C; }
        .footer { border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center; font-size: 12px; color: #718096; }
        @media print { .no-print { display: none !important; } }
      </style>
    </head>
    <body>
      <div class="invoice-box">
        <div class="no-print" style="text-align: right; margin-bottom: 20px;">
          <button onclick="window.print()" style="padding: 10px 20px; background: #28563C; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
            🖨️ Print Invoice / Save as PDF
          </button>
        </div>

        <div class="header">
          <div>
            <div class="brand-name">KAMADHENU GOUSHALA</div>
            <div class="brand-sub">Indigenous Cow Protection &amp; Vedic Sanctuary</div>
            <div class="brand-sub">Bengaluru, Karnataka, India • info@kamadhenugoushala.org</div>
          </div>
          <div class="invoice-title">
            <h2>TAX INVOICE</h2>
            <div style="font-size: 13px; color: #718096; margin-top: 4px;">Ref: <strong>${orderData.order_number || ("ORD-" + orderData.id)}</strong></div>
            <div style="font-size: 13px; color: #718096;">Date: ${new Date(orderData.created_at).toLocaleDateString()}</div>
          </div>
        </div>

        <div class="info-grid">
          <div class="info-col">
            <div class="info-title">CUSTOMER DETAILS</div>
            <div style="font-weight: bold; font-size: 15px;">${orderData.customer_name || "Guest Devotee"}</div>
            <div>Email: ${orderData.customer_email || "—"}</div>
            <div>Phone: ${orderData.customer_phone || "—"}</div>
          </div>
          <div class="info-col">
            <div class="info-title">SHIPPING &amp; PAYMENT</div>
            <div><strong>Address:</strong> ${orderData.shipping_address || "Standard Delivery"}</div>
            <div style="margin-top: 6px;">Payment Status: <strong>${orderData.payment_status || "Pending"}</strong> (${orderData.payment_method || "UPI / Direct"})</div>
            <div>Order Status: <strong>${orderData.order_status || "Processing"}</strong></div>
          </div>
        </div>

        <table>
          <thead>
            <tr>
              <th style="width: 50px; text-align: center;">#</th>
              <th>Product / Item Description</th>
              <th style="width: 70px; text-align: center;">Qty</th>
              <th style="width: 120px; text-align: right;">Unit Price</th>
              <th style="width: 130px; text-align: right;">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            ${itemsRows}
          </tbody>
        </table>

        <div class="totals-section">
          <table class="totals-table">
            <tr>
              <td>Subtotal:</td>
              <td style="text-align: right; font-weight: 600;">₹${parseFloat(orderData.total_amount || 0).toLocaleString("en-IN", {minimumFractionDigits: 2})}</td>
            </tr>
            <tr>
              <td>Shipping:</td>
              <td style="text-align: right; color: #28563C; font-weight: 600;">FREE</td>
            </tr>
            <tr class="grand-total">
              <td>Total Paid:</td>
              <td style="text-align: right;">₹${parseFloat(orderData.total_amount || 0).toLocaleString("en-IN", {minimumFractionDigits: 2})}</td>
            </tr>
          </table>
        </div>

        <div class="footer">
          <p>Thank you for supporting indigenous cow protection and sustainable organic farming!</p>
          <p>Kamadhenu Goushala Seva Trust • All proceeds go towards cow feeding and medical care.</p>
        </div>
      </div>
    </body>
    </html>
  `);
  printWin.document.close();
}

// 1. Edit Order Function
function openEditOrderModal(orderData) {
  document.getElementById("orderModalTitle").textContent = "Edit Order: " + (orderData.order_number || ("ORD-" + orderData.id));
  document.getElementById("orderId").value = orderData.id || "";
  document.getElementById("orderCustomerName").value = orderData.customer_name || "";
  document.getElementById("orderCustomerEmail").value = orderData.customer_email || "";
  document.getElementById("orderCustomerPhone").value = orderData.customer_phone || "";
  document.getElementById("orderTotalAmount").value = parseFloat(orderData.total_amount || 0).toFixed(2);
  document.getElementById("orderPaymentStatus").value = orderData.payment_status || "Pending";
  document.getElementById("orderPaymentMethod").value = orderData.payment_method || "UPI / Cash on Delivery / Direct";
  document.getElementById("orderOrderStatus").value = orderData.order_status || "Processing";
  document.getElementById("orderNotes").value = orderData.notes || "";
  document.getElementById("orderShippingAddress").value = orderData.shipping_address || "";

  // Hide view modal if open
  const viewModalEl = document.getElementById("orderViewModal");
  const viewModal = bootstrap.Modal.getInstance(viewModalEl);
  if (viewModal) viewModal.hide();

  const editModal = new bootstrap.Modal(document.getElementById("orderModal"));
  editModal.show();
}

// Wire up Edit buttons
document.querySelectorAll(".edit-order-btn").forEach(btn => {
  btn.addEventListener("click", function(e) {
    e.preventDefault();
    const orderData = JSON.parse(this.dataset.order);
    openEditOrderModal(orderData);
  });
});

document.getElementById("orderViewEditBtn")?.addEventListener("click", function() {
  if (currentModalOrder) {
    openEditOrderModal(currentModalOrder);
  }
});

// Wire up Print buttons
document.querySelectorAll(".print-receipt-btn").forEach(btn => {
  btn.addEventListener("click", async function(e) {
    e.preventDefault();
    const orderData = JSON.parse(this.dataset.order);
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=get_order&id=" + orderData.id);
      const data = await res.json();
      const items = (data.success && data.data && data.data.items) ? data.data.items : [];
      printOrderReceipt(orderData, items);
    } catch {
      printOrderReceipt(orderData, []);
    }
  });
});

document.getElementById("orderViewPrintBtn")?.addEventListener("click", function() {
  if (currentModalOrder) {
    printOrderReceipt(currentModalOrder, currentModalItems);
  }
});

// 2. View Order Details with Items
document.querySelectorAll(".view-order-btn").forEach(btn => {
  btn.addEventListener("click", async function(e) {
    e.preventDefault();
    const o = JSON.parse(this.dataset.order);
    currentModalOrder = o;
    currentModalItems = [];

    document.getElementById("orderViewTitle").textContent = "Order: " + (o.order_number || o.id);
    
    const renderModal = (orderData, items) => {
      let itemsListHtml = "";
      if (items && items.length > 0) {
        itemsListHtml = `
          <div class="mt-3">
            <h6 class="fw-bold text-forest mb-2"><i class="bi bi-box-seam me-1"></i> Purchased Products (${items.length})</h6>
            <div class="table-responsive border rounded">
              <table class="table table-sm mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Product</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  ${items.map(it => `
                    <tr>
                      <td class="fw-semibold">${it.product_name || "Product"}</td>
                      <td class="text-center">${it.quantity || 1}</td>
                      <td class="text-end">₹${parseFloat(it.unit_price || 0).toLocaleString("en-IN", {minimumFractionDigits: 2})}</td>
                      <td class="text-end fw-bold text-forest">₹${parseFloat(it.subtotal || 0).toLocaleString("en-IN", {minimumFractionDigits: 2})}</td>
                    </tr>
                  `).join("")}
                </tbody>
              </table>
            </div>
          </div>
        `;
      }

      const osClass = (orderData.order_status === "Delivered") ? "bg-success" : 
                      (orderData.order_status === "Shipped") ? "bg-primary" : 
                      (orderData.order_status === "Cancelled") ? "bg-danger" : 
                      (orderData.order_status === "Confirmed") ? "bg-info text-dark" : "bg-warning text-dark";

      const psClass = (orderData.payment_status === "Completed" || orderData.payment_status === "Paid") ? "bg-success" :
                      (orderData.payment_status === "Failed") ? "bg-danger" : "bg-warning text-dark";

      document.getElementById("orderViewBody").innerHTML = `
        <div class="p-3 bg-light rounded-3 mb-3 border d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <div class="fw-bold fs-4 text-forest">₹${parseFloat(orderData.total_amount || 0).toLocaleString("en-IN", {minimumFractionDigits: 2})}</div>
            <small class="text-muted">Payment: <strong class="badge ${psClass} ms-1">${orderData.payment_status || "Pending"}</strong> via ${orderData.payment_method || "UPI / Direct"}</small>
          </div>
          <div>
            <span class="badge ${osClass} fs-6 px-3 py-2">${orderData.order_status || "Processing"}</span>
          </div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-sm-6">
            <div class="p-3 bg-light rounded border h-100">
              <label class="text-muted small d-block mb-1 fw-bold">Customer Details</label>
              <div class="fw-bold text-dark fs-6">${orderData.customer_name || "—"}</div>
              <div class="small text-forest"><i class="bi bi-envelope me-1"></i>${orderData.customer_email || ""}</div>
              <div class="small text-muted"><i class="bi bi-telephone me-1"></i>${orderData.customer_phone || "Not provided"}</div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="p-3 bg-light rounded border h-100">
              <label class="text-muted small d-block mb-1 fw-bold">Order Information</label>
              <div><strong>Date:</strong> <span class="small">${new Date(orderData.created_at).toLocaleString()}</span></div>
              <div><strong>Ref:</strong> <code class="text-forest small">${orderData.order_number || ("ORD-" + orderData.id)}</code></div>
              ${orderData.notes ? `<div class="mt-1 small text-muted"><strong>Note:</strong> ${orderData.notes}</div>` : ""}
            </div>
          </div>
        </div>
        <div class="mb-3">
          <label class="text-muted small d-block mb-1 fw-bold">Delivery Shipping Address</label>
          <div class="p-3 bg-light rounded border small">${orderData.shipping_address || "No shipping address provided."}</div>
        </div>
        ${itemsListHtml}
      `;
    };

    renderModal(o, []);
    const modalEl = document.getElementById("orderViewModal");
    const viewModal = new bootstrap.Modal(modalEl);
    viewModal.show();

    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=get_order&id=" + o.id);
      const data = await res.json();
      if (data.success && data.data) {
        currentModalOrder = data.data;
        currentModalItems = data.data.items || [];
        renderModal(data.data, currentModalItems);
      }
    } catch(e) {}
  });
});

// 3. Change Order Status Option from Gear Dropdown
document.querySelectorAll(".order-status-item").forEach(item => {
  item.addEventListener("click", async function(e) {
    e.preventDefault();
    const id = this.dataset.id;
    const status = this.dataset.status;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=toggle_status&entity=orders&id=" + id + "&status=" + encodeURIComponent(status));
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message || "Order status updated.", "success");
        setTimeout(() => location.reload(), 600);
      } else {
        showAdminToast(result.message || "Status change failed.", "error");
      }
    } catch { showAdminToast("Status change failed.", "error"); }
  });
});

// 4. Change Payment Status Option from Gear Dropdown
document.querySelectorAll(".order-payment-item").forEach(item => {
  item.addEventListener("click", async function(e) {
    e.preventDefault();
    const id = this.dataset.id;
    const paymentStatus = this.dataset.paymentStatus;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=toggle_status&entity=orders&field=payment_status&id=" + id + "&status=" + encodeURIComponent(paymentStatus));
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message || "Payment status updated.", "success");
        setTimeout(() => location.reload(), 600);
      } else {
        showAdminToast(result.message || "Payment update failed.", "error");
      }
    } catch { showAdminToast("Payment update failed.", "error"); }
  });
});

// 5. Save / Record Order Form Submit
const saveOrderBtn = document.getElementById("saveOrderBtn");
if (saveOrderBtn) {
  saveOrderBtn.addEventListener("click", async () => {
    const form = document.getElementById("orderForm");
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const fd = new FormData(form);
    const data = Object.fromEntries(fd.entries());
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      });
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message, "success");
        setTimeout(() => location.reload(), 600);
      } else {
        showAdminToast(result.message || "Failed to save order.", "error");
      }
    } catch {
      showAdminToast("Network error occurred.", "error");
    }
  });
}

// 6. Delete Order Action
document.querySelectorAll(".delete-order-btn").forEach(btn => {
  btn.addEventListener("click", async function(e) {
    e.preventDefault();
    if (!confirm("Are you sure you want to delete order " + this.dataset.num + "?")) return;
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
