<?php
require_once __DIR__ . '/includes/auth_check.php'; 
$admin_page = 'settings'; 
$admin_title = 'Website Settings & Logo';
require_once __DIR__ . '/../includes/functions.php'; 
include __DIR__ . '/includes/admin_header.php';

$settings = getSettings();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Handle Reset / Remove Logo
    if (!empty($_POST['remove_logo'])) {
        $pdo->prepare("UPDATE settings SET setting_value = '' WHERE setting_key = 'site_logo'")->execute();
        $settings['site_logo'] = '';
    }

    // 2. Handle Reset / Remove Favicon
    if (!empty($_POST['remove_favicon'])) {
        $pdo->prepare("UPDATE settings SET setting_value = '' WHERE setting_key = 'site_favicon'")->execute();
        $settings['site_favicon'] = '';
    }

    // 3. Handle Logo File Upload
    if (isset($_FILES['site_logo_file']) && $_FILES['site_logo_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['site_logo_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['png', 'jpg', 'jpeg', 'svg', 'webp', 'gif', 'ico'];

        if (in_array($ext, $allowedExts)) {
            $uploadDir = ROOT_DIR . '/assets/uploads/branding/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = 'logo_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $logoPath = 'assets/uploads/branding/' . $filename;
                $check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = 'site_logo'");
                $check->execute();
                if ($check->fetchColumn() > 0) {
                    $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_logo'")->execute([$logoPath]);
                } else {
                    $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES ('site_logo', ?, 'general')")->execute([$logoPath]);
                }
                $settings['site_logo'] = $logoPath;
            } else {
                $error = 'Failed to save uploaded logo file to branding directory.';
            }
        } else {
            $error = 'Invalid logo format. Please upload PNG, JPG, JPEG, SVG, WEBP, or GIF.';
        }
    }

    // 4. Handle Favicon File Upload
    if (isset($_FILES['site_favicon_file']) && $_FILES['site_favicon_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['site_favicon_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['ico', 'png', 'svg', 'webp'];

        if (in_array($ext, $allowedExts)) {
            $uploadDir = ROOT_DIR . '/assets/uploads/branding/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = 'favicon_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $favPath = 'assets/uploads/branding/' . $filename;
                $check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = 'site_favicon'");
                $check->execute();
                if ($check->fetchColumn() > 0) {
                    $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_favicon'")->execute([$favPath]);
                } else {
                    $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES ('site_favicon', ?, 'general')")->execute([$favPath]);
                }
                $settings['site_favicon'] = $favPath;
            }
        }
    }

    // 5. Handle WhatsApp Numbers Manager (JSON array)
    if (isset($_POST['whatsapp_numbers_json'])) {
        $wpJson = trim($_POST['whatsapp_numbers_json']);
        $wpList = json_decode($wpJson, true);
        if (is_array($wpList)) {
            // Find primary number and sync to legacy key
            $primaryNum = '';
            foreach ($wpList as $wp) {
                if (!empty($wp['primary'])) {
                    $primaryNum = preg_replace('/[^0-9]/', '', $wp['number'] ?? '');
                    break;
                }
            }
            // If no primary selected, use first
            if (empty($primaryNum) && !empty($wpList[0]['number'])) {
                $primaryNum = preg_replace('/[^0-9]/', '', $wpList[0]['number']);
                $wpList[0]['primary'] = true;
            }

            $saveSetting = function(string $key, string $val, string $group = 'social') use ($pdo) {
                $chk = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
                $chk->execute([$key]);
                if ($chk->fetchColumn() > 0) {
                    $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?")->execute([$val, $key]);
                } else {
                    $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)")->execute([$key, $val, $group]);
                }
            };

            $saveSetting('whatsapp_numbers', json_encode($wpList), 'social');
            $saveSetting('whatsapp_number', $primaryNum, 'social');
        }
    }

    // 6. Handle Order Routing Mode
    if (isset($_POST['setting_order_routing_mode'])) {
        $routeMode = in_array($_POST['setting_order_routing_mode'], ['admin_panel', 'whatsapp']) ? $_POST['setting_order_routing_mode'] : 'admin_panel';
        $chk = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = 'order_routing_mode'");
        $chk->execute();
        if ($chk->fetchColumn() > 0) {
            $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'order_routing_mode'")->execute([$routeMode]);
        } else {
            $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES ('order_routing_mode', ?, 'orders')")->execute([$routeMode]);
        }
    }

    // 7. Handle Standard Text/Setting Fields
    foreach ($_POST as $key => $value) {
        if (str_starts_with($key, 'setting_')) {
            $skey = substr($key, 8);
            $sval = trim($value);

            // Skip keys handled by dedicated handlers above
            if (in_array($skey, ['order_routing_mode'])) {
                continue;
            }
            // Skip logo / favicon if new file was uploaded in this request
            if ($skey === 'site_logo' && !empty($_FILES['site_logo_file']['name'])) {
                continue;
            }
            if ($skey === 'site_favicon' && !empty($_FILES['site_favicon_file']['name'])) {
                continue;
            }

            $check = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
            $check->execute([$skey]);
            if ($check->fetchColumn() > 0) {
                $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?")->execute([$sval, $skey]);
            } else {
                $pdo->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES (?, ?, 'general')")->execute([$skey, $sval]);
            }
        }
    }

    // Refresh settings cache
    global $_SETTINGS_CACHE;
    $_SETTINGS_CACHE = getSettings();
    $settings = $_SETTINGS_CACHE;

    if (empty($error)) {
        $success = 'Website settings and logo updated successfully!';
    }
}

