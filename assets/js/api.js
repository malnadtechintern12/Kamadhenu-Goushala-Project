// Centralized API Client for Kamadhenu Goushala (PHP/XAMPP Version)
// BASE_URL is injected by footer.php before this script loads

const API = {
  get baseUrl() {
    return (typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/api';
  },

  async request(endpoint, options = {}) {
    const defaultHeaders = {
      'Content-Type': 'application/json'
    };

    if (options.body instanceof FormData) {
      delete defaultHeaders['Content-Type'];
    }

    const config = {
      ...options,
      headers: {
        ...defaultHeaders,
        ...options.headers
      }
    };

    try {
      const response = await fetch(`${this.baseUrl}${endpoint}`, config);
      const data = await response.json().catch(() => null);

      if (!response.ok) {
        const errorMsg = (data && data.message) || `Request failed with status ${response.status}`;
        throw new Error(errorMsg);
      }

      return data;
    } catch (error) {
      console.error(`API Error on ${endpoint}:`, error.message);
      throw error;
    }
  },

  get(endpoint, params = {}) {
    const query = new URLSearchParams(params).toString();
    const url = query ? `${endpoint}?${query}` : endpoint;
    return this.request(url, { method: 'GET' });
  },

  post(endpoint, body) {
    return this.request(endpoint, {
      method: 'POST',
      body: body instanceof FormData ? body : JSON.stringify(body)
    });
  },

  put(endpoint, body) {
    return this.request(endpoint, {
      method: 'PUT',
      body: body instanceof FormData ? body : JSON.stringify(body)
    });
  },

  delete(endpoint) {
    return this.request(endpoint, { method: 'DELETE' });
  }
};

// Global Toast Notification Helper
function showToast(message, type = 'success', duration = 4500) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `custom-toast toast-${type}`;

  let icon = 'bi-check-circle-fill';
  if (type === 'error') icon = 'bi-exclamation-octagon-fill';
  if (type === 'info') icon = 'bi-info-circle-fill';

  toast.innerHTML = `
    <i class="bi ${icon} fs-5"></i>
    <div style="flex: 1;">${message}</div>
    <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 0.75rem;" aria-label="Close"></button>
  `;

  const closeBtn = toast.querySelector('.btn-close');
  closeBtn.addEventListener('click', () => toast.remove());

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

window.API = API;
window.showToast = showToast;
