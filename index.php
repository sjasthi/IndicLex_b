<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/preferences_helper.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// ── Standalone pages (no header/footer) ──────────────────────
$standalone = ['logout', 'import', 'datatables_ajax', 'admin_crud_api', 'entry_datatables_ajax', 'api_autocomplete', 'api_search'];
if (in_array($page, $standalone)) {
    require "pages/{$page}.php";
    exit;
}

// ── Admin-only pages — use admin header/footer ───────────────
$admin_pages = [
    'admin_dashboard',
    'admin_import',
    'upload',
    'admin_dictionaries',
    'admin_entries',
    'admin_compare',       // ← added
    'admin_integrity',     // ← added
    'admin_reports',       // ← added
    'admin_docs'
];
if (in_array($page, $admin_pages)) {
    require_admin();
    require 'includes/admin_header.php';
    require "pages/{$page}.php";
    require 'includes/admin_footer.php';
    exit;
}

// ── Handle theme toggle redirect ──────────────────────────────
if ($page === 'preferences' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redirect'])) {
    if (isset($_POST['theme'])) {
        save_preference('theme', $_POST['theme'], $db, null);
    }
    header('Location: ' . $_POST['redirect']);
    exit;
}

// ── Public pages ─────────────────────────────────────────────
$allowed = ['home', 'catalog', 'search', 'preferences', 'login', 'register', 'help'];
if (!in_array($page, $allowed)) {
    $page = 'home';
}
?>

<?php include 'includes/header.php'; ?>
<?php include "pages/{$page}.php"; ?>
<?php include 'includes/footer.php'; ?>