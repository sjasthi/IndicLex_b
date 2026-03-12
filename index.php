<?php
  $page    = isset($_GET['page']) ? $_GET['page'] : 'home';
  $allowed = ['home', 'catalog', 'search', 'preferences', 'upload', 'admin_import'];

  // import.php handles its own POST and redirects — run it directly, no header/footer
  if ($page === 'import') {
    require 'pages/import.php';
    exit;
  }

  if (!in_array($page, $allowed)) {
    $page = 'home';
  }
?>

<?php include 'includes/header.php'; ?>

<?php include "pages/{$page}.php"; ?>

<?php include 'includes/footer.php'; ?>