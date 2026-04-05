<?php
// ============================================================
// includes/auth.php
// Session management, login/logout, role-based protection
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Check if user is logged in ───────────────────────────────
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ── Check if user is admin ───────────────────────────────────
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// ── Require admin — redirect if not ─────────────────────────
function require_admin() {
    if (!is_admin()) {
        header('Location: index.php?page=login&error=unauthorized');
        exit;
    }
}

// ── Require login — redirect if not ──────────────────────────
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php?page=login&error=login_required');
        exit;
    }
}

// ── Login user — verify password and set session ─────────────
function login_user($username, $password, $db) {
    $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) return ['success' => false, 'error' => 'Invalid username or password.'];

    // Verify bcrypt hash
    if (!password_verify($password, $row['password'])) {
        return ['success' => false, 'error' => 'Invalid username or password.'];
    }

    // Set session
    $_SESSION['user_id']  = $row['id'];
    $_SESSION['username'] = $row['username'];
    $_SESSION['role']     = $row['role'];
    session_regenerate_id(true); // prevent session fixation

    return ['success' => true, 'role' => $row['role']];
}

// ── Logout user ──────────────────────────────────────────────
function logout_user() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}

// ── Get current user info ─────────────────────────────────────
function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role'     => $_SESSION['role'],
    ];
}