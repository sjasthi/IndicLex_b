<?php
// ============================================================
// includes/db.php — Database connection
// ============================================================
//
// Credentials:
//   1. includes/db.local.php — defines DATABASE_* constants
//   2. includes/db.password — optional plain-text file (one line, no PHP); used if DATABASE_PASSWORD is empty
//   3. Environment: INDICLEX_DB_HOST, INDICLEX_DB_DATABASE, INDICLEX_DB_USER, INDICLEX_DB_PASSWORD
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

/** Effective password: constant, else first line of db.password, else env (again). */
$database_password_effective = DATABASE_PASSWORD;
if ($database_password_effective === '') {
    $pwfile = __DIR__ . '/db.password';
    if (is_readable($pwfile)) {
        foreach (explode("\n", (string) file_get_contents($pwfile)) as $line) {
            $line = trim($line);
            if ($line === '' || (isset($line[0]) && $line[0] === '#')) {
                continue;
            }
            $database_password_effective = $line;
            break;
        }
    }
}
if ($database_password_effective === '' && getenv('INDICLEX_DB_PASSWORD') !== false) {
    $database_password_effective = getenv('INDICLEX_DB_PASSWORD');
}

$db_connect_errno = 0;
$db_connect_error   = '';

try {
    $db = new mysqli(
        DATABASE_HOST,
        DATABASE_USER,
        $database_password_effective,
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
    $used_pw = ($database_password_effective !== '');
    $local_exists = is_file(__DIR__ . '/db.local.php');
    $pwfile_exists = is_file(__DIR__ . '/db.password');

    header('Content-Type: text/html; charset=utf-8');
    http_response_code(503);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Database connection</title></head><body style="font-family:system-ui,sans-serif;max-width:42rem;margin:2rem auto;padding:0 1rem;line-height:1.5">';
    echo '<h1>Database connection failed</h1>';
    echo '<p><code>' . htmlspecialchars($err, ENT_QUOTES, 'UTF-8') . '</code></p>';

    if ($errno === 1045 || stripos($err, 'Access denied') !== false) {
        echo '<p><strong>Access denied</strong> — MySQL user <code>' . htmlspecialchars(DATABASE_USER, ENT_QUOTES, 'UTF-8') . '</code> needs the correct password.</p>';
        echo '<ul>';
        echo '<li><code>includes/db.local.php</code> present: <strong>' . ($local_exists ? 'yes' : 'no') . '</strong></li>';
        echo '<li><code>includes/db.password</code> present: <strong>' . ($pwfile_exists ? 'yes' : 'no') . '</strong></li>';
        echo '<li>Non-empty password sent to MySQL: <strong>' . ($used_pw ? 'yes' : 'no — MySQL reports "using password: NO"') . '</strong></li>';
        echo '</ul>';
        echo '<p><strong>Fix (pick one):</strong></p>';
        echo '<ol>';
        echo '<li>In <code>includes/db.local.php</code>, set <code>define(\'DATABASE_PASSWORD\', \'your_password\');</code></li>';
        echo '<li>Or create <code>includes/db.password</code> (copy from <code>db.password.example</code>): one line, your MySQL password only — no quotes, optional <code>#</code> comment lines.</li>';
        echo '<li>Or set the environment variable <code>INDICLEX_DB_PASSWORD</code> for PHP.</li>';
        echo '</ol>';
        echo '<p>Find or reset the password in <strong>phpMyAdmin</strong> → <em>User accounts</em> for <code>root</code> @ <code>localhost</code>.</p>';
    }

    echo '</body></html>';
    exit;
}

// utf8mb4 is required for Hindi (Devanagari) script to save correctly
$db->set_charset('utf8mb4');

// Alias so both $db and $conn work across all team files
$conn = $db;
