// Unified Admin Controller for Kamadhenu Goushala

const Admin = {
  currentUser: null,

  async init() {
    const isLoginPage = window.location.pathname.endsWith('/admin/index.html') || window.location.pathname.endsWith('/admin/');

    try {
      const res = await fetch('/api/auth/me');
      const data = await res.json().catch(() => null);

      if (data && data.authenticated && data.user) {
        this.currentUser = data.user;
        if (isLoginPage) {
          window.location.href = '/admin/dashboard.html';
          return;
        }
        this.populateAdminProfileUI();
        this.highlightActiveSidebar();
      } else {
        if (!isLoginPage) {
          window.location.href = '/admin/index.html';
          return;
        }
      }
    } catch (e) {
      if (!isLoginPage) window.location.href = '/admin/index.html';
    }

    this.setupLogoutButtons();
    this.setupSidebarToggle();
  },

  populateAdminProfileUI() {
    if (!this.currentUser) return;
    document.querySelectorAll('.admin-display-name').forEach(el => el.textContent = this.currentUser.name);
    document.querySelectorAll('.admin-display-email').forEach(el => el.textContent = this.currentUser.email);
    document.querySelectorAll('.admin-display-role').forEach(el => el.textContent = this.currentUser.role.toUpperCase());
  },

  highlightActiveSidebar() {
    const currentPath = window.location.pathname.toLowerCase();
    const links = document.querySelectorAll('.sidebar-link');
    links.forEach(l => {
      const href = l.getAttribute('href');
      if (href && currentPath.includes(href.toLowerCase().replace('./', '').replace('/', ''))) {
        l.classList.add('active');
      } else {
        l.classList.remove('active');
      }
    });
  },

  setupLogoutButtons() {
    document.querySelectorAll('.btn-admin-logout').forEach(btn => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        try {
          await fetch('/api/auth/logout', { method: 'POST' });
          window.location.href = '/admin/index.html';
        } catch (e) {
          window.location.href = '/admin/index.html';
        }
      });
    });
  },

  setupSidebarToggle() {
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const sidebar = document.querySelector('.admin-sidebar');
    if (toggleBtn && sidebar) {
      toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('show');
      });
    }
  },

  // Confirm delete dialog helper
  confirmDelete(title, message, onConfirm) {
    if (confirm(`${title}\n\n${message}`)) {
      onConfirm();
    }
  },

  // CSV Export Utility
  exportToCSV(filename, rows) {
    if (!rows || !rows.length) {
      showToast('No data available to export.', 'error');
      return;
    }
    const separator = ',';
    const keys = Object.keys(rows[0]);
    const csvContent =
      keys.join(separator) +
      '\n' +
      rows.map(row => {
        return keys.map(k => {
          let cell = row[k] === null || row[k] === undefined ? '' : row[k];
          cell = cell instanceof Date
            ? cell.toLocaleString()
            : cell.toString().replace(/"/g, '""');
          if (cell.search(/("|,|\n)/g) >= 0) {
            cell = `"${cell}"`;
          }
          return cell;
        }).join(separator);
      }).join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `${filename}_${Date.now()}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('CSV export downloaded successfully!');
  }
};

document.addEventListener('DOMContentLoaded', () => {
  Admin.init();
});

window.Admin = Admin;
