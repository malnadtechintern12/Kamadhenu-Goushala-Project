<?php
// ============================================================
// Admin Login Page
// ============================================================
session_start();
require_once __DIR__ . '/../config/database.php';
$base = BASE_URL;

// Already logged in?
if (isset($_SESSION['admin_id'])) {
    header("Location: $base/admin/dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id']    = $admin['id'];
            $_SESSION['admin_name']  = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role']  = $admin['role'];
            header("Location: $base/admin/dashboard.php");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

if (isset($_GET['error']) && $_GET['error'] === 'session_expired') {
    $error = 'Your session has expired. Please log in again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Kamadhenu Goushala</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/variables.css">
  <style>
    body {
      font-family: 'Outfit', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #173B2A 0%, #28563C 50%, #173B2A 100%);
      padding: 20px;
    }
    .login-card {
      background: #fff;
      border-radius: 24px;
      padding: 48px 40px;
      width: 100%;
      max-width: 440px;
      box-shadow: 0 32px 80px rgba(0,0,0,0.3);
    }
    .login-brand {
      text-align: center;
      margin-bottom: 32px;
    }
    .login-brand .icon {
      width: 64px; height: 64px;
      background: linear-gradient(135deg, #D4A72C, #F3D068);
      border-radius: 16px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      color: #173B2A;
      margin-bottom: 16px;
    }
    .login-brand h2 { font-weight: 800; color: #173B2A; font-size: 1.5rem; }
    .login-brand small { color: #64746B; font-size: 0.85rem; }
    .form-control { border-radius: 12px; padding: 14px 16px; border: 1.5px solid #e8e2d5; }
    .form-control:focus { border-color: #28563C; box-shadow: 0 0 0 3px rgba(40,86,60,0.1); }
    .btn-login {
      background: linear-gradient(135deg, #D4A72C, #F3D068, #D4A72C);
      color: #173B2A;
      font-weight: 700;
      border: 0;
      border-radius: 12px;
      padding: 14px;
      font-size: 1rem;
      transition: all 0.2s;
    }
    .btn-login:hover { filter: brightness(1.05); transform: translateY(-1px); color: #173B2A; }
    .back-link { color: rgba(255,255,255,0.6); text-decoration: none; font-size: 0.85rem; }
    .back-link:hover { color: #fff; }
  </style>
</head>
<body>
  <div>
    <div class="login-card">
      <div class="login-brand">
        <div class="icon"><i class="bi bi-shield-lock-fill"></i></div>
        <h2>KAMADHENU ADMIN</h2>
        <small>Goushala Management Portal</small>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 text-center small"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="mb-3">
          <label class="form-label fw-semibold">Email Address</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0" style="border-radius:12px 0 0 12px;"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" class="form-control border-start-0" placeholder="admin@kamadhenugoushala.org" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" style="border-radius:0 12px 12px 0;">
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold">Password</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0" style="border-radius:12px 0 0 12px;"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required style="border-radius:0 12px 12px 0;">
          </div>
        </div>
        <button type="submit" class="btn btn-login w-100 mb-3">
          <i class="bi bi-box-arrow-in-right me-2"></i> SIGN IN
        </button>
      </form>
    </div>
  </div>
</body>
</html>
