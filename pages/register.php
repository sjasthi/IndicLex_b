<?php
// ============================================================
// pages/register.php — User registration
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Already logged in — redirect
if (is_logged_in()) {
    header('Location: index.php?page=home');
    exit;
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username']         ?? '');
    $email     = trim($_POST['email']            ?? '');
    $password  = $_POST['password']              ?? '';
    $password2 = $_POST['password_confirm']      ?? '';

    // ── Validation ──────────────────────────────────────────
    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = 'Username must be between 3 and 30 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers and underscores.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    } elseif (!preg_match('/[\W]/', $password)) {
        $errors[] = 'Password must contain at least one special character (!@#$ etc).';
    }

    if ($password !== $password2) {
        $errors[] = 'Passwords do not match.';
    }

    // ── Check username/email not already taken ───────────────
    if (empty($errors)) {
        $check = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param('ss', $username, $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $errors[] = 'Username or email is already registered.';
        }
        $check->close();
    }

    // ── Insert user ──────────────────────────────────────────
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $role = 'user'; // all self-registered accounts are regular users
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $username, $email, $hash, $role);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
}
?>

<section class="login-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">

        <div class="login-card">
          <div class="login-header">
            <div class="login-logo">📖</div>
            <h1 class="login-title">Create Account</h1>
            <p class="login-subtitle">Join DictionaryHub today</p>
          </div>

          <?php if ($success): ?>
            <div class="register-success">
              ✅ Account created successfully!<br>
              <a href="index.php?page=login">Click here to log in →</a>
            </div>

          <?php else: ?>

            <?php if (!empty($errors)): ?>
              <div class="login-error">
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-1 ps-3">
                  <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=register">

              <div class="login-field">
                <label class="login-label">Username</label>
                <input type="text" name="username" class="login-input"
                  value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                  placeholder="e.g. john_doe" autocomplete="username" required>
                <small class="field-hint">Letters, numbers and underscores only</small>
              </div>

              <div class="login-field">
                <label class="login-label">Email</label>
                <input type="email" name="email" class="login-input"
                  value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                  placeholder="you@example.com" autocomplete="email" required>
              </div>

              <div class="login-field">
                <label class="login-label">Password</label>
                <input type="password" name="password" class="login-input"
                  placeholder="Min 8 chars, 1 uppercase, 1 number, 1 symbol"
                  autocomplete="new-password" required
                  oninput="checkStrength(this.value)">
                <!-- Password strength bar -->
                <div class="strength-bar-wrap">
                  <div class="strength-bar" id="strengthBar"></div>
                </div>
                <small class="strength-label" id="strengthLabel"></small>
              </div>

              <div class="login-field">
                <label class="login-label">Confirm Password</label>
                <input type="password" name="password_confirm" class="login-input"
                  placeholder="Re-enter your password"
                  autocomplete="new-password" required>
              </div>

              <!-- Password requirements checklist -->
              <div class="password-rules">
                <div class="rule" id="rule-len">✗ At least 8 characters</div>
                <div class="rule" id="rule-upper">✗ One uppercase letter</div>
                <div class="rule" id="rule-num">✗ One number</div>
                <div class="rule" id="rule-sym">✗ One special character</div>
              </div>

              <button type="submit" class="login-btn">Create Account →</button>
            </form>

          <?php endif; ?>

          <div class="login-footer">
            Already have an account? <a href="index.php?page=login">Sign in →</a>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<script>
function checkStrength(pw) {
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');

    const rules = {
        'rule-len':   pw.length >= 8,
        'rule-upper': /[A-Z]/.test(pw),
        'rule-num':   /[0-9]/.test(pw),
        'rule-sym':   /[\W]/.test(pw),
    };

    // Update checklist
    Object.keys(rules).forEach(id => {
        const el = document.getElementById(id);
        if (rules[id]) {
            el.textContent = '✓ ' + el.textContent.slice(2);
            el.classList.add('rule-pass');
        } else {
            el.textContent = '✗ ' + el.textContent.slice(2);
            el.classList.remove('rule-pass');
        }
    });

    const score = Object.values(rules).filter(Boolean).length;
    const levels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    const colors = ['', '#e05a3a', '#f0a500', '#7ab648', '#2ecc71'];
    const widths = ['0%', '25%', '50%', '75%', '100%'];

    bar.style.width      = widths[score];
    bar.style.background = colors[score];
    label.textContent    = score > 0 ? levels[score] : '';
    label.style.color    = colors[score];
}
</script>