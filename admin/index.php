<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
    header('Location: dashboard.php' . (isset($_GET['lang']) ? '?lang=' . $_GET['lang'] : ''));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        if (attempt_login($_POST['password'] ?? '')) {
            log_action('login', 'success');
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Wrong password';
        log_action('login', 'failed');
    } catch (Exception $e) {
        $error = 'System error. Please try again.';
        log_error('Login error: ' . $e->getMessage());
    }
}

$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login — MT Messe Stand</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #1a1a1a; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: system-ui, sans-serif; }
    .login-card { background: #fff; padding: 2.5rem 2rem; border-radius: 12px; width: 100%; max-width: 360px; box-shadow: 0 8px 32px rgba(0,0,0,.3); }
    .login-card h1 { font-size: 1.3rem; text-align: center; margin-bottom: .3rem; }
    .login-card .accent { color: #cc0000; }
    .login-card .sub { text-align: center; color: #888; font-size: .85rem; margin-bottom: 1.5rem; }
    .btn-login { background: #cc0000; color: #fff; width: 100%; padding: .6rem; border: none; border-radius: 6px; font-weight: 600; }
    .btn-login:hover { background: #aa0000; }
    .error { color: #cc0000; text-align: center; margin-bottom: 1rem; font-size: .9rem; }
  </style>
</head>
<body>
<div class="login-card">
  <h1>MT <span class="accent">Messe Stand</span></h1>
  <div class="sub">Admin Panel</div>
  <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= $token ?>">
    <div class="mb-3">
      <input type="password" name="password" class="form-control" placeholder="Password" autofocus required>
    </div>
    <button type="submit" class="btn-login">Giriş →</button>
  </form>
</div>
</body>
</html>
