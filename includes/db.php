<?php
$host = "localhost";
$user = "your_db_user";
$password = "your_db_password";
$database = "your_db_name";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>