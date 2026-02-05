<?php
session_start();

include "auth_check.php";
requireUser();
include "db_connection.php";

/* ============================
   SAFETY CHECK: researcher_id
============================ */
if (!isset($_SESSION['researcher_id']) || empty($_SESSION['researcher_id'])) {
    // If user is logged in but researcher_id missing, force logout
    header("Location: logout.php");
    exit();
}

$researcher_id = intval($_SESSION['researcher_id']);

/* ============================
   FETCH FUNDING AGENCIES
============================ */
$result_agencies = $conn->query("SELECT funding_agency_id, agency_name FROM Funding_Agencies");

$success_message = "";
$error_message = "";

/* ============================
   HANDLE FORM SUBMISSION
============================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $grant_title = trim($_POST['grant_title'] ?? '');
    $grant_description = trim($_POST['grant_description'] ?? '');
    $grant_amount_requested = floatval($_POST['grant_amount_requested'] ?? 0);
    $funding_agency_id = intval($_POST['funding_agency_id'] ?? 0);

    if ($grant_title === "" || $grant_description === "" || $grant_amount_requested <= 0 || $funding_agency_id <= 0) {
        $error_message = "Please fill all fields correctly.";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO Grant_Applications
            (researcher_id, grant_title, grant_description, grant_amount_requested, application_status, submission_date, funding_agency_id)
            VALUES (?, ?, ?, ?, 'Submitted', CURDATE(), ?)
        ");

        if (!$stmt) {
            $error_message = "Database error. Please try again.";
        } else {
            $stmt->bind_param("issdi", $researcher_id, $grant_title, $grant_description, $grant_amount_requested, $funding_agency_id);

            if ($stmt->execute()) {
                $success_message = "✅ Grant application submitted successfully!";
            } else {
                $error_message = "❌ Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
}

/* ============================
   FETCH USER APPLICATIONS
============================ */
$stmt_my_apps = $conn->prepare("
    SELECT application_id, grant_title, grant_amount_requested, submission_date, application_status
    FROM Grant_Applications
    WHERE researcher_id = ?
    ORDER BY submission_date DESC
");
$stmt_my_apps->bind_param("i", $researcher_id);
$stmt_my_apps->execute();
$result_my_apps = $stmt_my_apps->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Grant</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
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
        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .navbar .logout-btn {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            transition: background 0.3s;
        }
        .navbar .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .form-container {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .form-container h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }
        .alert {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #66bb6a;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ef5350;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        .form-group textarea { resize: vertical; min-height: 120px; }

        .submit-btn {
            background: #4CAF50;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        .submit-btn:hover { background: #45a049; }

        .my-applications {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .my-applications h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
        }
        table { width: 100%; border-collapse: collapse; }
        table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        table tbody tr:hover { background: #f5f5f5; }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-submitted { background: #fff3e0; color: #e65100; }
        .status-approved { background: #e8f5e9; color: #2e7d32; }
        .status-rejected { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>🎓 Grant Application Portal</h1>
    <div class="user-info">
        <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="container">

    <div class="form-container">
        <h2>📝 Apply for Research Grant</h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="grant_title">Grant Title *</label>
                <input type="text" id="grant_title" name="grant_title" required placeholder="Enter grant title">
            </div>

            <div class="form-group">
                <label for="grant_description">Grant Description *</label>
                <textarea id="grant_description" name="grant_description" required placeholder="Describe your research project"></textarea>
            </div>

            <div class="form-group">
                <label for="grant_amount_requested">Amount Requested (₹) *</label>
                <input type="number" id="grant_amount_requested" name="grant_amount_requested" required min="1" step="0.01" placeholder="Enter amount">
            </div>

            <div class="form-group">
                <label for="funding_agency_id">Funding Agency *</label>
                <select id="funding_agency_id" name="funding_agency_id" required>
                    <option value="">-- Select Funding Agency --</option>

                    <?php if ($result_agencies && $result_agencies->num_rows > 0): ?>
                        <?php while ($agency = $result_agencies->fetch_assoc()): ?>
                            <option value="<?php echo $agency['funding_agency_id']; ?>">
                                <?php echo htmlspecialchars($agency['agency_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No agencies found</option>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="submit-btn">Submit Application</button>
        </form>
    </div>

    <div class="my-applications">
        <h2>📊 My Applications</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_my_apps->num_rows > 0): ?>
                    <?php while ($app = $result_my_apps->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['application_id']); ?></td>
                            <td><?php echo htmlspecialchars($app['grant_title']); ?></td>
                            <td>₹<?php echo number_format($app['grant_amount_requested'], 2); ?></td>
                            <td><?php echo date('d M Y', strtotime($app['submission_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($app['application_status']); ?>">
                                    <?php echo htmlspecialchars($app['application_status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 30px;">No applications yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
