// Shopping Cart Manager for Kamadhenu Goushala Organic Store

const Cart = {
  storageKey: 'kamadhenu_cart_v1',

  getItems() {
    try {
      const data = localStorage.getItem(this.storageKey);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      return [];
    }
  },

  saveItems(items) {
    localStorage.setItem(this.storageKey, JSON.stringify(items));
    this.updateCartBadge();
    this.renderCartModal();
    window.dispatchEvent(new CustomEvent('cart:updated', { detail: { items, count: this.getCount(), subtotal: this.getSubtotal() } }));
  },

  addItem(product, quantity = 1) {
    const items = this.getItems();
    const prodId = parseInt(product.id, 10);
    const existingIndex = items.findIndex(i => parseInt(i.id, 10) === prodId);

    if (existingIndex > -1) {
      items[existingIndex].quantity += quantity;
    } else {
      items.push({
        id: prodId,
        name: product.name,
        price: parseFloat(product.price) || 0,
        image: product.image || '',
        quantity: quantity
      });
    }

    this.saveItems(items);
    if (typeof showToast === 'function') {
      showToast(`Added "${product.name}" to cart! (Qty: ${quantity})`);
    }
  },

  removeItem(productId) {
    let items = this.getItems();
    const targetId = parseInt(productId, 10);
    items = items.filter(i => parseInt(i.id, 10) !== targetId);
    this.saveItems(items);
  },

  updateQuantity(productId, delta) {
    const items = this.getItems();
    const targetId = parseInt(productId, 10);
    const item = items.find(i => parseInt(i.id, 10) === targetId);
    if (item) {
      item.quantity += delta;
      if (item.quantity <= 0) {
        this.removeItem(productId);
        return;
      }
      this.saveItems(items);
    }
  },

  clear() {
    localStorage.removeItem(this.storageKey);
    this.updateCartBadge();
    this.renderCartModal();
    window.dispatchEvent(new CustomEvent('cart:updated', { detail: { items: [], count: 0, subtotal: 0 } }));
  },

  getCount() {
    const items = this.getItems();
    return items.reduce((sum, item) => sum + (parseInt(item.quantity, 10) || 0), 0);
  },

  getSubtotal() {
    const items = this.getItems();
    return items.reduce((sum, item) => sum + ((parseFloat(item.price) || 0) * (parseInt(item.quantity, 10) || 0)), 0);
  },

  updateCartBadge() {
    const badges = document.querySelectorAll('.cart-badge-count');
    const count = this.getCount();
    badges.forEach(b => {
      b.textContent = count;
      b.style.display = count > 0 ? 'inline-flex' : 'none';
    });
  },

  renderCartModal() {
    const container = document.getElementById('cartItemsContainer');
    const totalEl = document.getElementById('cartSubtotalAmount');
    if (!container || !totalEl) return;

    const items = this.getItems();

    if (items.length === 0) {
      container.innerHTML = `
        <div class="text-center py-5">
          <i class="bi bi-cart-x fs-1 text-muted"></i>
          <p class="mt-2 text-muted">Your cart is currently empty.</p>
          <a href="${typeof BASE_URL !== 'undefined' ? BASE_URL : ''}/products.php" class="btn btn-outline-gold btn-sm mt-2" data-bs-dismiss="modal">Explore Products</a>
        </div>
      `;
      totalEl.textContent = '₹0.00';
      const checkoutBtn = document.getElementById('cartCheckoutBtn');
      if (checkoutBtn) checkoutBtn.disabled = true;
      return;
    }

    const checkoutBtn = document.getElementById('cartCheckoutBtn');
    if (checkoutBtn) checkoutBtn.disabled = false;

    let html = '<div class="list-group list-group-flush">';
    items.forEach(item => {
      html += `
        <div class="list-group-item d-flex align-items-center gap-3 py-3">
          <img src="${item.image || 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?auto=format&fit=crop&w=150&q=80'}" alt="${item.name}" style="width: 55px; height: 55px; object-fit: cover; border-radius: 8px;">
          <div style="flex: 1;">
            <h6 class="mb-1 fw-bold">${item.name}</h6>
            <div class="text-forest fw-bold">₹${parseFloat(item.price).toFixed(2)}</div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="Cart.updateQuantity(${item.id}, -1)">-</button>
            <span class="fw-bold px-1">${item.quantity}</span>
            <button class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="Cart.updateQuantity(${item.id}, 1)">+</button>
          </div>
          <button class="btn btn-sm text-danger" onclick="Cart.removeItem(${item.id})" title="Remove item">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      `;
    });
    html += '</div>';

    container.innerHTML = html;
    totalEl.textContent = `₹${this.getSubtotal().toFixed(2)}`;
  }
};

// Global click handler for Add-To-Cart buttons
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.add-to-cart-btn');
  if (!btn) return;

  e.preventDefault();

  const id = btn.dataset.id;
  const name = btn.dataset.name;
  const price = btn.dataset.price;
  const image = btn.dataset.image;

  if (!id || !name) return;

  Cart.addItem({
    id: parseInt(id, 10),
    name: name,
    price: parseFloat(price) || 0,
    image: image || ''
  }, 1);

  // Visual feedback on button
  const originalHtml = btn.innerHTML;
  btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Added!';
  btn.classList.add('btn-success');
  btn.classList.remove('btn-gold');
  setTimeout(() => {
    btn.innerHTML = originalHtml;
    btn.classList.remove('btn-success');
    btn.classList.add('btn-gold');
  }, 1500);

  // If checkoutSection is on page, smooth scroll towards it or update summary
  const checkoutSec = document.getElementById('checkoutSection');
  if (checkoutSec) {
    checkoutSec.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
});

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () => {
  Cart.updateCartBadge();
  Cart.renderCartModal();

  const cartModalEl = document.getElementById('cartModal');
  if (cartModalEl) {
    cartModalEl.addEventListener('show.bs.modal', () => {
      Cart.renderCartModal();
    });
  }
});

window.Cart = Cart;
