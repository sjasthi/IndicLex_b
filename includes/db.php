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

$db_connect_errno = 0;
$db_connect_error   = '';

try {
    $db = new mysqli(
        DATABASE_HOST,
        DATABASE_USER,
        DATABASE_PASSWORD,
        DATABASE_DATABASE
    );
} catch (mysqli_sql_exception $e) {
    $db = null;
    $db_connect_errno = (int) $e->getCode();
    $db_connect_error = $e->getMessage();
    if ($db_connect_errno === 0 && stripos($db_connect_error, 'Access denied') !== false) {
        $db_connect_errno = 1045;
    }
}

if ($db === null || !($db instanceof mysqli) || $db->connect_errno) {
    $errno = $db_connect_errno;
    $err   = $db_connect_error !== '' ? $db_connect_error : ($db && $db->connect_error ? $db->connect_error : 'Unknown connection error');
    $used_pw = (defined('DATABASE_PASSWORD') && DATABASE_PASSWORD !== '');
    $local_exists = is_file(__DIR__ . '/db.local.php');

    header('Content-Type: text/html; charset=utf-8');
    http_response_code(503);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Database connection</title></head><body style="font-family:system-ui,sans-serif;max-width:42rem;margin:2rem auto;padding:0 1rem;line-height:1.5">';
    echo '<h1>Database connection failed</h1>';
    echo '<p><code>' . htmlspecialchars($err, ENT_QUOTES, 'UTF-8') . '</code></p>';

    if ($errno === 1045 || stripos($err, 'Access denied') !== false) {
        echo '<p><strong>Access denied</strong> usually means the username or password in <code>includes/db.local.php</code> does not match MySQL.</p>';
        echo '<ul>';
        echo '<li><code>db.local.php</code> present: <strong>' . ($local_exists ? 'yes' : 'no') . '</strong></li>';
        echo '<li>Password in use: <strong>' . ($used_pw ? 'yes (non-empty)' : 'no (empty — MySQL reports "using password: NO")') . '</strong></li>';
        echo '</ul>';
        echo '<p>Open <code>includes/db.local.php</code> and set <code>DATABASE_PASSWORD</code> to the same password as MySQL user <code>' . htmlspecialchars(DATABASE_USER, ENT_QUOTES, 'UTF-8') . '</code> (check in phpMyAdmin → <em>User accounts</em>, or reset the password there).</p>';
        echo '<p>If this account should have <em>no</em> password, leave <code>\'\'</code> — but your server is rejecting that, so a password is required.</p>';
    }

    echo '</body></html>';
    exit;
}

// utf8mb4 is required for Hindi (Devanagari) script to save correctly
$db->set_charset('utf8mb4');

// Alias so both $db and $conn work across all team files
$conn = $db;
