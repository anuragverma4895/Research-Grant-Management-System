<?php
session_start();
include "auth_check.php";
requireAdmin();
include "db_connection.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['application_id']);
    $status = $_POST['application_status'];

    $allowed = ['Submitted','Approved','Rejected'];
    if (in_array($status,$allowed)) {
        $stmt = $conn->prepare(
            "UPDATE Grant_Applications 
             SET application_status=? WHERE application_id=?"
        );
        $stmt->bind_param("si",$status,$id);
        $stmt->execute();
    }
}
header("Location: admin_dashboard.php");
exit();