$currentLogo = $settings['site_logo'] ?? '';
$currentFavicon = $settings['site_favicon'] ?? '';

$groups = [
  'General Information' => ['site_name','site_tagline','footer_about','footer_copyright'],
  'Contact Details'     => ['phone_primary','phone_secondary','email_primary','email_donations','address','google_maps_url'],
  'Social Media Links'  => ['facebook_url','instagram_url','youtube_url','twitter_url'],
  'Donation & Bank'     => ['donation_upi_id','donation_bank_name','donation_account_name','donation_account_no','donation_ifsc_code','donation_80g_info'],
  'Stats (Homepage)'    => ['stat_cows_served','stat_donors','stat_years_seva','stat_breeds'],
];
?>

<?php if ($success): ?>
  <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
    <div><strong>Success!</strong> <?= e($success) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
    <div><strong>Error:</strong> <?= e($error) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

  <!-- 1. Dedicated Website Logo & Branding Section -->
  <div class="admin-card mb-4 border-2 border-warning">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0 text-dark">
        <i class="bi bi-palette-fill text-warning me-2"></i> Website Logo &amp; Branding
      </h5>
      <span class="badge bg-warning text-dark px-3 py-1 fw-bold">Controlled by Admin</span>
    </div>
    <p class="text-muted small mb-4">
      Upload your official logo image. The logo will automatically appear in the top navbar, mobile navigation menu, footer, and admin panel.
    </p>

    <div class="row g-4 align-items-stretch">
      <!-- Left: Current Logo Preview -->
      <div class="col-lg-5">
        <div class="p-3 border rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
          <div>
            <div class="fw-bold small text-muted text-uppercase mb-2">
              <i class="bi bi-eye me-1"></i> Current Logo Preview
            </div>
            
            <!-- Navbar Light Preview Box -->
            <div class="p-3 mb-2 rounded border bg-white d-flex align-items-center justify-content-center gap-3" style="min-height: 90px;">
              <div id="logoSlotLight">
                <?php if (!empty($currentLogo)): ?>
                  <img src="<?= e(getImageUrl($currentLogo)) ?>" id="liveLogoPreviewLight" alt="Logo" class="img-fluid" style="max-height: 48px; width: auto; object-fit: contain;">
                <?php else: ?>
                  <div class="brand-icon" style="width:40px;height:40px;font-size:1.3rem;"><i class="bi bi-heart-fill"></i></div>
                <?php endif; ?>
              </div>
              <div class="text-start">
                <div class="fw-bold lh-1 text-dark" style="font-size: 1.2rem;"><?= e($settings['site_name'] ?? 'KAMADHENU') ?></div>
                <div style="font-size: 0.68rem; letter-spacing: 2px; color: var(--sacred-gold-dark); font-weight: 700; margin-top: 2px;"><?= e($settings['site_tagline'] ?? 'GOUSHALA') ?></div>
              </div>
            </div>
            <div class="text-center small text-muted mb-3">Navbar (Light Header Preview)</div>

            <!-- Dark Footer Preview Box -->
            <div class="p-3 rounded border d-flex align-items-center justify-content-center gap-3" style="min-height: 85px; background: #173B2A;">
              <div id="logoSlotDark">
                <?php if (!empty($currentLogo)): ?>
                  <img src="<?= e(getImageUrl($currentLogo)) ?>" id="liveLogoPreviewDark" alt="Logo" class="img-fluid" style="max-height: 44px; width: auto; object-fit: contain;">
                <?php else: ?>
                  <div class="brand-icon" style="width:36px;height:36px;font-size:1.1rem;"><i class="bi bi-heart-fill"></i></div>
                <?php endif; ?>
              </div>
              <div class="text-start">
                <div class="fw-bold lh-1 text-white" style="font-size: 1.1rem;"><?= e($settings['site_name'] ?? 'KAMADHENU') ?></div>
                <div style="font-size: 0.68rem; letter-spacing: 2px; color: #FFE082; font-weight: 700; margin-top: 2px;"><?= e($settings['site_tagline'] ?? 'GOUSHALA') ?></div>
              </div>
            </div>
            <div class="text-center small text-muted mt-1">Footer (Dark Background Preview)</div>
          </div>

          <?php if (!empty($currentLogo)): ?>
            <div class="mt-3 text-center">
              <button type="submit" name="remove_logo" value="1" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to remove the custom logo and revert to the default Kamadhenu brand title?')">
                <i class="bi bi-trash3 me-1"></i> Reset to Default Logo
              </button>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: Logo Upload & URL Options -->
      <div class="col-lg-7">
        <div class="p-3 border rounded-3 bg-white h-100">
          <div class="mb-3">
            <label class="form-label fw-bold small">
              <i class="bi bi-cloud-arrow-up text-primary me-1"></i> Upload New Logo File
            </label>
            <input type="file" name="site_logo_file" id="siteLogoFileInput" class="form-control" accept="image/png, image/jpeg, image/svg+xml, image/webp, image/gif">
            <div class="form-text small">
              Supported formats: <strong>PNG, SVG, WEBP, JPG, GIF</strong>. Recommended: Transparent PNG or SVG with 40px–90px height.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Or Provide Logo Image URL / Relative Path</label>
            <input type="text" name="setting_site_logo" id="siteLogoUrlInput" class="form-control form-control-sm" value="<?= e($currentLogo) ?>" placeholder="e.g. assets/uploads/branding/logo.png or https://...">
          </div>

          <!-- Logo & Title Sizing & Space Controls -->
          <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="fw-bold small text-dark mb-2"><i class="bi bi-sliders text-warning me-1"></i> Logo &amp; Title Size &amp; Space Controls</div>
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Display Mode</label>
                <select name="setting_brand_display" class="form-select form-select-sm">
                  <option value="both" <?= ($settings['brand_display'] ?? 'both') === 'both' ? 'selected' : '' ?>>Show Both: Logo + Title (Default)</option>
                  <option value="logo_only" <?= ($settings['brand_display'] ?? '') === 'logo_only' ? 'selected' : '' ?>>Logo Only (Maximum Space Saving)</option>
                  <option value="text_only" <?= ($settings['brand_display'] ?? '') === 'text_only' ? 'selected' : '' ?>>Title Text Only (With Sacred Icon)</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Logo Height (Pixels)</label>
                <select name="setting_logo_height" class="form-select form-select-sm">
                  <option value="28" <?= ($settings['logo_height'] ?? '') === '28' ? 'selected' : '' ?>>28px (Ultra Compact)</option>
                  <option value="32" <?= ($settings['logo_height'] ?? '') === '32' ? 'selected' : '' ?>>32px (Compact)</option>
                  <option value="36" <?= ($settings['logo_height'] ?? '36') === '36' ? 'selected' : '' ?>>36px (Standard / Recommended)</option>
                  <option value="42" <?= ($settings['logo_height'] ?? '') === '42' ? 'selected' : '' ?>>42px (Medium)</option>
                  <option value="48" <?= ($settings['logo_height'] ?? '') === '48' ? 'selected' : '' ?>>48px (Large)</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Brand Title Font Size</label>
                <select name="setting_brand_title_size" class="form-select form-select-sm">
                  <option value="compact" <?= ($settings['brand_title_size'] ?? 'compact') === 'compact' ? 'selected' : '' ?>>Compact (1.08rem) - Recommended</option>
                  <option value="small" <?= ($settings['brand_title_size'] ?? '') === 'small' ? 'selected' : '' ?>>Small (1.15rem)</option>
                  <option value="medium" <?= ($settings['brand_title_size'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium (1.25rem)</option>
                  <option value="large" <?= ($settings['brand_title_size'] ?? '') === 'large' ? 'selected' : '' ?>>Large (1.35rem)</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold">Navbar Tagline Display</label>
                <select name="setting_show_nav_tagline" class="form-select form-select-sm">
                  <option value="yes" <?= ($settings['show_nav_tagline'] ?? 'yes') === 'yes' ? 'selected' : '' ?>>Show Subtitle ("GOUSHALA")</option>
                  <option value="no" <?= ($settings['show_nav_tagline'] ?? '') === 'no' ? 'selected' : '' ?>>Hide Subtitle (Saves Space)</option>
                </select>
              </div>
            </div>
          </div>

          <hr class="my-3">

          <!-- Favicon Section -->
          <div>
            <label class="form-label fw-bold small">
              <i class="bi bi-browser-chrome text-primary me-1"></i> Website Favicon (Browser Tab Icon)
            </label>
            <div class="d-flex gap-3 align-items-center mb-2">
              <?php if (!empty($currentFavicon)): ?>
                <img src="<?= e(getImageUrl($currentFavicon)) ?>" alt="Favicon" style="width:32px;height:32px;object-fit:contain;" class="border rounded p-1 bg-white">
                <button type="submit" name="remove_favicon" value="1" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="return confirm('Remove custom favicon?')"><i class="bi bi-x"></i> Remove</button>
              <?php else: ?>
                <div class="border rounded p-1 bg-light text-muted small text-center" style="width:32px;height:32px;"><i class="bi bi-globe"></i></div>
                <span class="small text-muted">No custom favicon set</span>
              <?php endif; ?>
            </div>
            <input type="file" name="site_favicon_file" class="form-control form-control-sm mb-2" accept=".ico, .png, .svg, .webp">
            <input type="text" name="setting_site_favicon" class="form-control form-control-sm" value="<?= e($currentFavicon) ?>" placeholder="Favicon URL or path...">
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 2. Other Website Settings Groups -->
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

  <!-- 3. WhatsApp Numbers Manager -->
  <?php
    $wpNumbersRaw = $settings['whatsapp_numbers'] ?? '';
    $wpNumbers = json_decode($wpNumbersRaw, true);
    if (!is_array($wpNumbers) || empty($wpNumbers)) {
        // Migrate legacy single number
        $legacyNum = $settings['whatsapp_number'] ?? '';
        $wpNumbers = !empty($legacyNum) ? [['number' => $legacyNum, 'label' => 'Primary', 'primary' => true]] : [];
    }
  ?>
  <div class="admin-card mb-4 border-2 border-success">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0 text-dark">
        <i class="bi bi-whatsapp text-success me-2"></i> WhatsApp Numbers Manager
      </h5>
      <span class="badge bg-success px-3 py-1 fw-bold">Multiple Numbers</span>
    </div>
    <p class="text-muted small mb-3">
      Add multiple WhatsApp numbers for your organization (e.g. Sales, Support, Owner). Select one as the <strong>primary number</strong> — it will be used for the website WhatsApp link, floating button, and order routing.
    </p>

    <!-- Hidden field that stores the JSON -->
    <input type="hidden" name="whatsapp_numbers_json" id="wpNumbersJsonInput" value='<?= e($wpNumbersRaw ?: '[]') ?>'>

    <div id="wpNumbersList" class="mb-3"></div>

    <button type="button" class="btn btn-outline-success btn-sm" id="wpAddNumberBtn">
      <i class="bi bi-plus-circle me-1"></i> Add WhatsApp Number
    </button>
  </div>

  <!-- 4. Order Routing Configuration -->
  <?php $orderRoutingMode = $settings['order_routing_mode'] ?? 'admin_panel'; ?>
  <div class="admin-card mb-4 border-2 border-primary">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="fw-bold mb-0 text-dark">
        <i class="bi bi-signpost-split-fill text-primary me-2"></i> Order Routing Configuration
      </h5>
      <span class="badge <?= $orderRoutingMode === 'whatsapp' ? 'bg-success' : 'bg-primary' ?> px-3 py-1 fw-bold">
        <?= $orderRoutingMode === 'whatsapp' ? 'WhatsApp Mode' : 'Admin Panel Mode' ?>
      </span>
    </div>
    <p class="text-muted small mb-4">
      Choose where customer orders from the Organic Store are routed. <strong>Admin Panel</strong> saves orders to the database (viewable in Admin → Orders). <strong>WhatsApp</strong> sends the full order with product details and customer info directly to your primary WhatsApp number.
    </p>

    <div class="row g-3">
      <div class="col-md-6">
        <label class="d-block">
          <div class="form-check form-check-inline p-3 border rounded-3 w-100 <?= $orderRoutingMode === 'admin_panel' ? 'border-primary bg-primary bg-opacity-10' : '' ?>" style="cursor: pointer;">
            <input class="form-check-input" type="radio" name="setting_order_routing_mode" id="routeAdminPanel" value="admin_panel" <?= $orderRoutingMode === 'admin_panel' ? 'checked' : '' ?>>
            <label class="form-check-label w-100" for="routeAdminPanel" style="cursor: pointer;">
              <div class="fw-bold fs-6 text-dark">
                <i class="bi bi-display me-2 text-primary"></i> Admin Panel (Default)
              </div>
              <div class="text-muted small mt-1">
                Orders are saved to the database and appear in <strong>Admin → Orders</strong>. You can manage order status, print invoices, and track everything from the admin panel.
              </div>
            </label>
          </div>
        </label>
      </div>
      <div class="col-md-6">
        <label class="d-block">
          <div class="form-check form-check-inline p-3 border rounded-3 w-100 <?= $orderRoutingMode === 'whatsapp' ? 'border-success bg-success bg-opacity-10' : '' ?>" style="cursor: pointer;">
            <input class="form-check-input" type="radio" name="setting_order_routing_mode" id="routeWhatsapp" value="whatsapp" <?= $orderRoutingMode === 'whatsapp' ? 'checked' : '' ?>>
            <label class="form-check-label w-100" for="routeWhatsapp" style="cursor: pointer;">
              <div class="fw-bold fs-6 text-dark">
                <i class="bi bi-whatsapp me-2 text-success"></i> WhatsApp
              </div>
              <div class="text-muted small mt-1">
                Orders are <strong>also saved to the database</strong>, plus the customer is redirected to WhatsApp with a pre-filled message containing all product details, quantities, total, and delivery information.
              </div>
            </label>
          </div>
        </label>
      </div>
    </div>

    <div class="mt-3 p-3 bg-light rounded-3 border" id="routingInfoBox">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill text-primary"></i>
        <span class="small" id="routingInfoText">
          <?php if ($orderRoutingMode === 'whatsapp'): ?>
            Orders will be saved to admin panel <strong>AND</strong> sent via WhatsApp to your primary number: <strong><?= e($settings['whatsapp_number'] ?? 'Not Set') ?></strong>
          <?php else: ?>
            All orders will be saved and managed exclusively through the Admin Panel.
          <?php endif; ?>
        </span>
      </div>
    </div>
  </div>

  <div class="sticky-bottom bg-white p-3 border-top rounded-3 shadow-sm d-flex justify-content-between align-items-center">
    <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Changes will immediately take effect across all pages on the website.</span>
    <button type="submit" class="btn btn-gold px-5 py-2 fw-bold">
      <i class="bi bi-check-lg me-2"></i> Save All Settings &amp; Logo
    </button>
  </div>
</form>

<script>
// Instant client-side preview when an image file is selected
document.getElementById('siteLogoFileInput')?.addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(evt) {
      const src = evt.target.result;
      const slotLight = document.getElementById('logoSlotLight');
      if (slotLight) {
        slotLight.innerHTML = '<img src="' + src + '" id="liveLogoPreviewLight" alt="Logo" class="img-fluid" style="max-height: 48px; width: auto; object-fit: contain;">';
      }

      const slotDark = document.getElementById('logoSlotDark');
      if (slotDark) {
        slotDark.innerHTML = '<img src="' + src + '" id="liveLogoPreviewDark" alt="Logo" class="img-fluid" style="max-height: 44px; width: auto; object-fit: contain;">';
      }
    };
    reader.readAsDataURL(file);
  }
});

