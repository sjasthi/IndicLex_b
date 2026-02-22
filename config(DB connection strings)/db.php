<?php
/*
 * All database connection variables
 */
define('DB_USER', "your_database_username"); // db user
define('DB_PASSWORD', "your_database_password"); // db password
define('DB_DATABASE', "your_database_name"); // database name
define('DB_HOST', "localhost"); // db server (use 'localhost' if script is on Bluehost)

// Attempt to connect to the database
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully to Bluehost database!";

// You can now perform database operations here...

// Close connection when finished (optional for scripts that end immediately)
// mysqli_close($conn);
?>