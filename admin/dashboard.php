<?php
// ============================================================
// Admin Dashboard
// ============================================================
require_once __DIR__ . '/includes/auth_check.php';
$admin_page  = 'dashboard';
$admin_title = 'Dashboard';
require_once __DIR__ . '/../includes/functions.php';
include __DIR__ . '/includes/admin_header.php';

// KPI stats
$cowCount     = $pdo->query("SELECT COUNT(*) FROM cows WHERE status='Active'")->fetchColumn();
$breedCount   = $pdo->query("SELECT COUNT(*) FROM breeds WHERE status='active'")->fetchColumn();
$donationSum  = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM donations WHERE payment_status='Completed'")->fetchColumn();
$donorCount   = $pdo->query("SELECT COUNT(DISTINCT donor_email) FROM donations WHERE payment_status='Completed'")->fetchColumn();
$productCount = $pdo->query("SELECT COUNT(*) FROM products WHERE status='active'")->fetchColumn();
$msgCount     = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='New'")->fetchColumn();
$subCount     = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status='Active'")->fetchColumn();
$eventCount   = $pdo->query("SELECT COUNT(*) FROM events WHERE status='Upcoming'")->fetchColumn();

// Recent donations
$recentDonations = $pdo->query("SELECT * FROM donations ORDER BY created_at DESC LIMIT 5")->fetchAll();
// Recent messages
$recentMessages  = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!-- Quick Action Buttons Bar -->
<div class="admin-card mb-4 bg-white">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h6 class="fw-bold mb-1 text-forest"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Actions</h6>
      <p class="text-muted small mb-0">Frequently used shortcuts and administrative management actions.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="<?= BASE_URL ?>/admin/cows.php" class="btn btn-sm btn-gold"><i class="bi bi-plus-lg me-1"></i> Add Cow</a>
      <a href="<?= BASE_URL ?>/admin/donations.php" class="btn btn-sm btn-forest"><i class="bi bi-cash-stack me-1"></i> View Donations</a>
      <a href="<?= BASE_URL ?>/admin/blogs.php" class="btn btn-sm btn-outline-forest"><i class="bi bi-pencil-square me-1"></i> New Blog</a>
      <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-sm btn-outline-forest"><i class="bi bi-bag-plus me-1"></i> Add Product</a>
      <a href="<?= BASE_URL ?>/admin/gallery.php" class="btn btn-sm btn-outline-forest"><i class="bi bi-upload me-1"></i> Upload Photo</a>
      <a href="<?= BASE_URL ?>/admin/settings.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-gear me-1"></i> Settings</a>
    </div>
  </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['icon'=>'bi-heart-fill',      'bg'=>'#d4edda','color'=>'#28563C','value'=>$cowCount,      'label'=>'Active Cows', 'link'=>'cows.php'],
    ['icon'=>'bi-bookmark-star',   'bg'=>'#fff3cd','color'=>'#856404','value'=>$breedCount,    'label'=>'Cow Breeds', 'link'=>'breeds.php'],
    ['icon'=>'bi-cash-stack',      'bg'=>'#d1ecf1','color'=>'#0c5460','value'=>'₹'.number_format((float)$donationSum),'label'=>'Total Donations', 'link'=>'donations.php'],
    ['icon'=>'bi-people-fill',     'bg'=>'#f8d7da','color'=>'#721c24','value'=>$donorCount,    'label'=>'Unique Donors', 'link'=>'sponsors.php'],
    ['icon'=>'bi-bag-check-fill',  'bg'=>'#e2d5f1','color'=>'#563d7c','value'=>$productCount,  'label'=>'Products', 'link'=>'products.php'],
    ['icon'=>'bi-envelope-fill',   'bg'=>'#fce4ec','color'=>'#c62828','value'=>$msgCount,      'label'=>'New Messages', 'link'=>'messages.php'],
    ['icon'=>'bi-megaphone-fill',  'bg'=>'#e8f5e9','color'=>'#2e7d32','value'=>$subCount,      'label'=>'Subscribers', 'link'=>'newsletter.php'],
    ['icon'=>'bi-calendar-event',  'bg'=>'#fff8e1','color'=>'#f57f17','value'=>$eventCount,    'label'=>'Upcoming Events', 'link'=>'events.php'],
  ];
  foreach ($cards as $c): ?>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= BASE_URL ?>/admin/<?= $c['link'] ?>'">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:<?= $c['bg'] ?>;color:<?= $c['color'] ?>;"><i class="bi <?= $c['icon'] ?>"></i></div>
        <div>
          <div class="stat-value"><?= $c['value'] ?></div>
          <div class="stat-label"><?= $c['label'] ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Recent Data Tables -->
<div class="row g-4">
  <div class="col-lg-7">
    <div class="admin-table">
      <div class="p-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-cash-stack text-warning me-2"></i>Recent Donations</h6>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>/admin/donations.php" class="btn btn-sm btn-forest"><i class="bi bi-list-check me-1"></i> Manage Donations</a>
        </div>
      </div>
      <table class="table">
        <thead><tr><th>Donor</th><th>Amount</th><th>Seva</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($recentDonations as $d): ?>
          <tr>
            <td><div class="fw-semibold"><?= e($d['donor_name']) ?></div><small class="text-muted"><?= e($d['donor_email']) ?></small></td>
            <td class="fw-bold text-forest">₹<?= number_format((float)$d['amount']) ?></td>
            <td><small><?= e($d['seva_name']) ?></small></td>
            <td>
              <?php
              $statusClass = match($d['payment_status']) {
                'Completed' => 'bg-success',
                'Pending' => 'bg-warning text-dark',
                'Failed' => 'bg-danger',
                default => 'bg-secondary'
              };
              ?>
              <span class="badge <?= $statusClass ?>"><?= e($d['payment_status']) ?></span>
            </td>
            <td><small class="text-muted"><?= formatDate($d['created_at'], 'd M') ?></small></td>
            <td>
              <a href="<?= BASE_URL ?>/admin/donations.php" class="btn btn-sm btn-outline-forest" title="Manage"><i class="bi bi-arrow-right"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recentDonations)): ?><tr><td colspan="6" class="text-center text-muted py-3">No donations yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="admin-table">
      <div class="p-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bi bi-envelope text-warning me-2"></i>Recent Messages</h6>
        <a href="<?= BASE_URL ?>/admin/messages.php" class="btn btn-sm btn-outline-forest">View All</a>
      </div>
      <table class="table">
        <thead><tr><th>From</th><th>Subject</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($recentMessages as $m): ?>
          <tr>
            <td>
              <div class="fw-semibold"><?= e($m['name']) ?></div>
              <small class="text-muted"><?= e($m['email']) ?></small>
            </td>
            <td><small><?= e(truncate($m['subject'] ?: $m['message'], 30)) ?></small></td>
            <td>
              <span class="badge <?= $m['status'] === 'New' ? 'bg-warning text-dark' : 'bg-secondary' ?>"><?= e($m['status']) ?></span>
            </td>
            <td>
              <a href="<?= BASE_URL ?>/admin/messages.php" class="btn btn-sm btn-outline-forest" title="Open Inbox"><i class="bi bi-envelope-open"></i></a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($recentMessages)): ?><tr><td colspan="4" class="text-center text-muted py-3">No messages.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