// ============================================================
// WhatsApp Numbers Manager
// ============================================================
(function() {
  const jsonInput = document.getElementById('wpNumbersJsonInput');
  const listEl = document.getElementById('wpNumbersList');
  const addBtn = document.getElementById('wpAddNumberBtn');
  if (!jsonInput || !listEl || !addBtn) return;

  let numbers = [];
  try { numbers = JSON.parse(jsonInput.value); } catch(e) { numbers = []; }
  if (!Array.isArray(numbers)) numbers = [];

  function render() {
    if (numbers.length === 0) {
      listEl.innerHTML = '<div class="text-muted small p-3 bg-light rounded border text-center"><i class="bi bi-info-circle me-1"></i> No WhatsApp numbers added yet. Click "Add WhatsApp Number" below.</div>';
      syncJson();
      return;
    }
    let html = '';
    numbers.forEach((wp, idx) => {
      const isPrimary = !!wp.primary;
      html += `
        <div class="d-flex flex-wrap gap-2 align-items-center p-2 mb-2 border rounded-3 ${isPrimary ? 'border-success bg-success bg-opacity-10' : 'bg-white'}">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="wp_primary_radio" id="wpPrimary_${idx}" ${isPrimary ? 'checked' : ''} onchange="WPManager.setPrimary(${idx})">
            <label class="form-check-label small fw-bold ${isPrimary ? 'text-success' : 'text-muted'}" for="wpPrimary_${idx}">
              ${isPrimary ? '★ Primary' : 'Set Primary'}
            </label>
          </div>
          <div style="flex:1;min-width:160px;">
            <input type="text" class="form-control form-control-sm" placeholder="919845088990 (country code + number)" value="${wp.number || ''}" onchange="WPManager.updateNumber(${idx}, this.value)">
          </div>
          <div style="flex:0.6;min-width:100px;">
            <input type="text" class="form-control form-control-sm" placeholder="Label (e.g. Sales)" value="${wp.label || ''}" onchange="WPManager.updateLabel(${idx}, this.value)">
          </div>
          <button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="WPManager.remove(${idx})" title="Remove this number">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      `;
    });
    listEl.innerHTML = html;
    syncJson();
  }

  function syncJson() {
    jsonInput.value = JSON.stringify(numbers);
  }

  window.WPManager = {
    setPrimary(idx) {
      numbers.forEach((w, i) => { w.primary = (i === idx); });
      render();
    },
    updateNumber(idx, val) {
      numbers[idx].number = val.replace(/[^0-9+]/g, '');
      syncJson();
    },
    updateLabel(idx, val) {
      numbers[idx].label = val;
      syncJson();
    },
    remove(idx) {
      const wasPrimary = numbers[idx].primary;
      numbers.splice(idx, 1);
      if (wasPrimary && numbers.length > 0) {
        numbers[0].primary = true;
      }
      render();
    },
    add() {
      numbers.push({ number: '', label: '', primary: numbers.length === 0 });
      render();
    }
  };

  addBtn.addEventListener('click', () => WPManager.add());
  render();
})();

// ============================================================
// Order Routing Toggle — Visual Feedback
// ============================================================
document.querySelectorAll('input[name="setting_order_routing_mode"]').forEach(radio => {
  radio.addEventListener('change', function() {
    const mode = this.value;
    const infoText = document.getElementById('routingInfoText');
    // Update border highlights
    this.closest('.row').querySelectorAll('.form-check-inline').forEach(el => {
      el.classList.remove('border-primary','bg-primary','bg-opacity-10','border-success','bg-success');
    });
    const parent = this.closest('.form-check-inline');
    if (mode === 'whatsapp') {
      parent.classList.add('border-success','bg-success','bg-opacity-10');
      if (infoText) infoText.innerHTML = 'Orders will be saved to admin panel <strong>AND</strong> sent via WhatsApp to your primary number.';
    } else {
      parent.classList.add('border-primary','bg-primary','bg-opacity-10');
      if (infoText) infoText.innerHTML = 'All orders will be saved and managed exclusively through the Admin Panel.';
    }
  });
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
