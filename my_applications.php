<?php
session_start();
include "auth_check.php";
requireUser();
include "db_connection.php";

$user = getCurrentUser();
$researcher_id = $user['researcher_id'];

// Fetch all applications with agency details
$applications = $conn->query("
    SELECT ga.*, fa.agency_name, fa.funding_area 
    FROM Grant_Applications ga 
    JOIN Funding_Agencies fa ON ga.funding_agency_id = fa.funding_agency_id 
    WHERE ga.researcher_id = $researcher_id 
    ORDER BY ga.submission_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - RGMS</title>
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
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .applications-grid {
            display: grid;
            gap: 20px;
        }

        .application-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 5px solid #667eea;
            transition: all 0.3s;
        }

        .application-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .card-header h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 5px;
        }

        .application-id {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .card-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .meta-item .icon {
            font-size: 18px;
        }

        .meta-item .label {
            color: #666;
            font-weight: 600;
        }

        .card-description {
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 3px solid #667eea;
            border-radius: 5px;
            font-size: 14px;
            color: #555;
            line-height: 1.6;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-submitted { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-under.review { background: #d1ecf1; color: #0c5460; }
        .status-on.hold { background: #d6d8db; color: #383d41; }

        .admin-comment {
            margin-top: 10px;
            padding: 10px;
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            border-radius: 5px;
            font-size: 13px;
        }

        .admin-comment strong {
            display: block;
            margin-bottom: 5px;
        }

        .no-applications {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .no-applications .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .no-applications h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 15px;
        }

        .no-applications a {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📋 My Applications</h1>
        <div class="navbar-right">
            <a href="user_dashboard.php">← Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h2>📊 All Your Grant Applications</h2>
            <p style="color: #666;">Track and manage your submitted grant applications</p>
        </div>

        <div class="applications-grid">
            <?php if ($applications->num_rows > 0): ?>
                <?php while ($app = $applications->fetch_assoc()): ?>
                <div class="application-card">
                    <div class="card-header">
                        <div>
                            <h3><?php echo htmlspecialchars($app['grant_title']); ?></h3>
                            <p style="color: #666; font-size: 13px;">🏢 <?php echo htmlspecialchars($app['agency_name']); ?></p>
                        </div>
                        <span class="application-id">#<?php echo $app['application_id']; ?></span>
                    </div>

                    <div class="card-meta">
                        <div class="meta-item">
                            <span class="icon">💰</span>
                            <span><span class="label">Amount:</span> ₹<?php echo number_format($app['grant_amount_requested'], 2); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="icon">📅</span>
                            <span><span class="label">Submitted:</span> <?php echo date('d M Y', strtotime($app['submission_date'])); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="icon">⏱️</span>
                            <span><span class="label">Duration:</span> <?php echo $app['project_duration_months']; ?> months</span>
                        </div>
                        <div class="meta-item">
                            <span class="icon">🎯</span>
                            <span><span class="label">Priority:</span> <?php echo $app['priority_level']; ?></span>
                        </div>
                    </div>

                    <div class="card-description">
                        <strong>📝 Description:</strong><br>
                        <?php echo nl2br(htmlspecialchars($app['grant_description'])); ?>
                    </div>

                    <?php if (!empty($app['admin_comments'])): ?>
                    <div class="admin-comment">
                        <strong>💬 Admin Comments:</strong>
                        <?php echo nl2br(htmlspecialchars($app['admin_comments'])); ?>
                    </div>
                    <?php endif; ?>

                    <div class="card-footer">
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '.', $app['application_status'])); ?>">
                            <?php echo $app['application_status']; ?>
                        </span>
                        <span style="color: #999; font-size: 13px;">
                            Updated: <?php echo date('d M Y, h:i A', strtotime($app['updated_at'])); ?>
                        </span>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-applications">
                    <div class="icon">📄</div>
                    <h3>No Applications Yet</h3>
                    <p style="color: #999;">You haven't submitted any grant applications yet.</p>
                    <a href="apply_grant.php">Submit Your First Application</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>