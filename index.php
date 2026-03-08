<?php
  $page    = isset($_GET['page']) ? $_GET['page'] : 'home';
  $allowed = ['home', 'catalog', 'search', 'preferences'];

  if (!in_array($page, $allowed)) {
    $page = 'home';
  }
?>

<?php include 'includes/header.php'; ?>

<?php include "pages/{$page}.php"; ?>

<?php include 'includes/footer.php'; ?>