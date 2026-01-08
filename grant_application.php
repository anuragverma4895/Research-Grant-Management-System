<?php
include('../includes/db_connection.php');

// Fetch researchers to populate the dropdown
$sql_researchers = "SELECT researcher_id, first_name, last_name FROM Researchers";
$result_researchers = $conn->query($sql_researchers);

// Fetch funding agencies to populate the dropdown
$sql_agencies = "SELECT funding_agency_id, agency_name FROM Funding_Agencies";
$result_agencies = $conn->query($sql_agencies);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $researcher_id = $_POST['researcher_id'];
    $grant_title = $_POST['grant_title'];
    $grant_description = $_POST['grant_description'];
    $grant_amount_requested = $_POST['grant_amount_requested'];
    $funding_agency_id = $_POST['funding_agency_id'];

    $sql_insert = "INSERT INTO Grant_Applications (researcher_id, grant_title, grant_description, grant_amount_requested, application_status, submission_date, funding_agency_id)
                   VALUES ('$researcher_id', '$grant_title', '$grant_description', '$grant_amount_requested', 'Submitted', CURDATE(), '$funding_agency_id')";

    if ($conn->query($sql_insert) === TRUE) {
        $success_message = "Grant application submitted successfully!";
    } else {
        $error_message = "Error submitting grant application: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grant Application</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Additional styling for the grant application page */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .form-container {
            width: 50%;
            margin: 20px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 1rem;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group textarea {
            resize: vertical;
            height: 150px;
        }

        .form-group button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #45a049;
        }

        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: white;
            text-align: center;
        }

        .alert.success {
            background-color: #4CAF50;
        }

        .alert.error {
            background-color: #f44336;
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
    <h1>Research Grant Application</h1>
</header>

<div class="form-container">
    <h2>Apply for a Grant</h2>
    
    <!-- Success or Error Message -->
    <?php if (isset($success_message)) { ?>
        <div class="alert success"><?php echo $success_message; ?></div>
    <?php } elseif (isset($error_message)) { ?>
        <div class="alert error"><?php echo $error_message; ?></div>
    <?php } ?>

    <!-- Grant Application Form -->
    <form action="grant_application.php" method="POST">
        <div class="form-group">
            <label for="researcher_id">Researcher Name</label>
            <select name="researcher_id" id="researcher_id" required>
                <option value="">Select Researcher</option>
                <?php while($row = $result_researchers->fetch_assoc()) { ?>
                    <option value="<?php echo $row['researcher_id']; ?>"><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="grant_title">Grant Title</label>
            <input type="text" id="grant_title" name="grant_title" required>
        </div>

        <div class="form-group">
            <label for="grant_description">Grant Description</label>
            <textarea id="grant_description" name="grant_description" required></textarea>
        </div>

        <div class="form-group">
            <label for="grant_amount_requested">Amount Requested ($)</label>
            <input type="number" id="grant_amount_requested" name="grant_amount_requested" min="0" required>
        </div>

        <div class="form-group">
            <label for="funding_agency_id">Funding Agency</label>
            <select name="funding_agency_id" id="funding_agency_id" required>
                <option value="">Select Funding Agency</option>
                <?php while($row = $result_agencies->fetch_assoc()) { ?>
                    <option value="<?php echo $row['funding_agency_id']; ?>"><?php echo $row['agency_name']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <button type="submit">Submit Application</button>
        </div>
    </form>
</div>

<footer>
    <p>&copy; 2025 Research Grant Management System. All Rights Reserved.</p>
</footer>

</body>
</html>
