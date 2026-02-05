<?php
session_start();
include "auth_check.php";
requireUser();
include "db_connection.php";

$user = getCurrentUser();
$researcher_id = $user['researcher_id'];

$success_message = "";
$error_message = "";

// Fetch funding agencies
$agencies = $conn->query("SELECT * FROM Funding_Agencies ORDER BY agency_name");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_application'])) {
    $grant_title = trim($_POST['grant_title']);
    $grant_description = trim($_POST['grant_description']);
    $grant_amount = floatval($_POST['grant_amount_requested']);
    $funding_agency_id = intval($_POST['funding_agency_id']);
    $project_duration = intval($_POST['project_duration_months']);
    $priority_level = $_POST['priority_level'];
    
    if (empty($grant_title) || empty($grant_description) || $grant_amount <= 0) {
        $error_message = "Please fill all required fields correctly.";
    } else {
        $stmt = $conn->prepare("INSERT INTO Grant_Applications (researcher_id, grant_title, grant_description, grant_amount_requested, funding_agency_id, project_duration_months, priority_level, submission_date) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())");
        $stmt->bind_param("issdiss", $researcher_id, $grant_title, $grant_description, $grant_amount, $funding_agency_id, $project_duration, $priority_level);
        
        if ($stmt->execute()) {
            $application_id = $stmt->insert_id;
            
            // Create notification for user
            $message = "Your grant application #$application_id has been submitted successfully and is under review.";
            $notif_stmt = $conn->prepare("INSERT INTO Notifications (user_id, message, notification_type) VALUES (?, ?, 'Application')");
            $notif_stmt->bind_param("is", $user['user_id'], $message);
            $notif_stmt->execute();
            
            $success_message = "Grant application submitted successfully! Application ID: #$application_id";
        } else {
            $error_message = "Error submitting application. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Grant - RGMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar h1 { font-size: 24px; }

        .navbar-right {
            display: flex;
            gap: 15px;
        }

        .navbar a {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .form-panel {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .form-panel h2 {
            font-size: 26px;
            margin-bottom: 25px;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group label span {
            color: #e74c3c;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📝 Apply for Grant</h1>
        <div class="navbar-right">
            <a href="user_dashboard.php">← Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="form-panel">
            <h2>📄 New Grant Application</h2>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Grant Title <span>*</span></label>
                    <input type="text" name="grant_title" required placeholder="Enter a descriptive title for your research grant">
                </div>

                <div class="form-group">
                    <label>Grant Description <span>*</span></label>
                    <textarea name="grant_description" required placeholder="Provide a detailed description of your research project, objectives, methodology, and expected outcomes"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Amount Requested (₹) <span>*</span></label>
                        <input type="number" name="grant_amount_requested" required min="1" step="0.01" placeholder="Enter amount">
                    </div>

                    <div class="form-group">
                        <label>Project Duration (Months) <span>*</span></label>
                        <input type="number" name="project_duration_months" required min="1" max="120" placeholder="Project duration">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Funding Agency <span>*</span></label>
                        <select name="funding_agency_id" required>
                            <option value="">-- Select Funding Agency --</option>
                            <?php while ($agency = $agencies->fetch_assoc()): ?>
                                <option value="<?php echo $agency['funding_agency_id']; ?>">
                                    <?php echo htmlspecialchars($agency['agency_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Priority Level</label>
                        <select name="priority_level">
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="submit_application" class="submit-btn">🚀 Submit Application</button>
            </form>
        </div>
    </div>
</body>
</html>