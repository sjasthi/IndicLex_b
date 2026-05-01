<?php
// ============================================================
// pages/login.php
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Already logged in — redirect
if (is_logged_in()) {
    header('Location: index.php?page=' . (is_admin() ? 'admin_dashboard' : 'home'));
    exit;
}

$error = '';

// ── Handle POST ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') {
        $error = 'Please enter username.';
    } else {
        $result = login_user($username, $password, $db);
        if ($result['success']) {
            // Redirect based on role
            $dest = $result['role'] === 'admin' ? 'admin_dashboard' : 'home';
            header('Location: index.php?page=' . $dest);
            exit;
        } else {
            $error = $result['error'];
        }
    }
}

// ── Error from redirect ──────────────────────────────────────
if (isset($_GET['error'])) {
    $error = match($_GET['error']) {
        'unauthorized'    => 'You must be an admin to access that page.',
        'login_required'  => 'Please log in to continue.',
        default           => '',
    };
}
?>

<section class="login-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-5 col-lg-4">

        <div class="login-card">
          <div class="login-header">
            <div class="login-logo">📖</div>
            <h1 class="login-title">DictionaryHub</h1>
            <p class="login-subtitle">Admin Login</p>
          </div>

          <?php if ($error): ?>
            <div class="login-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form method="POST" action="index.php?page=login">
            <div class="login-field">
              <label class="login-label">Username</label>
              <input
                type="text"
                name="username"
                class="login-input"
                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                placeholder="Enter username"
                autocomplete="username"
                required
              >
            </div>
            <div class="login-field">
              <label class="login-label">Password</label>
              <input
                type="password"
                name="password"
                class="login-input"
                placeholder="Enter password"
                autocomplete="current-password"
              >
            </div>
            <button type="submit" class="login-btn">Sign In →</button>
          </form>

          <div class="login-footer">
            <a href="index.php">← Back to Dictionary</a>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>