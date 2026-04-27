<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";
require_once "config.php";
require_once "functions.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['application_id']);
    $status = $_POST['application_status'];
    $comments = sanitize($_POST['admin_comments'] ?? '');

    if (in_array($status, ALLOWED_STATUSES)) {
        $stmt = $conn->prepare(
            "UPDATE Grant_Applications 
             SET application_status=?, admin_comments=? WHERE application_id=?"
        );
        $stmt->bind_param("ssi", $status, $comments, $id);
        $stmt->execute();
    }
}
header("Location: admin_dashboard.php?success=1");
exit();
