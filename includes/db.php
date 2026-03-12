<?php
// ============================================================
// includes/db.php — Database connection
// ============================================================

DEFINE('DATABASE_HOST',     'localhost');
DEFINE('DATABASE_DATABASE', 'indiclex_db');  // ← your database name
DEFINE('DATABASE_USER',     'root');              // ← your DB username
DEFINE('DATABASE_PASSWORD', '');                  // ← your DB password (blank for XAMPP)

$db = new mysqli(
    DATABASE_HOST,
    DATABASE_USER,
    DATABASE_PASSWORD,
    DATABASE_DATABASE
);

if ($db->connect_error) {
    die('Connect Error (' . $db->connect_errno . '): ' . $db->connect_error);
}

// utf8mb4 is required for Hindi (Devanagari) script to save correctly
$db->set_charset('utf8mb4');

// Alias so both $db and $conn work across all team files
$conn = $db;