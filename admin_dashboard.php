<?php
include('../includes/db_connection.php');

// Fetch grant application stats
$sql_stats = "SELECT COUNT(*) AS total_apps, 
                     SUM(CASE WHEN application_status = 'Approved' THEN 1 ELSE 0 END) AS approved_apps,
                     SUM(CASE WHEN application_status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_apps
              FROM Grant_Applications";
$result_stats = $conn->query($sql_stats);
$stats = $result_stats->fetch_assoc();

// Fetch all grant applications
$sql_grants = "SELECT * FROM Grant_Applications";
$result_grants = $conn->query($sql_grants);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Dashboard Custom Styling */
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

        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin: 20px;
        }

        .dashboard-card {
            width: 30%;
            background-color: white;
            padding: 20px;
            margin: 10px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .dashboard-card h3 {
            margin-bottom: 10px;
        }

        .dashboard-card p {
            font-size: 2rem;
            color: #4CAF50;
        }

        .dashboard-card .icon {
            font-size: 3rem;
            color: #4CAF50;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }

        .action-buttons a {
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            margin-right: 5px;
            transition: background-color 0.3s;
        }

        .action-buttons a:hover {
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

        .page-link {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 5px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
        }

        .page-link:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
</header>

<div class="dashboard-container">
    <!-- Dashboard Cards -->
    <div class="dashboard-card">
        <div class="icon">&#128179;</div>
        <h3>Total Applications</h3>
        <p><?php echo $stats['total_apps']; ?></p>
    </div>

    <div class="dashboard-card">
        <div class="icon">&#9989;</div>
        <h3>Approved Applications</h3>
        <p><?php echo $stats['approved_apps']; ?></p>
    </div>

    <div class="dashboard-card">
        <div class="icon">&#10060;</div>
        <h3>Rejected Applications</h3>
        <p><?php echo $stats['rejected_apps']; ?></p>
    </div>
</div>

<!-- Table of Grant Applications -->
<h2 style="text-align:center; margin-top: 30px;">Grant Applications</h2>
<table>
    <thead>
        <tr>
            <th>Application ID</th>
            <th>Researcher ID</th>
            <th>Grant Title</th>
            <th>Amount Requested</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result_grants->num_rows > 0) {
            while ($row = $result_grants->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['application_id']}</td>
                        <td>{$row['researcher_id']}</td>
                        <td>{$row['grant_title']}</td>
                        <td>\${$row['grant_amount_requested']}</td>
                        <td>{$row['application_status']}</td>
                        <td class='action-buttons'>
                            <a href='grant_valuation_panel.php?application_id={$row['application_id']}'>View</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No applications found</td></tr>";
        }
        ?>
    </tbody>
</table>

<footer>
    <p>&copy; 2025 Research Grant Management System. All Rights Reserved.</p>
</footer>

</body>
</html>
