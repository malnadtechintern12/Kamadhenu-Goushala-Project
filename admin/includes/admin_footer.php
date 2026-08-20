    </div><!-- /.admin-content -->
  </div><!-- /.admin-main -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const BASE_URL = '<?= BASE_URL ?>';

    // Sidebar toggle for mobile
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
      document.getElementById('adminSidebar').classList.toggle('show');
    });

    // Close sidebar on overlay click (mobile)
    document.addEventListener('click', (e) => {
      const sidebar = document.getElementById('adminSidebar');
      const toggle = document.getElementById('sidebarToggle');
      if (sidebar.classList.contains('show') && !sidebar.contains(e.target) && e.target !== toggle) {
        sidebar.classList.remove('show');
      }
    });

    // Toast helper
    function showAdminToast(message, type = 'success') {
      let container = document.getElementById('admin-toast-container');
      if (!container) {
        container = document.createElement('div');
        container.id = 'admin-toast-container';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
        document.body.appendChild(container);
      }
      const toast = document.createElement('div');
      toast.className = 'alert alert-' + (type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info') + ' shadow-sm border-0 mb-2';
      toast.style.cssText = 'min-width:300px;animation:slideIn 0.3s ease;';
      toast.innerHTML = '<i class="bi ' + (type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill') + ' me-2"></i>' + message;
      container.appendChild(toast);
      setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 4000);
    }
  </script>
  <?= $admin_extra_js ?? '' ?>
</body>
</html>
