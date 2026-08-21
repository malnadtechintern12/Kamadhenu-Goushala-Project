<?php
$page_title = 'Organic Products'; 
$page_desc = 'Shop organic cow products — vermicompost, Panchagavya, diyas, and more from Kamadhenu Goushala.'; 
$active_nav = 'products';
include __DIR__ . '/includes/header.php'; 
include __DIR__ . '/includes/navbar.php';
$base = BASE_URL;
$products = getActiveProducts();
try { 
  $prodCats = $pdo->query("SELECT * FROM product_categories WHERE status='active' ORDER BY name")->fetchAll(); 
} catch (Exception $e) { 
  $prodCats = []; 
}
?>

  <!-- Early inline JS functions to guarantee immediate responsiveness on button click -->
  <script>
    function changeCardQty(prodId, delta) {
      const input = document.getElementById('qty_' + prodId);
      if (!input) return;
      let val = parseInt(input.value, 10) || 1;
      val = Math.max(1, val + delta);
      input.value = val;
    }

    function addToOrder(id, name, price, image, btn) {
      const qtyInput = document.getElementById('qty_' + id);
      const quantity = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;

      // 1. Save to LocalStorage / Cart
      try {
        let items = [];
        const raw = localStorage.getItem('kamadhenu_cart_v1');
        if (raw) items = JSON.parse(raw);
        
        const existingIdx = items.findIndex(i => parseInt(i.id, 10) === parseInt(id, 10));
        if (existingIdx > -1) {
          items[existingIdx].quantity += quantity;
        } else {
          items.push({
            id: parseInt(id, 10),
            name: name,
            price: parseFloat(price) || 0,
            image: image || '',
            quantity: quantity
          });
        }
        localStorage.setItem('kamadhenu_cart_v1', JSON.stringify(items));
      } catch (err) {
        console.error('Cart save error:', err);
      }

      // 2. Trigger Cart object update if loaded
      if (typeof Cart !== 'undefined') {
        Cart.updateCartBadge();
        Cart.renderCartModal();
      }

      // 3. Trigger custom event
      window.dispatchEvent(new CustomEvent('cart:updated'));

      // 4. Visual button feedback
      if (btn) {
        const origHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Added (' + quantity + ')';
        btn.classList.remove('btn-gold');
        btn.classList.add('btn-success');
        setTimeout(() => {
          btn.innerHTML = origHtml;
          btn.classList.remove('btn-success');
          btn.classList.add('btn-gold');
        }, 1800);
      }

      // 5. Toast Notification
      if (typeof showToast === 'function') {
        showToast('Added ' + quantity + 'x "' + name + '" to your order! 🌿', 'success');
      }

      // 6. Update summary & scroll to checkout section
      if (typeof renderCheckoutSummary === 'function') {
        renderCheckoutSummary();
      }

      const checkoutSec = document.getElementById('checkoutSection');
      if (checkoutSec) {
        checkoutSec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }
  </script>

  <!-- Page Banner -->
  <section class="page-hero">
    <div class="container">
      <div class="hero-badge"><i class="bi bi-bag-check"></i> Natural &amp; Organic</div>
      <h1 class="hero-title">Organic <span>Products</span></h1>
      <p class="hero-subtitle">100% natural products crafted from sacred cow resources — vermicompost, Panchagavya, diyas, and herbal formulations.</p>
    </div>
  </section>

  <!-- Products Section -->
  <section class="section-padding">
    <div class="container">
      
      <!-- Category Filter Tabs -->
      <div class="d-flex flex-wrap gap-2 mb-5 justify-content-center">
        <button class="btn btn-sm btn-forest active px-4 py-2 rounded-pill fw-semibold" data-prod-filter="all">All Products</button>
        <?php foreach ($prodCats as $c): ?>
          <button class="btn btn-sm btn-outline-forest px-4 py-2 rounded-pill fw-semibold" data-prod-filter="<?= $c['id'] ?>"><?= e($c['name']) ?></button>
        <?php endforeach; ?>
      </div>

      <!-- Products Grid -->
      <div class="row g-4 mb-5" id="productsGrid">
        <?php if (empty($products)): ?>
          <div class="col-12 text-center text-muted fs-5 py-5">
            <i class="bi bi-box2 fs-1 d-block mb-3 text-muted"></i>
            No products available right now.
          </div>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
          <div class="col-md-6 col-lg-4 prod-item" data-cat="<?= $p['category_id'] ?>">
            <div class="product-card h-100 shadow-sm rounded-4 border overflow-hidden d-flex flex-column bg-white">
              <div class="product-img-box position-relative" style="height: 220px; overflow: hidden; background: #f8f9fa;">
                <img src="<?= e($p['image'] ?: 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=600&q=80') ?>" 
                     alt="<?= e($p['name']) ?>" 
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;">
                <?php if ($p['stock'] > 0): ?>
                  <span class="badge bg-success position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill shadow-sm">In Stock (<?= $p['stock'] ?>)</span>
                <?php else: ?>
                  <span class="badge bg-danger position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill shadow-sm">Sold Out</span>
                <?php endif; ?>
              </div>
              <div class="product-body p-4 d-flex flex-column flex-grow-1">
                <span class="small text-forest fw-bold mb-1 d-inline-block"><?= e($p['category_name'] ?? 'Goushala Organic') ?></span>
                <h5 class="fw-bold mb-2 text-forest"><?= e($p['name']) ?></h5>
                <p class="small text-muted mb-4" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:38px;">
                  <?= e($p['description'] ?? 'Pure traditional Ayurvedic preparation from indigenous desi cow resources.') ?>
                </p>
                <div class="d-flex align-items-center justify-content-between mt-auto pt-3 border-top flex-wrap gap-2">
                  <div>
                    <span class="text-muted small d-block">Price:</span>
                    <div class="fs-4 fw-bold text-forest">₹<?= number_format((float)$p['price'], 2) ?></div>
                  </div>
                  <?php if ($p['stock'] > 0): ?>
                  <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 90px;">
                      <button class="btn btn-outline-secondary px-2" type="button" onclick="changeCardQty(<?= $p['id'] ?>, -1)">-</button>
                      <input type="number" id="qty_<?= $p['id'] ?>" class="form-control text-center px-1 fw-bold" value="1" min="1" max="<?= $p['stock'] ?>">
                      <button class="btn btn-outline-secondary px-2" type="button" onclick="changeCardQty(<?= $p['id'] ?>, 1)">+</button>
                    </div>
                    <button type="button"
                            class="btn btn-gold px-3 py-2 fw-semibold add-to-cart-btn shadow-sm"
                            data-id="<?= $p['id'] ?>" 
                            data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"
                            data-price="<?= $p['price'] ?>" 
                            data-image="<?= htmlspecialchars($p['image'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                            onclick="addToOrder(<?= $p['id'] ?>, '<?= addslashes(htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8')) ?>', <?= (float)$p['price'] ?>, '<?= addslashes(htmlspecialchars($p['image'] ?? '', ENT_QUOTES, 'UTF-8')) ?>', this)"
                            title="Add to order">
                      <i class="bi bi-cart-plus-fill me-1"></i> Add to Order
                    </button>
                  </div>
                  <?php else: ?>
                  <button class="btn btn-secondary btn-sm px-3 py-2" disabled>Out of Stock</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Checkout & Direct Order Section -->
      <div class="p-4 p-md-5 bg-white rounded-4 shadow-lg border mt-5" id="checkoutSection">
        <div class="row g-5">
          
          <!-- Order Summary in Checkout -->
          <div class="col-lg-5 order-lg-2">
            <div class="p-4 bg-light rounded-4 border">
              <h5 class="fw-bold text-forest mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-basket-fill text-warning me-2"></i> Your Order Items</span>
                <span class="badge bg-forest rounded-pill cart-badge-count" style="font-size: 11px;">0</span>
              </h5>
              <div id="checkoutOrderItems" class="mb-3" style="max-height: 320px; overflow-y: auto;">
                <p class="text-muted small py-4 text-center">Your order list is empty. Click <strong>"Add to Order"</strong> on any product above.</p>
              </div>
              <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <span class="fw-bold fs-5">Total Amount:</span>
                <span class="fw-bold fs-3 text-forest" id="checkoutTotalDisplay">₹0.00</span>
              </div>
              <div class="mt-3 p-2 bg-white rounded-3 border text-center small text-muted">
                <i class="bi bi-shield-check text-success me-1"></i> 100% Proceeds Support Cow Protection &amp; Fodder
              </div>
            </div>
          </div>

          <!-- Customer & Shipping Form -->
          <div class="col-lg-7 order-lg-1">
            <div class="d-flex align-items-center gap-2 mb-3">
              <div class="p-2 rounded-circle bg-warning bg-opacity-25 text-warning">
                <i class="bi bi-truck fs-4 text-forest"></i>
              </div>
              <h4 class="fw-bold text-forest mb-0">Delivery &amp; Customer Details</h4>
            </div>
            <p class="text-muted small mb-4">Please fill in your shipping information to place your order directly. Our team will verify and dispatch your organic essentials.</p>

            <form id="checkoutForm">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Customer Full Name <span class="text-danger">*</span></label>
                  <input type="text" id="custName" class="form-control py-2" placeholder="e.g. Ramesh Sharma" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                  <input type="email" id="custEmail" class="form-control py-2" placeholder="ramesh@example.com" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Contact Phone / WhatsApp <span class="text-danger">*</span></label>
                  <input type="tel" id="custPhone" class="form-control py-2" placeholder="+91 98450 12345" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-semibold">Payment Method</label>
                  <select id="custPaymentMethod" class="form-select py-2">
                    <option value="Cash on Delivery">Cash on Delivery (COD)</option>
                    <option value="UPI on Delivery">UPI on Delivery</option>
                    <option value="Direct Bank / UPI Transfer">Direct UPI Transfer (kamadhenu@sbi)</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Complete Delivery Address <span class="text-danger">*</span></label>
                  <textarea id="custAddress" class="form-control" rows="3" placeholder="Door No / Building, Street, Landmark, City, State, PIN Code" required></textarea>
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Special Delivery Instructions / Notes</label>
                  <input type="text" id="custNotes" class="form-control py-2" placeholder="e.g. Please call before delivery or deliver in afternoon">
                </div>
                <div class="col-12 mt-4">
                  <button type="submit" id="btnPlaceOrder" class="btn btn-gold py-3 px-5 w-100 fs-5 fw-bold shadow-sm">
                    <i class="bi bi-bag-check-fill me-2"></i> PLACE ORDER NOW
                  </button>
                </div>
              </div>
            </form>
          </div>

        </div>
      </div>

    </div>
  </section>

  <!-- Order Confirmation Modal -->
  <div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4 text-center p-4">
        <div class="my-3">
          <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle bg-success bg-opacity-25 text-success">
            <i class="bi bi-check-circle-fill fs-1"></i>
          </div>
        </div>
        <h4 class="fw-bold text-forest mb-1">Order Placed Successfully!</h4>
        <p class="text-muted small mb-3">Thank you for supporting Kamadhenu Goushala with your sacred purchase. Our team will contact you to confirm delivery.</p>
        <div class="p-3 bg-light rounded-3 border mb-3">
          <div class="small text-muted">Order Reference Number:</div>
          <div class="fs-4 fw-bold text-forest" id="modalOrderNumber">ORD-20260821-12345</div>
        </div>
        <button type="button" class="btn btn-gold w-100 py-2 fw-semibold" data-bs-dismiss="modal" onclick="window.location.reload()">
          <i class="bi bi-bag-plus me-1"></i> Continue Shopping
        </button>
      </div>
    </div>
  </div>

