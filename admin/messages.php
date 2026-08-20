<?php
require_once __DIR__ . '/includes/auth_check.php';
$admin_page = 'messages';
$admin_title = 'Contact Messages';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

$messages = $pdo->query("SELECT * FROM contact_messages ORDER BY FIELD(status,'New','Read','Replied') ASC, created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h5 class="fw-bold mb-1">Inbound Contact Inquiries (<?= count($messages) ?>)</h5>
    <p class="text-muted small mb-0">Messages submitted from the website contact forms.</p>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-sm btn-outline-forest" onclick="location.reload()">
      <i class="bi bi-arrow-clockwise me-1"></i> Refresh Inbox
    </button>
  </div>
</div>

<div class="admin-table">
  <table class="table">
    <thead>
      <tr>
        <th>Sender</th>
        <th>Subject</th>
        <th>Message Preview</th>
        <th>Phone</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($messages as $m): ?>
      <tr class="<?= $m['status']==='New' ? 'table-warning' : '' ?>">
        <td>
          <div class="fw-semibold fs-6"><?= e($m['name']) ?></div>
          <a href="mailto:<?= e($m['email']) ?>" class="small text-forest"><?= e($m['email']) ?></a>
        </td>
        <td class="fw-semibold"><?= e($m['subject'] ?: 'General Inquiry') ?></td>
        <td><small class="text-muted"><?= e(truncate($m['message'], 60)) ?></small></td>
        <td><small><?= e($m['phone'] ?: '—') ?></small></td>
        <td>
          <span class="badge <?= $m['status']==='New'?'bg-warning text-dark':($m['status']==='Replied'?'bg-success':'bg-secondary') ?>">
            <?= e($m['status']) ?>
          </span>
        </td>
        <td><small class="text-muted"><?= formatDate($m['created_at']) ?></small></td>
        <td>
          <div class="d-flex gap-1">
            <button class="btn btn-sm btn-outline-forest view-msg-btn" data-msg='<?= json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) ?>' title="View Full Message">
              <i class="bi bi-eye"></i>
            </button>
            <a href="mailto:<?= e($m['email']) ?>?subject=Re: <?= urlencode($m['subject'] ?: 'Kamadhenu Goushala Inquiry') ?>" class="btn btn-sm btn-outline-primary" title="Reply via Email">
              <i class="bi bi-reply"></i>
            </a>
            <?php if ($m['status'] === 'New'): ?>
              <button class="btn btn-sm btn-outline-success mark-read-btn" data-id="<?= $m['id'] ?>" data-status="Read" title="Mark as Read">
                <i class="bi bi-check2-circle"></i>
              </button>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-danger delete-msg-btn" data-id="<?= $m['id'] ?>" data-name="<?= e($m['name']) ?>" title="Delete Message">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?>
        <tr><td colspan="7" class="text-center py-4 text-muted">No messages received yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Message Details Modal -->
<div class="modal fade" id="msgModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="msgModalTitle">Inquiry Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <div class="p-3 bg-light rounded-3 border">
              <small class="text-muted d-block">From</small>
              <strong id="msgModalSender" class="fs-6"></strong>
              <div id="msgModalEmail" class="text-forest small"></div>
              <div id="msgModalPhone" class="text-muted small"></div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="p-3 bg-light rounded-3 border">
              <small class="text-muted d-block">Subject &amp; Date</small>
              <strong id="msgModalSubject" class="fs-6"></strong>
              <div id="msgModalDate" class="text-muted small"></div>
              <div id="msgModalStatusBadge" class="mt-1"></div>
            </div>
          </div>
        </div>
        <div class="p-4 bg-white rounded-3 border">
          <h6 class="fw-bold text-forest mb-2">Message Body</h6>
          <p id="msgModalContent" class="mb-0 text-dark" style="white-space: pre-wrap; line-height: 1.7;"></p>
        </div>
      </div>
      <div class="modal-footer d-flex justify-content-between">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a id="msgModalReplyBtn" href="#" class="btn btn-gold px-4"><i class="bi bi-reply-fill me-1"></i> Reply to Devotee</a>
      </div>
    </div>
  </div>
</div>

<?php
$admin_extra_js = '<script>
document.querySelectorAll(".view-msg-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const m = JSON.parse(this.dataset.msg);
    document.getElementById("msgModalSender").textContent = m.name;
    document.getElementById("msgModalEmail").textContent = m.email;
    document.getElementById("msgModalPhone").textContent = m.phone ? "Phone: " + m.phone : "";
    document.getElementById("msgModalSubject").textContent = m.subject || "General Inquiry";
    document.getElementById("msgModalDate").textContent = new Date(m.created_at).toLocaleString();
    document.getElementById("msgModalContent").textContent = m.message;
    document.getElementById("msgModalStatusBadge").innerHTML = `<span class="badge ${m.status===\'New\'?\'bg-warning text-dark\':\'bg-secondary\'}">${m.status}</span>`;
    document.getElementById("msgModalReplyBtn").href = `mailto:${m.email}?subject=Re: ${encodeURIComponent(m.subject || "Kamadhenu Goushala Inquiry")}`;
    new bootstrap.Modal(document.getElementById("msgModal")).show();
  });
});

document.querySelectorAll(".mark-read-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=toggle_status&entity=contact_messages&id=" + this.dataset.id + "&status=Read");
      const result = await res.json();
      if (result.success) {
        showAdminToast(result.message, "success");
        setTimeout(() => location.reload(), 600);
      }
    } catch { showAdminToast("Error updating status.", "error"); }
  });
});

document.querySelectorAll(".delete-msg-btn").forEach(btn => {
  btn.addEventListener("click", async function() {
    if (!confirm("Delete message from \'" + this.dataset.name + "\'?")) return;
    try {
      const res = await fetch(BASE_URL + "/admin/api/crud_api.php?action=delete&entity=contact_messages&id=" + this.dataset.id);
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
