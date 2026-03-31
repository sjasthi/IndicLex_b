<?php
require_once 'includes/db.php';
require_once 'includes/preferences_helper.php';

$page    = isset($_GET['page']) ? $_GET['page'] : 'home';
$allowed = ['home', 'catalog', 'search', 'preferences', 'upload', 'admin_import'];

// import.php handles its own POST and redirects — run it directly
if ($page === 'import') {
    require 'pages/import.php';
    exit;
}

// Handle theme toggle redirect — save pref then go back to previous page
if ($page === 'preferences' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redirect'])) {
    if (isset($_POST['theme'])) {
        save_preference('theme', $_POST['theme'], $db, null);
    }
    header('Location: ' . $_POST['redirect']);
    exit;
}

if (!in_array($page, $allowed)) {
    $page = 'home';
}
?>

<?php include 'includes/header.php'; ?>

<?php include "pages/{$page}.php"; ?>

<?php include 'includes/footer.php'; ?>