<?php
$extra_js = '
<script>
// 1. Render Checkout Summary Function
function renderCheckoutSummary() {
  let items = [];
  try {
    const raw = localStorage.getItem("kamadhenu_cart_v1");
    if (raw) items = JSON.parse(raw);
  } catch (e) {
    items = [];
  }

  const container = document.getElementById("checkoutOrderItems");
  const totalDisplay = document.getElementById("checkoutTotalDisplay");

  if (!container || !totalDisplay) return;

  if (!items || items.length === 0) {
    container.innerHTML = `<p class="text-muted small py-4 text-center">Your order list is empty. Click <strong>"Add to Order"</strong> on any product above.</p>`;
    totalDisplay.textContent = "₹0.00";
    return;
  }

  let subtotal = 0;
  let html = \'<div class="list-group list-group-flush mb-2">\';
  items.forEach(i => {
    const itemPrice = parseFloat(i.price) || 0;
    const itemQty = parseInt(i.quantity, 10) || 1;
    const lineTotal = itemPrice * itemQty;
    subtotal += lineTotal;

    html += `
      <div class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
        <div style="flex: 1; padding-right: 10px;">
          <div class="fw-bold small text-forest">${i.name}</div>
          <small class="text-muted">₹${itemPrice.toFixed(2)} x ${itemQty}</small>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="Cart.updateQuantity(${i.id}, -1)">-</button>
          <span class="fw-bold px-1 small">${itemQty}</span>
          <button type="button" class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="Cart.updateQuantity(${i.id}, 1)">+</button>
          <button type="button" class="btn btn-sm text-danger p-0 ms-1" onclick="Cart.removeItem(${i.id})" title="Remove item">
            <i class="bi bi-trash"></i>
          </button>
        </div>
        <div class="fw-bold text-forest ms-3">₹${lineTotal.toFixed(2)}</div>
      </div>
    `;
  });
  html += \'</div>\';

  container.innerHTML = html;
  totalDisplay.textContent = "₹" + subtotal.toFixed(2);
}

