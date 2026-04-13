<?php
/**
 * Copy this file to db.local.php in the same folder and set your real credentials.
 * db.local.php overrides the defaults in db.php and is the usual fix for
 * "Access denied for user 'root'@'localhost' (using password: NO)" when MySQL
 * requires a password for root.
 */
define('DATABASE_HOST', 'localhost');
define('DATABASE_DATABASE', 'indiclex_db');
define('DATABASE_USER', 'root');
define('DATABASE_PASSWORD', '');
