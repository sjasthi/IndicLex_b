<?php
/**
 * Sets dashboard stat variables using $db.
 * Include after db.php is loaded.
 */
$total_words = 0;
$r = $db->query("SELECT COUNT(*) AS total FROM dictionary_entries");
if ($r) {
    $total_words = (int) $r->fetch_assoc()['total'];
}

$total_dicts = 0;
$r = $db->query("SELECT COUNT(*) AS total FROM dictionaries");
if ($r) {
    $total_dicts = (int) $r->fetch_assoc()['total'];
}

$total_users = 0;
$r = $db->query("SELECT COUNT(*) AS total FROM users");
if ($r) {
    $total_users = (int) $r->fetch_assoc()['total'];
}

$with_telugu = 0;
$r = $db->query("SELECT COUNT(*) AS total FROM dictionary_entries WHERE telugu != '' AND telugu IS NOT NULL");
if ($r) {
    $with_telugu = (int) $r->fetch_assoc()['total'];
}

$with_hindi = 0;
$r = $db->query("SELECT COUNT(*) AS total FROM dictionary_entries WHERE hindi != '' AND hindi IS NOT NULL");
if ($r) {
    $with_hindi = (int) $r->fetch_assoc()['total'];
}