document.addEventListener("DOMContentLoaded", () => {
  // Category Filter
  document.querySelectorAll("[data-prod-filter]").forEach(btn => {
    btn.addEventListener("click", function() {
      document.querySelectorAll("[data-prod-filter]").forEach(b => { 
        b.classList.remove("active","btn-forest"); 
        b.classList.add("btn-outline-forest"); 
      });
      this.classList.add("active","btn-forest"); 
      this.classList.remove("btn-outline-forest");
      const f = this.dataset.prodFilter;
      document.querySelectorAll(".prod-item").forEach(i => { 
        i.style.display = (f === "all" || i.dataset.cat === f) ? "" : "none"; 
      });
    });
  });

  // Initial Summary Render
  renderCheckoutSummary();

  // Listen for Cart Updates
  window.addEventListener("cart:updated", renderCheckoutSummary);

  // Form Submit Handler
  const checkoutForm = document.getElementById("checkoutForm");
  const placeOrderBtn = document.getElementById("btnPlaceOrder");

  if (checkoutForm) {
    checkoutForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      let cartItems = [];
      try {
        const raw = localStorage.getItem("kamadhenu_cart_v1");
        if (raw) cartItems = JSON.parse(raw);
      } catch (err) {
        cartItems = [];
      }

      if (!cartItems || cartItems.length === 0) {
        if (typeof showToast === "function") {
          showToast("Please click \"Add to Order\" on at least one product before placing your order.", "error");
        } else {
          alert("Please add at least one product to your order.");
        }
        document.getElementById("productsGrid").scrollIntoView({ behavior: "smooth" });
        return;
      }

      const payload = {
        customer_name: document.getElementById("custName").value.trim(),
        customer_email: document.getElementById("custEmail").value.trim(),
        customer_phone: document.getElementById("custPhone").value.trim(),
        payment_method: document.getElementById("custPaymentMethod").value,
        shipping_address: document.getElementById("custAddress").value.trim(),
        notes: document.getElementById("custNotes").value.trim(),
        items: cartItems.map(item => ({
          product_id: item.id,
          quantity: item.quantity,
          name: item.name,
          price: item.price
        }))
      };

      try {
        if (placeOrderBtn) {
          placeOrderBtn.disabled = true;
          placeOrderBtn.innerHTML = \'<span class="spinner-border spinner-border-sm me-2"></span>Placing Order...\';
        }

        const res = await fetch(BASE_URL + "/api/submit_order.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data && data.success) {
          if (typeof Cart !== "undefined") {
            Cart.clear();
          } else {
            localStorage.removeItem("kamadhenu_cart_v1");
          }
          renderCheckoutSummary();
          checkoutForm.reset();

          const modalOrderNumber = document.getElementById("modalOrderNumber");
          if (modalOrderNumber) {
            modalOrderNumber.textContent = (data.data && data.data.order_number) || "ORD-SUCCESS";
          }

          const successModalEl = document.getElementById("orderSuccessModal");
          if (successModalEl) {
            const successModal = new bootstrap.Modal(successModalEl);
            successModal.show();
          } else {
            alert(data.message || "Order placed successfully!");
          }
        } else {
          if (typeof showToast === "function") {
            showToast(data.message || "Failed to process order.", "error");
          } else {
            alert(data.message || "Failed to process order.");
          }
        }
      } catch (err) {
        if (typeof showToast === "function") {
          showToast("Order placement failed. Please check connection and try again.", "error");
        } else {
          alert("Order placement failed.");
        }
      } finally {
        if (placeOrderBtn) {
          placeOrderBtn.disabled = false;
          placeOrderBtn.innerHTML = \'<i class="bi bi-bag-check-fill me-2"></i> PLACE ORDER NOW\';
        }
      }
    });
  }
});
</script>
';
include __DIR__ . '/includes/footer.php';
?>
