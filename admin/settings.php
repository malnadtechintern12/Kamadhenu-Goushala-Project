<?php
require_once __DIR__ . '/includes/auth_check.php'; $admin_page = 'settings'; $admin_title = 'Website Settings';
require_once __DIR__ . '/../includes/functions.php'; include __DIR__ . '/includes/admin_header.php';
$settings = getSettings();
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if (str_starts_with($key, 'setting_')) {
            $skey = substr($key, 8);
            $sval = trim($value);
            $check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?"); $check->execute([$skey]);
            if ($check->fetchColumn() > 0) {
                $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?")->execute([$sval, $skey]);
            } else {
                $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, 'general')")->execute([$skey, $sval]);
            }
        }
    }
    $settings = getSettings();
    $success = 'Settings saved successfully!';
}
$groups = [
  'General' => ['site_name','site_tagline','footer_about','footer_copyright'],
  'Contact' => ['phone_primary','phone_secondary','email_primary','email_donations','address','google_maps_url'],
  'Social Media' => ['whatsapp_number','facebook_url','instagram_url','youtube_url','twitter_url'],
  'Donation & Bank' => ['donation_upi_id','donation_bank_name','donation_account_name','donation_account_no','donation_ifsc_code','donation_80g_info'],
  'Stats (Homepage)' => ['stat_cows_served','stat_donors','stat_years_seva','stat_breeds'],
];
?>
<?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= e($success) ?></div><?php endif; ?>
<form method="POST">
  <?php foreach ($groups as $group => $keys): ?>
  <div class="admin-card mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-gear text-warning me-2"></i><?= e($group) ?></h5>
    <div class="row g-3">
      <?php foreach ($keys as $k):
        $label = ucwords(str_replace('_', ' ', str_replace(['site_','phone_','email_','donation_','stat_','footer_','google_'], ['','','','','','',''], $k)));
        $val = $settings[$k] ?? '';
        $isTextarea = strlen($val) > 100 || in_array($k, ['address','footer_about','donation_80g_info','google_maps_url']);
      ?>
      <div class="col-md-6">
        <label class="form-label fw-semibold small"><?= e($label) ?> <code class="text-muted"><?= e($k) ?></code></label>
        <?php if ($isTextarea): ?>
          <textarea name="setting_<?= e($k) ?>" class="form-control form-control-sm" rows="3"><?= e($val) ?></textarea>
        <?php else: ?>
          <input type="text" name="setting_<?= e($k) ?>" class="form-control form-control-sm" value="<?= e($val) ?>">
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-gold px-5 py-2"><i class="bi bi-check-lg me-2"></i> Save All Settings</button>
</form>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
