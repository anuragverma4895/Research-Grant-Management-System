<?php
// Database Configuration
$servername = "sql100.infinityfree.com";
$username   = "if0_40850986";
$password   = "v5vVbqGRP7";
$dbname     = "if0_40850986_grant_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Optional: Set timezone
date_default_timezone_set('Asia/Kolkata');
?>