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
  },

  addItem(product, quantity = 1) {
    const items = this.getItems();
    const existingIndex = items.findIndex(i => i.id === product.id);

    if (existingIndex > -1) {
      items[existingIndex].quantity += quantity;
    } else {
      items.push({
        id: product.id,
        name: product.name,
        price: parseFloat(product.price),
        image: product.image,
        quantity: quantity
      });
    }

    this.saveItems(items);
    if (typeof showToast === 'function') {
      showToast(`Added "${product.name}" to cart!`);
    }
  },

  removeItem(productId) {
    let items = this.getItems();
    items = items.filter(i => i.id !== productId);
    this.saveItems(items);
    this.renderCartModal();
  },

  updateQuantity(productId, delta) {
    const items = this.getItems();
    const item = items.find(i => i.id === productId);
    if (item) {
      item.quantity += delta;
      if (item.quantity <= 0) {
        this.removeItem(productId);
        return;
      }
      this.saveItems(items);
      this.renderCartModal();
    }
  },

  clear() {
    localStorage.removeItem(this.storageKey);
    this.updateCartBadge();
  },

  getCount() {
    const items = this.getItems();
    return items.reduce((sum, item) => sum + item.quantity, 0);
  },

  getSubtotal() {
    const items = this.getItems();
    return items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  },

  updateCartBadge() {
    const badges = document.querySelectorAll('.cart-badge-count');
    const count = this.getCount();
    badges.forEach(b => {
      b.textContent = count;
      b.style.display = count > 0 ? 'flex' : 'none';
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
          <a href="/products.html" class="btn btn-outline-gold btn-sm mt-2">Explore Products</a>
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
            <div class="text-forest fw-bold">₹${item.price.toFixed(2)}</div>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="Cart.updateQuantity(${item.id}, -1)">-</button>
            <span class="fw-bold px-1">${item.quantity}</span>
            <button class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="Cart.updateQuantity(${item.id}, 1)">+</button>
          </div>
          <button class="btn btn-sm text-danger" onclick="Cart.removeItem(${item.id})">
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

document.addEventListener('DOMContentLoaded', () => {
  Cart.updateCartBadge();
});

window.Cart = Cart;
