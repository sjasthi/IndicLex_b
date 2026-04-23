<?php
// ============================================================
// includes/admin_header.php
// Only rendered for admin pages — separate from public header
// ============================================================
if (!function_exists('is_admin')) {
    require_once __DIR__ . '/auth.php';
}
require_admin(); // block non-admins immediately

$current_page = isset($_GET['page']) ? $_GET['page'] : '';
$user         = current_user();
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin — IndicLex</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">

<!-- Admin top bar -->
<nav class="admin-navbar">
  <div class="admin-navbar-brand">
    <span class="admin-badge">🛡️ ADMIN</span>
    <a href="index.php?page=admin_dashboard" class="admin-site-name">IndicLex</a>
  </div>

  <ul class="admin-nav-links">
    <li>
      <a href="index.php?page=admin_dashboard" class="admin-nav-link <?php echo $current_page === 'admin_dashboard' ? 'active' : ''; ?>">
        📊 Dashboard
      </a>
    </li>
    <li>
      <a href="index.php?page=admin_dictionaries" class="admin-nav-link <?php echo $current_page === 'admin_dictionaries' ? 'active' : ''; ?>">
        📚 Dictionaries
      </a>
    </li>
    <li>
      <a href="index.php?page=admin_entries" class="admin-nav-link <?php echo $current_page === 'admin_entries' ? 'active' : ''; ?>">
        📝 Entries
      </a>
    </li>
    <li>
      <a href="index.php?page=admin_import" class="admin-nav-link <?php echo $current_page === 'admin_import' ? 'active' : ''; ?>">
        📥 Import
      </a>
    </li>
    <li>
      <a href="index.php?page=upload" class="admin-nav-link <?php echo $current_page === 'upload' ? 'active' : ''; ?>">
        📤 Export
      </a>
    </li>
    <li>
      <a href="index.php?page=admin_users" class="admin-nav-link <?php echo $current_page === 'admin_users' ? 'active' : ''; ?>">
        👥 Users
      </a>
    </li>
  </ul>

  <div class="admin-navbar-right">
    <a href="index.php" class="admin-nav-link" target="_blank">🌐 View Site</a>
    <div class="admin-user-pill">
      👤 <?php echo htmlspecialchars($user['username']); ?>
      <span class="admin-role-tag">admin</span>
    </div>
    <a href="index.php?page=logout" class="admin-logout-btn">Sign Out</a>
  </div>
</nav>