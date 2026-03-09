<?php
  $page    = isset($_GET['page']) ? $_GET['page'] : 'home';
  // Added 'admin_import' to the list below
  $allowed = ['home', 'catalog', 'search', 'preferences', 'admin_import'];

  if (!in_array($page, $allowed)) {
    $page = 'home';
  }
?>

<?php include 'includes/header.php'; ?>
<?php include "pages/{$page}.php"; ?>
<?php include 'includes/footer.php'; ?>