<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'newsletter';
$admin_title = 'Newsletter Subscribers';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$subs = $pdo->query("SELECT * FROM newsletter_subscribers ORDER BY created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Newsletter Subscribers (<?= count($subs) ?>)</h5>
    <p class="text-muted small mb-0">Manage community mailing list for monthly sanctuary updates and announcements.</p>
  </div>
  <button class="btn btn-gold" data-bs-toggle="modal" data-bs-target="#subscriberModal">
    <i class="bi bi-plus-lg me-1"></i> Add Subscriber
  </button>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Email Address</th>
        <th>Subscription Status</th>
        <th>Subscribed On</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($subs as $s): ?>
      <tr>
        <td class="fw-semibold fs-6"><i class="bi bi-envelope-at me-2 text-warning"></i><?= e($s['email']) ?></td>
        <td>
          <span class="badge <?= $s['status']==='Active'?'badge-active':'badge-inactive' ?>">
            <?= e($s['status']) ?>
          </span>
        </td>
        <td><small class="text-muted"><?= formatDate($s['created_at']) ?></small></td>
        <td>
          <div class="d-flex gap-1">
            <?php if ($s['status'] === 'Active'): ?>
              <button class="btn btn-sm btn-outline-warning toggle-sub-btn" data-id="<?= $s['id'] ?>" data-status="Inactive" title="Unsubscribe">
                <i class="bi bi-pause-circle"></i>
              </button>
            <?php else: ?>
              <button class="btn btn-sm btn-outline-success toggle-sub-btn" data-id="<?= $s['id'] ?>" data-status="Active" title="Reactivate">
                <i class="bi bi-play-circle"></i>
              </button>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-danger delete-sub-btn" data-id="<?= $s['id'] ?>" data-email="<?= e($s['email']) ?>" title="Remove Email">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($subs)): ?>
        <tr><td colspan="4" class="text-center py-4 text-muted">No subscribers found. Click "Add Subscriber" to add one.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Add Subscriber Modal -->
<div class="modal fade" id="subscriberModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Newsletter Subscriber</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form id="subscriberForm">
          <div class="mb-3">
            <label class="form-label fw-semibold">Email Address *</label>
            <input type="email" name="email" id="subEmail" class="form-control" placeholder="devotee@example.com" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-gold px-4" id="saveSubBtn">Add Subscriber</button>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
document.getElementById("saveSubBtn").addEventListener("click", async () => {
  const email = document.getElementById("subEmail").value;
  if (!email) { alert("Please enter email."); return; }
  try {
    const res = await fetch(BASE_URL + "/api/submit_newsletter.php", {
      method: "POST", headers: {"Content-Type":"application/json"}, body: JSON.stringify({ email })
    });
    const result = await res.json();
    if (result.success) {
      showAdminToast(result.message, "success");
      setTimeout(() => location.reload(), 600);
    } else {
      showAdminToast(result.message || "Failed to add subscriber.", "error");
    }
  } catch { showAdminToast("Network error.", "error"); }
});

document.querySelectorAll(".toggle-sub-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=toggle_status&entity=newsletter_subscribers&id=" + this.dataset.id + "&status=" + this.dataset.status);
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message, "success");
        setTimeout(() => location.reload(), 600);
      } else {
        showAdminToast(result.message || "Status change failed.", "error");
      }
    } catch { showAdminToast("Network error.", "error"); }
  });
});

document.querySelectorAll(".delete-sub-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete subscriber \'" + this.dataset.email + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=newsletter_subscribers&id=" + this.dataset.id);
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
