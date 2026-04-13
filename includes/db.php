<?php
// ============================================================
// includes/db.php — Database connection
// ============================================================
//
// Credentials (first match wins):
//   1. includes/db.local.php — copy from db.local.example.php (recommended for XAMPP)
//   2. Environment: INDICLEX_DB_HOST, INDICLEX_DB_DATABASE, INDICLEX_DB_USER, INDICLEX_DB_PASSWORD
//   3. Defaults below (blank password matches stock XAMPP)
//

$__db_local = __DIR__ . '/db.local.php';
if (is_readable($__db_local)) {
    require_once $__db_local;
}

if (!defined('DATABASE_HOST')) {
    $v = getenv('INDICLEX_DB_HOST');
    define('DATABASE_HOST', $v !== false ? $v : 'localhost');
}
if (!defined('DATABASE_DATABASE')) {
    $v = getenv('INDICLEX_DB_DATABASE');
    define('DATABASE_DATABASE', $v !== false ? $v : 'indiclex_db');
}
if (!defined('DATABASE_USER')) {
    $v = getenv('INDICLEX_DB_USER');
    define('DATABASE_USER', $v !== false ? $v : 'root');
}
if (!defined('DATABASE_PASSWORD')) {
    $v = getenv('INDICLEX_DB_PASSWORD');
    define('DATABASE_PASSWORD', $v !== false ? $v : '');
}

$db = new mysqli(
    DATABASE_HOST,
    DATABASE_USER,
    DATABASE_PASSWORD,
    DATABASE_DATABASE
);

if ($db->connect_error) {
    $msg = 'Connect Error (' . $db->connect_errno . '): ' . $db->connect_error;
    if ($db->connect_errno === 1045) {
        $msg .= "\n\nHint: copy includes/db.local.example.php to includes/db.local.php and set DATABASE_PASSWORD (and user/host if needed).";
    }
    die($msg);
}

// utf8mb4 is required for Hindi (Devanagari) script to save correctly
$db->set_charset('utf8mb4');

// Alias so both $db and $conn work across all team files
$conn = $db;
