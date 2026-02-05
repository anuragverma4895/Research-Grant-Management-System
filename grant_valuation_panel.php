<?php
session_start();

// Include authentication check
include "auth_check.php";

// Require admin role
requireAdmin();

// Database connection
include "db_connection.php";

// Fetch all applications with researcher details
$sql = "
    SELECT 
        ga.application_id,
        ga.grant_title,
        ga.grant_description,
        ga.grant_amount_requested,
        ga.application_status,
        ga.submission_date,
        r.first_name,
        r.last_name,
        r.email,
        r.institution,
        fa.agency_name
    FROM Grant_Applications ga
    JOIN Researchers r ON ga.researcher_id = r.researcher_id
    JOIN Funding_Agencies fa ON ga.funding_agency_id = fa.funding_agency_id
    ORDER BY ga.submission_date DESC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grant Valuation Panel</title>
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
        .navbar .nav-links {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .navbar a {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            transition: background 0.3s;
        }
        .navbar a:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .panel-container {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .panel-container h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }
        .application-card {
            background: #f9f9f9;
            border-left: 4px solid #4CAF50;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            transition: box-shadow 0.3s;
        }
        .application-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .application-card h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .application-card .meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }
        .application-card .meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .application-card .description {
            margin: 15px 0;
            color: #555;
            line-height: 1.6;
        }
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

<!-- Navigation Bar -->
<div class="navbar">
    <h1>🔍 Grant Valuation Panel</h1>
    <div class="nav-links">
        <a href="admin_dashboard.php">← Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <div class="panel-container">
        <h2>📋 Application Review Panel</h2>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($app = mysqli_fetch_assoc($result)): ?>
            <div class="application-card">
                <h3><?php echo htmlspecialchars($app['grant_title']); ?></h3>
                
                <div class="meta">
                    <span>👤 <strong><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></strong></span>
                    <span>🏛️ <?php echo htmlspecialchars($app['institution']); ?></span>
                    <span>💰 ₹<?php echo number_format($app['grant_amount_requested'], 2); ?></span>
                    <span>📅 <?php echo date('d M Y', strtotime($app['submission_date'])); ?></span>
                    <span>🏢 <?php echo htmlspecialchars($app['agency_name']); ?></span>
                    <span>
                        <span class="status-badge status-<?php echo strtolower($app['application_status']); ?>">
                            <?php echo htmlspecialchars($app['application_status']); ?>
                        </span>
                    </span>
                </div>
                
                <div class="description">
                    <strong>Description:</strong><br>
                    <?php echo nl2br(htmlspecialchars($app['grant_description'])); ?>
                </div>
                
                <div class="meta">
                    <span>✉️ <?php echo htmlspecialchars($app['email']); ?></span>
                    <span>🆔 Application ID: #<?php echo $app['application_id']; ?></span>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; padding: 50px; color: #999;">No applications to review</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>