<?php
session_start();
include "auth_check.php";
requireUser();
include "db_connection.php";

$user = getCurrentUser();
$researcher_id = $user['researcher_id'];

// Fetch researcher details
$stmt = $conn->prepare("SELECT * FROM Researchers WHERE researcher_id = ?");
$stmt->bind_param("i", $researcher_id);
$stmt->execute();
$researcher = $stmt->get_result()->fetch_assoc();

// Fetch statistics
$stats = [];

// Total applications
$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE researcher_id = $researcher_id");
$stats['total_applications'] = $result->fetch_assoc()['total'];

// Approved applications
$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE researcher_id = $researcher_id AND application_status = 'Approved'");
$stats['approved'] = $result->fetch_assoc()['total'];

// Pending applications
$result = $conn->query("SELECT COUNT(*) as total FROM Grant_Applications WHERE researcher_id = $researcher_id AND application_status IN ('Submitted', 'Under Review')");
$stats['pending'] = $result->fetch_assoc()['total'];

// Total grant amount received
$result = $conn->query("SELECT COALESCE(SUM(g.grant_amount_awarded), 0) as total FROM Grants g JOIN Grant_Applications ga ON g.application_id = ga.application_id WHERE ga.researcher_id = $researcher_id");
$stats['total_funding'] = $result->fetch_assoc()['total'];

// Recent applications
$recent_apps = $conn->query("SELECT * FROM Grant_Applications WHERE researcher_id = $researcher_id ORDER BY submission_date DESC LIMIT 5");

// Notifications
$notifications = $conn->query("SELECT * FROM Notifications WHERE user_id = {$user['user_id']} ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - RGMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
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

        .navbar h1 {
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome-banner {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .welcome-banner h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .welcome-banner p {
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-card .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .action-btn {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .action-btn:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.2);
        }

        .action-btn .icon {
            font-size: 35px;
            margin-bottom: 10px;
        }

        .action-btn span {
            display: block;
            font-weight: 600;
            font-size: 14px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .panel h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .application-item {
            padding: 15px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .application-item h4 {
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
        }

        .application-item .meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #666;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-submitted { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-under.review { background: #d1ecf1; color: #0c5460; }

        .notification-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 13px;
            border-left: 3px solid #667eea;
        }

        .notification-item.unread {
            background: #e3f2fd;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>🎓 Research Grant Management</h1>
        <div class="navbar-right">
            <div class="user-info">
                <span>Welcome, <strong><?php echo htmlspecialchars($researcher['first_name']); ?></strong></span>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <h2>👋 Welcome back, <?php echo htmlspecialchars($researcher['first_name'] . ' ' . $researcher['last_name']); ?>!</h2>
            <p><?php echo htmlspecialchars($researcher['institution'] ?? 'Researcher'); ?> • <?php echo htmlspecialchars($researcher['department'] ?? ''); ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon">📊</div>
                <h3>Total Applications</h3>
                <div class="number"><?php echo $stats['total_applications']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">✅</div>
                <h3>Approved Grants</h3>
                <div class="number"><?php echo $stats['approved']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">⏳</div>
                <h3>Pending Review</h3>
                <div class="number"><?php echo $stats['pending']; ?></div>
            </div>

            <div class="stat-card">
                <div class="icon">💰</div>
                <h3>Total Funding</h3>
                <div class="number">₹<?php echo number_format($stats['total_funding'], 0); ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h3 style="margin-bottom: 15px; color: #333;">⚡ Quick Actions</h3>
        <div class="quick-actions">
            <a href="apply_grant.php" class="action-btn">
                <div class="icon">📝</div>
                <span>Apply for Grant</span>
            </a>
            <a href="my_applications.php" class="action-btn">
                <div class="icon">📋</div>
                <span>My Applications</span>
            </a>
            <a href="application_status.php" class="action-btn">
                <div class="icon">🔍</div>
                <span>Track Status</span>
            </a>
            <a href="view_grants.php" class="action-btn">
                <div class="icon">🎯</div>
                <span>Available Grants</span>
            </a>
            <a href="edit_profile.php" class="action-btn">
                <div class="icon">👤</div>
                <span>Edit Profile</span>
            </a>
            <a href="notifications.php" class="action-btn">
                <div class="icon">🔔</div>
                <span>Notifications</span>
            </a>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Recent Applications -->
            <div class="panel">
                <h3>📄 Recent Applications</h3>
                <?php if ($recent_apps->num_rows > 0): ?>
                    <?php while ($app = $recent_apps->fetch_assoc()): ?>
                        <div class="application-item">
                            <h4><?php echo htmlspecialchars($app['grant_title']); ?></h4>
                            <div class="meta">
                                <span>💰 ₹<?php echo number_format($app['grant_amount_requested'], 2); ?></span>
                                <span>📅 <?php echo date('d M Y', strtotime($app['submission_date'])); ?></span>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '.', $app['application_status'])); ?>">
                                    <?php echo $app['application_status']; ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 30px;">No applications yet. Start by applying for a grant!</p>
                <?php endif; ?>
            </div>

            <!-- Notifications -->
            <div class="panel">
                <h3>🔔 Notifications</h3>
                <?php if ($notifications->num_rows > 0): ?>
                    <?php while ($notif = $notifications->fetch_assoc()): ?>
                        <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                            <?php echo htmlspecialchars($notif['message']); ?>
                            <div style="font-size: 11px; color: #999; margin-top: 5px;">
                                <?php echo date('d M Y, h:i A', strtotime($notif['created_at'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 20px;">No new notifications</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>