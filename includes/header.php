<?php
// ============================================================
// includes/header.php
// Loads preferences BEFORE rendering — no theme flash
// ============================================================
if (!function_exists('load_all_preferences')) {
    require_once __DIR__ . '/preferences_helper.php';
}
if (!function_exists('is_admin')) {
    require_once __DIR__ . '/auth.php';
}

$prefs         = load_all_preferences($db ?? null, null);
$current_theme = $prefs['theme'];
$current_page  = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?php echo htmlspecialchars($current_theme); ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DictionaryHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">📖 DictionaryHub</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">

      <!-- Left nav links -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?php echo (!isset($_GET['page']) || $current_page === 'home') ? 'active-link' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $current_page === 'catalog'     ? 'active-link' : ''; ?>" href="index.php?page=catalog">Catalog</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $current_page === 'search'      ? 'active-link' : ''; ?>" href="index.php?page=search">Search</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $current_page === 'preferences' ? 'active-link' : ''; ?>" href="index.php?page=preferences">Preferences</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo $current_page === 'help' ? 'active-link' : ''; ?>" href="index.php?page=help">Help</a>
        </li>
      </ul>

      <!-- Right side buttons -->
      <div class="d-flex align-items-center gap-2">

        <?php if (is_logged_in()): ?>
          <!-- Logged in: show username, admin dashboard link, sign out -->
          <span class="nav-user-badge">
            👤 <?php echo htmlspecialchars(current_user()['username']); ?>
          </span>
          <?php if (is_admin()): ?>
            <a href="index.php?page=admin_dashboard"
               class="theme-btn"
               style="text-decoration:none;">
              🛡️ Dashboard
            </a>
          <?php endif; ?>
          <a href="index.php?page=logout"
             class="theme-btn"
             style="text-decoration:none;">
            Sign Out
          </a>

        <?php else: ?>
          <!-- Not logged in: show Register and Login buttons -->
          <a href="index.php?page=register"
             class="theme-btn"
             style="text-decoration:none;">
            Register
          </a>
          <a href="index.php?page=login"
             class="theme-btn"
             style="text-decoration:none; background:var(--ink); color:var(--cream);">
            Login
          </a>
        <?php endif; ?>

        <!-- Dark mode toggle — always visible -->
        <button onclick="toggleTheme()" class="theme-btn" id="themeBtn">
          <?php echo $current_theme === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode'; ?>
        </button>

      </div>
    </div>
  </div>
</nav>