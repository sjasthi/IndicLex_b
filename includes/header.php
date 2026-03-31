<?php
// ============================================================
// includes/header.php
// Loads preferences BEFORE rendering so theme is applied
// instantly — no flash of wrong theme
// ============================================================
require_once __DIR__ . '/preferences_helper.php';

// Load preferences: cookies first, then DB, then defaults
$prefs = load_all_preferences($db ?? null, null);

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
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'home'        || !isset($_GET['page'])) ? 'active-link' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'catalog')     ? 'active-link' : ''; ?>" href="index.php?page=catalog">Catalog</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'search')      ? 'active-link' : ''; ?>" href="index.php?page=search">Search</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'preferences') ? 'active-link' : ''; ?>" href="index.php?page=preferences">Preferences</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($current_page === 'admin_import') ? 'active-link' : ''; ?>" href="index.php?page=admin_import">Import</a>
        </li>
      </ul>
      <button onclick="toggleTheme()" class="theme-btn" id="themeBtn">
        <?php echo $current_theme === 'dark' ? '☀️ Light Mode' : '🌙 Dark Mode'; ?>
      </button>
    </div>
  </div>
</nav>