<?php
include('../includes/db_connection.php');

// Get the application ID from the URL
$application_id = $_GET['application_id'];

// Fetch the grant application details
$sql_application = "SELECT * FROM Grant_Applications WHERE application_id = $application_id";
$result_application = $conn->query($sql_application);
$application = $result_application->fetch_assoc();

// Fetch the reviewer comments (if any)
$sql_reviews = "SELECT * FROM Grant_Reviews WHERE application_id = $application_id";
$result_reviews = $conn->query($sql_reviews);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $comments = $_POST['comments'];

    // Update the application status
    $sql_update = "UPDATE Grant_Applications SET application_status = '$status' WHERE application_id = $application_id";
    $conn->query($sql_update);

    // If it's approved or rejected, save the comments
    if ($status === 'Approved' || $status === 'Rejected') {
        $sql_insert_comment = "INSERT INTO Grant_Reviews (application_id, reviewer_id, review_score, review_comments, review_date) 
                               VALUES ($application_id, 1, 0, '$comments', NOW())"; // Assuming reviewer_id = 1 for now
        $conn->query($sql_insert_comment);
    }

    // Redirect back to admin dashboard
    header('Location: admin_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grant Valuation Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Custom Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .panel-container {
            margin: 30px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .application-details {
            margin-bottom: 30px;
        }

        .application-details h3 {
            margin-bottom: 15px;
        }

        .application-details p {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .status-update {
            margin-bottom: 30px;
        }

        .status-update select, .status-update textarea {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .status-update button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            border-radius: 5px;
        }

        .status-update button:hover {
            background-color: #45a049;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            position: relative;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<header>
    <h1>Grant Valuation Panel</h1>
</header>

<div class="panel-container">
    <div class="application-details">
        <h3>Grant Application Details</h3>
        <p><strong>Grant Title:</strong> <?php echo $application['grant_title']; ?></p>
        <p><strong>Researcher ID:</strong> <?php echo $application['researcher_id']; ?></p>
        <p><strong>Grant Amount Requested:</strong> $<?php echo $application['grant_amount_requested']; ?></p>
        <p><strong>Grant Description:</strong> <?php echo $application['grant_description']; ?></p>
        <p><strong>Submission Date:</strong> <?php echo $application['submission_date']; ?></p>
        <p><strong>Current Status:</strong> <?php echo $application['application_status']; ?></p>
    </div>

    <div class="status-update">
        <h3>Update Status & Add Comments</h3>

        <form method="POST" action="">
            <label for="status">Grant Application Status</label>
            <select name="status" id="status" required>
                <option value="Submitted" <?php echo ($application['application_status'] === 'Submitted') ? 'selected' : ''; ?>>Submitted</option>
                <option value="Reviewed" <?php echo ($application['application_status'] === 'Reviewed') ? 'selected' : ''; ?>>Reviewed</option>
                <option value="Approved" <?php echo ($application['application_status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                <option value="Rejected" <?php echo ($application['application_status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
            </select>

            <label for="comments">Add Comments (Optional)</label>
            <textarea name="comments" id="comments" rows="5"><?php echo isset($comments) ? $comments : ''; ?></textarea>

            <button type="submit">Update Status</button>
        </form>
    </div>

    <!-- Reviewer Comments (if any) -->
    <div class="reviewer-comments">
        <h3>Reviewer Comments</h3>
        <?php
        if ($result_reviews->num_rows > 0) {
            while ($review = $result_reviews->fetch_assoc()) {
                echo "<p><strong>Score:</strong> {$review['review_score']} | <strong>Comments:</strong> {$review['review_comments']}</p>";
            }
        } else {
            echo "<p>No reviewer comments yet.</p>";
        }
        ?>
    </div>
</div>

<footer>
    <p>&copy; 2025 Research Grant Management System. All Rights Reserved.</p>
</footer>

</body>
</html>
