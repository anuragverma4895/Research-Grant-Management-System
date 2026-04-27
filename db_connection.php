<?php
/**
 * ============================================
 * DATABASE CONNECTION (Secure)
 * ============================================
 */
require_once __DIR__ . '/config.php';

// Create connection using constants from config
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    die("Database Connection Failed. Please try again later.");
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
?>