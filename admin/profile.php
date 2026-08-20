<?php
require_once __DIR__ . '/includes/auth_check.php'; $admin_page = 'profile'; $admin_title = 'My Profile';
require_once __DIR__ . '/../includes/functions.php'; include __DIR__ . '/includes/admin_header.php';
$admin = $pdo->prepare("SELECT * FROM admins WHERE id = ?"); $admin->execute([$_SESSION['admin_id']]); $admin = $admin->fetch();
$success = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        if (empty($name) || empty($email)) { $error = 'Name and email are required.'; }
        else {
            $pdo->prepare("UPDATE admins SET name=?, email=? WHERE id=?")->execute([$name, $email, $_SESSION['admin_id']]);
            $_SESSION['admin_name'] = $name; $_SESSION['admin_email'] = $email;
            $admin['name'] = $name; $admin['email'] = $email;
            $success = 'Profile updated!';
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $newpass = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (!password_verify($current, $admin['password_hash'])) { $error = 'Current password is incorrect.'; }
        elseif (strlen($newpass) < 6) { $error = 'New password must be at least 6 characters.'; }
        elseif ($newpass !== $confirm) { $error = 'New passwords do not match.'; }
        else {
            $hash = password_hash($newpass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET password_hash=? WHERE id=?")->execute([$hash, $_SESSION['admin_id']]);
            $success = 'Password changed successfully!';
        }
    }
}
?>
<?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= e($error) ?></div><?php endif; ?>
<div class="row g-4">
  <div class="col-lg-6">
    <div class="admin-card">
      <h5 class="fw-bold mb-3"><i class="bi bi-person text-warning me-2"></i>Profile Info</h5>
      <form method="POST">
        <input type="hidden" name="action" value="update_profile">
        <div class="mb-3"><label class="form-label fw-semibold">Name</label><input type="text" name="name" class="form-control" value="<?= e($admin['name']) ?>" required></div>
        <div class="mb-3"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control" value="<?= e($admin['email']) ?>" required></div>
        <div class="mb-3"><label class="form-label fw-semibold">Role</label><input type="text" class="form-control" value="<?= e(ucfirst($admin['role'])) ?>" disabled></div>
        <button type="submit" class="btn btn-gold">Save Profile</button>
      </form>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="admin-card">
      <h5 class="fw-bold mb-3"><i class="bi bi-lock text-warning me-2"></i>Change Password</h5>
      <form method="POST">
        <input type="hidden" name="action" value="change_password">
        <div class="mb-3"><label class="form-label fw-semibold">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
        <div class="mb-3"><label class="form-label fw-semibold">New Password</label><input type="password" name="new_password" class="form-control" required minlength="6"></div>
        <div class="mb-3"><label class="form-label fw-semibold">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
        <button type="submit" class="btn btn-forest">Change Password</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
