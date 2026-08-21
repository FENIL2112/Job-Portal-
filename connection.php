<?php
// Database Connection Configuration
$server = 'localhost';
$username = 'root';
$password = '';
$db = 'jobportal';

// Establish MySQL connection
$con = mysqli_connect($server, $username, $password, $db);

if (!$con) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4 for full Unicode support
mysqli_set_charset($con, "utf8mb4");

// Synchronize PHP & MySQL Timezones
date_default_timezone_set('Asia/Kolkata');
@mysqli_query($con, "SET time_zone = '+05:30'");
?>