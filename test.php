<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1 style='color:green;'>✅ PHP is Working!</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test database connection
$servername = "sql100.infinityfree.com";
$username   = "if0_40850986";
$password   = "v5vVbqGRP7";
$dbname     = "if0_40850986_grant_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo "<div style='padding:20px;background:#f8d7da;color:#721c24;border-radius:8px;margin:20px;'>";
    echo "<h3>❌ Database Connection Failed!</h3>";
    echo "<p>Error: " . $conn->connect_error . "</p>";
    echo "</div>";
} else {
    echo "<div style='padding:20px;background:#d4edda;color:#155724;border-radius:8px;margin:20px;'>";
    echo "<h3>✅ Database Connected Successfully!</h3>";
    echo "</div>";
    
    // Check if Users table exists
    $result = $conn->query("SHOW TABLES LIKE 'Users'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green;'><strong>✅ Users table exists!</strong></p>";
        
        // Count users
        $count_result = $conn->query("SELECT COUNT(*) as total FROM Users");
        if ($count_result) {
            $count = $count_result->fetch_assoc()['total'];
            echo "<p>Total Users in database: <strong>$count</strong></p>";
            
            // Show sample users
            echo "<h3>Sample Users:</h3>";
            $users = $conn->query("SELECT user_id, username, email, role FROM Users LIMIT 5");
            echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
            echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
            while ($user = $users->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $user['user_id'] . "</td>";
                echo "<td>" . $user['username'] . "</td>";
                echo "<td>" . $user['email'] . "</td>";
                echo "<td>" . $user['role'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<div style='padding:20px;background:#fff3cd;color:#856404;border-radius:8px;margin:20px;'>";
        echo "<h3>⚠️ Users table does NOT exist!</h3>";
        echo "<p>You need to run the SQL setup in phpMyAdmin first!</p>";
        echo "</div>";
    }
}

echo "<hr><h2>✅ All Files Present:</h2>";
echo "<ul>";
$files = [
    'index.php', 'login.php', 'signup.php', 'admin_login.php', 
    'logout.php', 'auth_check.php', 'db_connection.php',
    'user_dashboard.php', 'admin_dashboard.php', 'apply_grant.php',
    'my_applications.php', 'manage_researchers.php', 'manage_agencies.php',
    'style.css', 'script.js'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<li style='color:green;'>✅ $file</li>";
    } else {
        echo "<li style='color:red;'>❌ $file NOT FOUND</li>";
    }
}
echo "</ul>";
?>