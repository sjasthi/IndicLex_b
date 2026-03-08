<!DOCTYPE html>
<html lang="en">
<script>
  document.documentElement.setAttribute(
    'data-bs-theme',
    localStorage.getItem('theme') || 'light'
  );
</script>
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
          <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] === 'home') ? 'active-link' : ''; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'catalog') ? 'active-link' : ''; ?>" href="index.php?page=catalog">Catalog</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'search') ? 'active-link' : ''; ?>" href="index.php?page=search">Search</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] === 'preferences') ? 'active-link' : ''; ?>" href="index.php?page=preferences">Preferences</a>
        </li>
      </ul>
      <button onclick="toggleTheme()" class="theme-btn" id="themeBtn">🌙 Dark Mode</button>
    </div>
  </div>
</nav>