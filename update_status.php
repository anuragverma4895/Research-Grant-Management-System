<?php
// Include the database connection file
include('../includes/db_connection.php');

// Check if the form is submitted
if (isset($_POST['update_status'])) {
    // Get the data from the form
    $application_id = $_POST['application_id'];
    $status = $_POST['status'];

    // Prepare the SQL query to update the status
    $sql = "UPDATE Grant_Applications SET application_status = ? WHERE application_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $application_id);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect to the Admin Dashboard after successful update
        header("Location: admin_dashboard.php?status_updated=true");
        exit;
    } else {
        // Show an error message if the update fails
        echo "Error updating the status. Please try again.";
    }
}
?>
